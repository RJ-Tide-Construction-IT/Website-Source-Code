<?php
// Employee requests: Time Off, Address Changes, and Direct Deposit Changes.
//
// Employees submit them on dashboard/request-new.php and follow them on
// dashboard/requests.php. HR (anyone whose role has 'handle_requests') reviews
// them on dashboard/review.php: time off is approved or denied; address and
// direct deposit changes are marked done once entered into payroll, or rejected.
//
// Direct deposit bank numbers are the most sensitive thing the site holds:
//   - stored encrypted (AES-256-GCM) with DASHBOARD_ENCRYPTION_KEY from
//     includes/secrets.php, so a copy of the database alone is useless;
//   - shown only to HR, only on that request's review page, and each viewing
//     is recorded in the activity log;
//   - never put in emails or lists (only "account ending 1234");
//   - permanently erased the moment the request is no longer pending.
// Without a valid key, the direct deposit form is switched off.

const REQUEST_TYPES = [
    'time_off'       => 'Time Off',
    'address'        => 'Address Change',
    'direct_deposit' => 'Direct Deposit Change',
];

const TIME_OFF_KINDS = [
    'vacation' => 'Vacation / PTO',
    'sick'     => 'Sick',
    'unpaid'   => 'Unpaid',
    'other'    => 'Other',
];

const REQUEST_STATUSES = [
    'pending'   => 'Waiting for review',
    'approved'  => 'Approved',
    'denied'    => 'Denied',
    'done'      => 'Done',
    'rejected'  => 'Rejected',
    'cancelled' => 'Cancelled',
];

function request_type_label(string $type): string {
    return REQUEST_TYPES[$type] ?? $type;
}

function request_status_label(string $status): string {
    return REQUEST_STATUSES[$status] ?? $status;
}

// What HR can decide for each type of request: status => button label.
function request_decisions(string $type): array {
    return $type === 'time_off'
        ? ['approved' => 'Approve', 'denied' => 'Deny']
        : ['done' => 'Mark as done', 'rejected' => 'Reject'];
}

// 'Y-m-d' (or 'Y-m-d H:i:s' UTC) shown as e.g. "Oct 14, 2026".
function format_date(?string $date): string {
    if (!$date) {
        return '';
    }
    $d = DateTime::createFromFormat('Y-m-d', substr($date, 0, 10));
    return $d ? $d->format('M j, Y') : $date;
}

// ---------- Encryption for direct deposit bank numbers ----------

// The 32-byte key from DASHBOARD_ENCRYPTION_KEY (base64), or null if it's
// missing or malformed. See includes/secrets.example.php for how to make one.
function encryption_key(): ?string {
    $raw = base64_decode(setting('DASHBOARD_ENCRYPTION_KEY'), true);
    return ($raw !== false && strlen($raw) === 32) ? $raw : null;
}

function encryption_ready(): bool {
    return encryption_key() !== null
        && function_exists('openssl_encrypt')
        && in_array('aes-256-gcm', openssl_get_cipher_methods(), true);
}

