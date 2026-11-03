<?php
// Employee documents: safety forms, certifications, and payroll forms (W-4 and
// similar) that employees upload on dashboard/documents.php.
//
// Who sees what (capabilities from DASHBOARD_ROLES in config.php):
//   safety forms, certifications   'safety_documents' (Safety role, Admins)
//   payroll forms                  'payroll_documents' (HR, Admins)
// Employees always see their own uploads.
//
// These can hold Social Security numbers, birth dates and addresses, so:
//   - every file is stored encrypted with DASHBOARD_ENCRYPTION_KEY (see
//     requests.php) in uploads/dashboard/documents/, under a random name;
//   - uploads are switched off until that key is set;
//   - payroll forms are permanently erased once HR marks them done, and every
//     time someone other than the employee opens one, it's recorded in the
//     activity log;
//   - emails only ever say a document was uploaded, never what's in it.

const DOCUMENT_TYPES = [
    'safety'        => ['label' => 'Safety Form',   'can' => 'safety_documents'],
    'certification' => ['label' => 'Certification', 'can' => 'safety_documents'],
    'payroll'       => ['label' => 'Payroll Form',  'can' => 'payroll_documents'],
];

const DOCUMENT_STATUSES = [
    'received' => 'Received',
    'done'     => 'Processed',
    'removed'  => 'Removed',
];

// Checked by the file's actual contents, not its name. Phone photos of a paper
// form are fine (JPG, PNG, and iPhone HEIC).
const DOCUMENT_FILE_TYPES = [
    'application/pdf' => 'pdf',
    'image/jpeg'      => 'jpg',
    'image/png'       => 'png',
    'image/heic'      => 'heic',
    'image/heif'      => 'heic',
];

// Certifications expiring within this many days are flagged for Safety.
const CERT_WARNING_DAYS = 30;

define('DOCUMENTS_DIR', DASHBOARD_DATA_DIR . '/documents');

function document_type_label(string $type): string {
    return DOCUMENT_TYPES[$type]['label'] ?? $type;
}

function document_status_label(string $status): string {
    return DOCUMENT_STATUSES[$status] ?? $status;
}

// The document types this person reviews (not counting their own uploads).
function reviewable_document_types(?array $user): array {
    return array_keys(array_filter(DOCUMENT_TYPES, fn($t) => user_can($user, $t['can'])));
}

function can_open_document(array $user, array $doc): bool {
    return (int) $doc['user_id'] === (int) $user['id'] || in_array($doc['type'], reviewable_document_types($user), true);
}

// The largest upload allowed: 10MB, or less if the server's PHP settings are lower.
function document_max_bytes(): int {
    $toBytes = function (string $value): int {
        $n = (int) $value;
        switch (strtolower(substr(trim($value), -1))) {
            case 'g': return $n * 1073741824;
            case 'm': return $n * 1048576;
            case 'k': return $n * 1024;
        }
        return $n;
    };
    $limits = array_filter([$toBytes((string) ini_get('upload_max_filesize')), $toBytes((string) ini_get('post_max_size'))]);
    return min(array_merge([10 * 1048576], $limits));
}

// ---------- Reading ----------