function encrypt_sensitive(array $data): string {
    $key = encryption_key();
    if ($key === null) {
        throw new RuntimeException('DASHBOARD_ENCRYPTION_KEY is missing or invalid');
    }
    $iv = random_bytes(12);
    $tag = '';
    $ciphertext = openssl_encrypt(json_encode($data), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($ciphertext === false) {
        throw new RuntimeException('Encryption failed');
    }
    return 'v1:' . base64_encode($iv . $tag . $ciphertext);
}

// The decrypted data, or null if there's nothing stored, the key is missing,
// or the key has changed since it was stored (it then can't be read).
function decrypt_sensitive(?string $blob): ?array {
    $key = encryption_key();
    if (!$blob || $key === null || !str_starts_with($blob, 'v1:')) {
        return null;
    }
    $raw = base64_decode(substr($blob, 3), true);
    if ($raw === false || strlen($raw) < 29) {
        return null;
    }
    $plain = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    $data = $plain === false ? null : json_decode($plain, true);
    return is_array($data) ? $data : null;
}

// ---------- Checking what was entered ----------

// US bank routing numbers have a built-in check digit, which catches most typos.
function valid_routing_number(string $number): bool {
    if (!preg_match('/^\d{9}$/', $number)) {
        return false;
    }
    $d = array_map('intval', str_split($number));
    return (3 * ($d[0] + $d[3] + $d[6]) + 7 * ($d[1] + $d[4] + $d[7]) + ($d[2] + $d[5] + $d[8])) % 10 === 0;
}

function valid_date(string $date): bool {
    $d = DateTime::createFromFormat('!Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Checks a submitted request form.
 * @return array [details (safe to store as JSON), sensitive data or null, list of problems]
 */
function validate_request(string $type, array $post): array {
    $t = fn(string $key) => posted_text($post, $key);
    $errors = [];
    $tooLong = function (array $limits) use ($t, &$errors) {
        foreach ($limits as $key => [$label, $max]) {
            if (strlen($t($key)) > $max) {
                $errors[] = "$label is too long.";
            }
        }
    };

    if ($type === 'time_off') {
        $details = ['kind' => $t('kind'), 'start' => $t('start'), 'end' => $t('end'), 'hours' => $t('hours'), 'note' => $t('note')];
        if (!isset(TIME_OFF_KINDS[$details['kind']])) {
            $errors[] = 'Choose the type of time off.';
        }
        if (!valid_date($details['start']) || !valid_date($details['end'])) {
            $errors[] = 'Enter the first and last day off.';
        } elseif ($details['end'] < $details['start']) {
            $errors[] = 'The last day off can\'t be before the first day.';
        } elseif ((new DateTime($details['start']))->diff(new DateTime($details['end']))->days > 365) {
            $errors[] = 'Time off requests can cover up to a year.';
        }
        if ($details['hours'] !== '' && (!is_numeric($details['hours']) || $details['hours'] <= 0 || $details['hours'] > 24)) {
            $errors[] = 'Hours should be a number between 0 and 24 (leave it blank for full days).';
        }
        if ($details['kind'] === 'other' && $details['note'] === '') {
            $errors[] = 'Add a note explaining the time off when choosing "Other".';
        }
        $tooLong(['note' => ['The note', 1000]]);
        return [$details, null, $errors];
    }

    if ($type === 'address') {
        $details = [
            'street' => $t('street'), 'street2' => $t('street2'), 'city' => $t('city'),
            'state' => strtoupper($t('state')), 'zip' => $t('zip'), 'phone' => $t('phone'),
            'effective' => $t('effective'), 'note' => $t('note'),
        ];
        if ($details['street'] === '' || $details['city'] === '' || $details['state'] === '' || $details['zip'] === '') {
            $errors[] = 'Enter the full new address (street, city, state and ZIP).';
        }
        if ($details['zip'] !== '' && !preg_match('/^\d{5}(-?\d{4})?$/', $details['zip'])) {
            $errors[] = 'Enter a 5-digit ZIP code (or ZIP+4).';
        }
        if (!valid_date($details['effective'])) {
            $errors[] = 'Enter the date the new address takes effect.';
        }
        $tooLong(['street' => ['The street', 150], 'street2' => ['The apartment / unit', 100], 'city' => ['The city', 100],
                  'state' => ['The state', 30], 'phone' => ['The phone number', 30], 'note' => ['The note', 1000]]);
        return [$details, null, $errors];
    }

    if ($type === 'direct_deposit') {
        $routing = preg_replace('/\D/', '', $t('routing'));
        $account = preg_replace('/\D/', '', $t('account'));
        $details = [
            'bank_name'     => $t('bank_name'),
            'account_type'  => $t('account_type'),
            'account_last4' => substr($account, -4),
            'note'          => $t('note'),
        ];
        if (!encryption_ready()) {
            $errors[] = 'Direct deposit changes aren\'t available right now. Please contact the office.';
        }
        if ($details['bank_name'] === '') {
            $errors[] = 'Enter your bank\'s name.';
        }
        if (!valid_routing_number($routing)) {
            $errors[] = 'That routing number isn\'t valid. It\'s the 9-digit number at the bottom left of a check.';
        }
        if (!preg_match('/^\d{4,17}$/', $account)) {
            $errors[] = 'Enter your account number (4 to 17 digits).';
        } elseif ($account !== preg_replace('/\D/', '', $t('account_confirm'))) {
            $errors[] = 'The two account numbers don\'t match.';
        }
        if (!in_array($details['account_type'], ['checking', 'savings'], true)) {
            $errors[] = 'Choose checking or savings.';
        }
        if (empty($post['authorize'])) {
            $errors[] = 'Check the box authorizing the change.';
        }
        $tooLong(['bank_name' => ['The bank name', 100], 'note' => ['The note', 1000]]);
        return [$details, ['routing' => $routing, 'account' => $account], $errors];
    }

    return [[], null, ['Unknown request type.']];
}

// ---------- Reading ----------

// One request, with the employee's name and email, or null.
function find_request(int $id): ?array {
    $stmt = db()->prepare('SELECT r.*, u.name AS employee_name, u.email AS employee_email
                           FROM requests r JOIN users u ON u.id = r.user_id WHERE r.id = ?');
    $stmt->execute([$id]);
    $request = $stmt->fetch();
    if (!$request) {
        return null;
    }
    $request['details'] = json_decode($request['details'], true) ?: [];
    return $request;
}

// A one-line description, safe for lists and emails (never bank numbers).
function request_summary(array $request): string {
    $d = $request['details'];
    switch ($request['type']) {
        case 'time_off':
            $days = $d['start'] === $d['end'] ? format_date($d['start']) : format_date($d['start']) . ' to ' . format_date($d['end']);
            return (TIME_OFF_KINDS[$d['kind']] ?? $d['kind']) . ', ' . $days . ($d['hours'] !== '' ? ' (' . $d['hours'] . ' hours)' : '');
        case 'address':
            return trim($d['street'] . ' ' . $d['street2']) . ', ' . $d['city'] . ', ' . $d['state'] . ' ' . $d['zip']
                 . ' (from ' . format_date($d['effective']) . ')';
        case 'direct_deposit':
            return $d['bank_name'] . ', ' . $d['account_type'] . ' account ending ' . $d['account_last4'];
    }
    return '';
}

// ---------- Creating, deciding, cancelling ----------

function create_request(array $user, string $type, array $details, ?array $sensitive): int {
    db()->prepare('INSERT INTO requests (user_id, type, status, details, sensitive, created_at) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$user['id'], $type, 'pending', json_encode($details), $sensitive ? encrypt_sensitive($sensitive) : null, db_now()]);
    $id = (int) db()->lastInsertId();
    $request = find_request($id);
    audit((int) $user['id'], 'request_created', $user['name'] . ' submitted a ' . request_type_label($type) . ' request: ' . request_summary($request));
    notify_hr_of_request($request);
    return $id;
}

// HR's decision. Bank numbers are erased as soon as a request isn't pending.
function decide_request(array $reviewer, array $request, string $status, string $note): void {
    if ($request['status'] !== 'pending' || !isset(request_decisions($request['type'])[$status])) {
        throw new RuntimeException('That request can\'t be changed that way');
    }
    db()->prepare('UPDATE requests SET status = ?, review_note = ?, reviewed_by = ?, reviewed_at = ?, sensitive = NULL WHERE id = ? AND status = ?')
        ->execute([$status, $note, $reviewer['id'], db_now(), $request['id'], 'pending']);
    $request['status'] = $status;
    $request['review_note'] = $note;
    audit((int) $reviewer['id'], 'request_decided', $reviewer['name'] . ' marked ' . $request['employee_name'] . '\'s '
        . request_type_label($request['type']) . ' request as ' . request_status_label($status)
        . ($request['type'] === 'direct_deposit' ? ' (bank numbers erased)' : ''));
    notify_employee_of_decision($request);
}

// An employee withdrawing their own pending request.
function cancel_request(array $user, array $request): bool {
    if ((int) $request['user_id'] !== (int) $user['id'] || $request['status'] !== 'pending') {
        return false;
    }
    db()->prepare('UPDATE requests SET status = ?, sensitive = NULL WHERE id = ? AND status = ?')
        ->execute(['cancelled', $request['id'], 'pending']);
    audit((int) $user['id'], 'request_cancelled', $user['name'] . ' cancelled their ' . request_type_label($request['type']) . ' request');
    return true;
}

function pending_request_count(): int {
    return (int) db()->query("SELECT COUNT(*) FROM requests WHERE status = 'pending'")->fetchColumn();
}

// ---------- Emails (never include bank numbers) ----------
// Sent with dashboard_send_email() (accounts.php).

// Everyone whose role can handle requests.
function request_reviewer_emails(): array {
    $roles = array_keys(array_filter($GLOBALS['DASHBOARD_ROLES'], fn($r) => in_array('handle_requests', $r['can'], true)));
    $marks = implode(',', array_fill(0, count($roles), '?'));
    $stmt = db()->prepare("SELECT email FROM users WHERE active = 1 AND awaiting_approval = 0 AND role IN ($marks)");
    $stmt->execute($roles);
    return array_unique($stmt->fetchAll(PDO::FETCH_COLUMN));
}

function notify_hr_of_request(array $request): void {
    $body = $request['employee_name'] . ' submitted a ' . request_type_label($request['type']) . " request:\n\n"
          . request_summary($request) . "\n\n"
          . ($request['type'] === 'direct_deposit' ? "The bank details are only shown on the dashboard, not in email.\n\n" : '')
          . "Review it here:\n" . SITE_URL . BASE_URL . '/dashboard/review-request.php?id=' . $request['id'] . "\n";
    foreach (request_reviewer_emails() as $email) {
        dashboard_send_email($email, 'Employee Dashboard: ' . header_safe($request['employee_name']) . ' - ' . request_type_label($request['type']), $body);
    }
}

function notify_employee_of_decision(array $request): void {
    $body = 'Hi ' . (strtok($request['employee_name'], ' ') ?: $request['employee_name']) . ",\n\n"
          . 'Your ' . request_type_label($request['type']) . ' request (' . request_summary($request) . ') is now: '
          . request_status_label($request['status']) . ".\n"
          . ($request['review_note'] !== '' ? "\nNote from HR: " . $request['review_note'] . "\n" : '')
          . "\nSee your requests here:\n" . SITE_URL . BASE_URL . "/dashboard/requests.php\n";
    dashboard_send_email($request['employee_email'], 'Your ' . request_type_label($request['type']) . ' request: ' . request_status_label($request['status']), $body);
}