function find_document(int $id): ?array {
    $stmt = db()->prepare('SELECT d.*, u.name AS employee_name, u.email AS employee_email
                           FROM documents d JOIN users u ON u.id = d.user_id WHERE d.id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// Each employee's most recent copy of each certification (by name) that has
// expired or expires within CERT_WARNING_DAYS, soonest first. Uploading a
// renewed copy with a later date takes the old one off the list.
function expiring_certifications(): array {
    $cutoff = gmdate('Y-m-d', strtotime('+' . CERT_WARNING_DAYS . ' days'));
    $stmt = db()->prepare("SELECT d.*, u.name AS employee_name FROM documents d JOIN users u ON u.id = d.user_id
        WHERE d.type = 'certification' AND d.status = 'received' AND u.active = 1 AND d.expires_on <= ?
          AND NOT EXISTS (SELECT 1 FROM documents n WHERE n.user_id = d.user_id AND n.type = 'certification'
                          AND n.status = 'received' AND LOWER(n.title) = LOWER(d.title) AND n.expires_on > d.expires_on)
        ORDER BY d.expires_on");
    $stmt->execute([$cutoff]);
    return $stmt->fetchAll();
}

function waiting_payroll_document_count(): int {
    return (int) db()->query("SELECT COUNT(*) FROM documents WHERE type = 'payroll' AND status = 'received'")->fetchColumn();
}

// ---------- Uploading ----------

/**
 * Checks and saves an upload from dashboard/documents.php.
 * @return string[] problems to show; empty if it was saved
 */
function upload_document(array $user, array $post, array $file): array {
    $type    = posted_text($post, 'type');
    $title   = posted_text($post, 'title');
    $note    = posted_text($post, 'note');
    $expires = posted_text($post, 'expires_on');
    $errors  = [];

    if (!encryption_ready()) {
        return ['Document uploads aren\'t available right now. Please contact the office.'];
    }
    if (!isset(DOCUMENT_TYPES[$type])) {
        $errors[] = 'Choose what kind of document this is.';
    }
    if ($title === '') {
        $errors[] = 'Say what the document is, e.g. "W-4" or "Forklift certification".';
    } elseif (strlen($title) > 150) {
        $errors[] = 'Keep the description under 150 characters.';
    }
    if (strlen($note) > 1000) {
        $errors[] = 'The note is too long.';
    }
    if ($type === 'certification' && !valid_date($expires)) {
        $errors[] = 'Enter the date the certification expires.';
    }

    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Choose a file to upload.';
    } elseif ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE || ($file['size'] ?? 0) > document_max_bytes()) {
        $errors[] = 'That file is too large. The limit is ' . round(document_max_bytes() / 1048576) . 'MB.';
    } elseif ($error !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        $errors[] = 'The upload didn\'t finish. Please try again.';
    } elseif (!isset(DOCUMENT_FILE_TYPES[(new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name'])])) {
        $errors[] = 'Upload a PDF or a photo (JPG, PNG, or iPhone HEIC).';
    }
    if ($errors) {
        return $errors;
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $doc  = store_document($user, $type, $title, $note, (string) file_get_contents($file['tmp_name']), $mime,
                           (string) $file['name'], $type === 'certification' ? $expires : null);
    audit((int) $user['id'], 'document_uploaded', $user['name'] . ' uploaded a ' . document_type_label($type) . ': ' . $title);
    notify_reviewers_of_document($doc);
    return [];
}

// Encrypts and saves a file (an upload, or a filled-out safety form's PDF) and
// returns its documents row.
function store_document(array $user, string $type, string $title, string $note, string $bytes, string $mime, string $fileName, ?string $expires): array {
    $stored = bin2hex(random_bytes(16));
    dashboard_ensure_dir(DOCUMENTS_DIR);
    if (file_put_contents(DOCUMENTS_DIR . '/' . $stored, encrypt_bytes($bytes), LOCK_EX) === false) {
        throw new RuntimeException('Could not save the document');
    }
    // The original name is only used to name the download, keep it tidy.
    $name = preg_replace('/[^\p{L}\p{N} ._()-]/u', '', pathinfo($fileName, PATHINFO_FILENAME)) ?: 'document';
    db()->prepare('INSERT INTO documents (user_id, type, title, note, file_name, mime, size, stored_name, expires_on, status, created_at)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
        ->execute([$user['id'], $type, $title, $note, preg_replace('/^(.{100}).+$/us', '$1', $name) . '.' . DOCUMENT_FILE_TYPES[$mime], $mime,
                   strlen($bytes), $stored, $expires, 'received', db_now()]);
    return find_document((int) db()->lastInsertId());
}

// ---------- Opening, processing, removing ----------

// The decrypted file, or null if it's been erased or can't be read.
function document_contents(array $doc): ?string {
    if (!$doc['stored_name'] || !is_file(DOCUMENTS_DIR . '/' . $doc['stored_name'])) {
        return null;
    }
    return decrypt_bytes((string) file_get_contents(DOCUMENTS_DIR . '/' . $doc['stored_name']));
}

// Erases the file for good and records the new status: 'done' when HR has
// processed a payroll form, 'removed' when deleted.
function close_document(array $by, array $doc, string $status): void {
    if ($doc['stored_name'] && is_file(DOCUMENTS_DIR . '/' . $doc['stored_name'])) {
        unlink(DOCUMENTS_DIR . '/' . $doc['stored_name']);
    }
    db()->prepare('UPDATE documents SET status = ?, stored_name = NULL, reviewed_by = ?, reviewed_at = ? WHERE id = ?')
        ->execute([$status, $by['id'], db_now(), $doc['id']]);
    $whose = (int) $doc['user_id'] === (int) $by['id'] ? 'their own' : $doc['employee_name'] . '\'s';
    audit((int) $by['id'], 'document_' . $status, $by['name'] . ($status === 'done' ? ' processed ' : ' removed ') . $whose . ' '
        . document_type_label($doc['type']) . ': ' . $doc['title'] . ' (file erased)');
    if ($status === 'done') {
        dashboard_send_email($doc['employee_email'], 'Your ' . document_type_label($doc['type']) . ' has been processed',
            'Hi ' . (strtok($doc['employee_name'], ' ') ?: $doc['employee_name']) . ",\n\n"
            . 'HR has processed the ' . $doc['title'] . " you uploaded, and the copy on the dashboard has been deleted.\n\n"
            . "See your documents here:\n" . SITE_URL . BASE_URL . "/dashboard/documents.php\n");
    }
}

// ---------- Emails (never include what's in the document) ----------

function notify_reviewers_of_document(array $doc): void {
    $can   = DOCUMENT_TYPES[$doc['type']]['can'];
    $roles = array_keys(array_filter($GLOBALS['DASHBOARD_ROLES'], fn($r) => in_array($can, $r['can'], true)));
    $marks = implode(',', array_fill(0, count($roles), '?'));
    $stmt  = db()->prepare("SELECT DISTINCT email FROM users WHERE active = 1 AND awaiting_approval = 0 AND role IN ($marks) AND id <> ?");
    $stmt->execute(array_merge($roles, [$doc['user_id']]));

    $body = $doc['employee_name'] . ' uploaded a ' . document_type_label($doc['type']) . ': ' . $doc['title'] . "\n"
          . ($doc['expires_on'] ? 'Expires: ' . format_date($doc['expires_on']) . "\n" : '')
          . "\nThe document itself is only on the dashboard, not in email:\n"
          . SITE_URL . BASE_URL . '/dashboard/documents-review.php' . "\n";
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $email) {
        dashboard_send_email($email, 'Employee Dashboard: ' . header_safe($doc['employee_name']) . ' - ' . document_type_label($doc['type']), $body);
    }
}
