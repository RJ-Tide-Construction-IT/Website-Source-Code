<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

function redirect_with($params) {
    header('Location: ' . BASE_URL . '/employment.php?' . http_build_query($params));
    exit;
}

// Falls back to '-' for the email body so blank optional answers don't just
// leave an empty line, making it clear the applicant skipped the question
// rather than that something failed to save.
function or_dash(string $value): string {
    return $value === '' ? '-' : $value;
}

function field(string $key): string {
    return or_dash(posted_text($_POST, $key));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/employment.php');
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    redirect_with(['sent' => 1]);
}

$name     = posted_text($_POST, 'name');
$email    = posted_text($_POST, 'email');
$phone    = posted_text($_POST, 'phone');
$position = posted_text($_POST, 'position');
$certify  = !empty($_POST['certify']);

// Position must be one of the real job titles (or 'Other', the dropdown's
// catch-all), so arbitrary text can't be injected into the HR email subject.
// Closed jobs are still accepted in case someone had the form open when a
// posting was switched off.
$validPositions = array_merge(array_column($GLOBALS['JOBS'], 'title'), ['Other']);

if ($name === '' || $phone === '' || !in_array($position, $validPositions, true) || !$certify || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(['error' => 'validation']);
}

// Resumes and the applications.csv backup log both live under uploads/.
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Resume is optional now that the form captures full employment history
// directly, unlike the short version this replaced.
$safeName = null;
if (!empty($_FILES['resume']) && $_FILES['resume']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
        redirect_with(['error' => 'file']);
    }

    $resume = $_FILES['resume'];

    // --- Resume upload validation ---
    // Whitelist by real content (finfo), not the client-supplied name/extension,
    // and cap size, so this can't be used to plant an executable file.
    $maxBytes = 5 * 1024 * 1024;
    if ($resume['size'] > $maxBytes) {
        redirect_with(['error' => 'file']);
    }

    $allowedMime = [
        'application/pdf'                                                          => 'pdf',
        'application/msword'                                                       => 'doc',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'  => 'docx',
    ];

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($resume['tmp_name']);

    if (!isset($allowedMime[$mime])) {
        redirect_with(['error' => 'file']);
    }
    $extension = $allowedMime[$mime];

    $safeName = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = $uploadDir . $safeName;

    if (!move_uploaded_file($resume['tmp_name'], $destination)) {
        redirect_with(['error' => 'upload']);
    }
}

// --- Keep a local backup record in case email delivery fails ---
// Just the core contact fields, the full application detail lives in the
// email body below; this CSV is only a fallback way to reach someone.
// Fields are quoted for CSV and any leading =/+/-/@ is neutralized so a crafted
// applicant name/email can't turn into an executing formula when opened in Excel/Sheets.
$csvSafe = function ($v) {
    if (preg_match('/^[=+\-@]/', $v)) { $v = "'" . $v; }
    return '"' . str_replace('"', '""', $v) . '"';
};
$logLine = implode(',', array_map($csvSafe, [date('c'), $name, $email, $phone, $position, $safeName ?? '']));
file_put_contents($uploadDir . '../applications.csv', $logLine . "\n", FILE_APPEND | LOCK_EX);

// --- Build the language proficiency lines ---
$languageLine = function (string $prefix, string $languageLabel) {
    $language = field($prefix);
    if ($language === '-') return null;
    return "$languageLabel: $language (Speak: " . field($prefix . '_speak') . ', Read: ' . field($prefix . '_read') . ', Write: ' . field($prefix . '_write') . ')';
};

// --- Build the experience/skill level lines ---
// Option lists are shared with employment.php via includes/config.php.
$experienceLines = [];
foreach ($GLOBALS['APPLICATION_EXPERIENCE_SKILLS'] as $key => $label) {
    $experienceLines[] = "  $label: " . field('experience_' . $key);
}

$licenses = array_values(array_intersect((array) ($_POST['licenses'] ?? []), $GLOBALS['APPLICATION_LICENSES']));
$physical = array_values(array_intersect((array) ($_POST['physical'] ?? []), $GLOBALS['APPLICATION_PHYSICAL_REQUIREMENTS']));

// --- Build the employment history blocks ---
$employmentHistory = [];
$postedEmployers = is_array($_POST['employer'] ?? null) ? $_POST['employer'] : [];
for ($i = 1; $i <= 3; $i++) {
    $entry = is_array($postedEmployers[$i] ?? null) ? $postedEmployers[$i] : [];
    $job = fn(string $key) => or_dash(posted_text($entry, $key));
    $company = posted_text($entry, 'company');
    if ($company === '') continue;
    $employmentHistory[] = "  Employer #$i: $company\n"
        . '    Employed: ' . $job('from') . ' to ' . $job('to') . "\n"
        . '    Title & Duties: ' . $job('title') . "\n"
        . '    Wage: ' . $job('starting_wage') . ' starting, ' . $job('final_wage') . " final\n"
        . '    Reason for Leaving: ' . $job('reason') . "\n"
        . '    Supervisor: ' . $job('supervisor');
}

$languageLines = array_filter([
    $languageLine('primary_language', 'Primary'),
    $languageLine('other_language', 'Other'),
]);

// --- Email HR ---
$subject = 'New job application: ' . header_safe($position) . ', ' . header_safe($name);
$body = "PERSONAL INFORMATION\n"
      . "Name: $name\n"
      . "Email: $email\n"
      . "Phone: $phone\n"
      . 'Address: ' . field('address') . ', ' . field('city') . ', ' . field('state') . ' ' . field('zip') . "\n"
      . 'Emergency Contact: ' . field('emergency_contact') . "\n"
      . '18 or older: ' . field('age_18') . "\n\n"

      . "EMPLOYMENT DESIRED\n"
      . "Position: $position\n"
      . 'Date Available to Start: ' . field('start_date') . "\n"
      . 'Salary Desired: ' . field('salary_desired') . "\n"
      . 'Currently Employed: ' . field('employed_now') . "\n"
      . 'Applied/Worked Here Before: ' . field('applied_before') . ' (When: ' . field('applied_before_when') . ")\n"
      . 'Can Work Weekends: ' . field('weekends') . "\n"
      . 'Available for Overtime: ' . field('overtime') . "\n"
      . 'Willing to Travel: ' . field('travel') . "\n"
      . 'Willing to Stay Overnight: ' . field('overnight') . "\n\n"

      . "GENERAL\n"
      . 'Special Training: ' . field('special_training') . "\n"
      . 'Special Skills: ' . field('special_skills') . "\n"
      . ($languageLines ? implode("\n", $languageLines) . "\n" : "Languages: -\n") . "\n"

      . "EDUCATION\n"
      . 'High School: ' . field('high_school_city_state') . ', ' . field('high_school_degree') . "\n"
      . 'College: ' . field('college_city_state') . ', ' . field('college_degree') . "\n"
      . 'Other: ' . field('other_education_city_state') . ', ' . field('other_education_degree') . "\n\n"

      . "EXPERIENCE\n" . implode("\n", $experienceLines) . "\n\n"

      . "VALID LICENSES/CERTIFICATIONS\n" . ($licenses ? implode(', ', $licenses) : '-') . "\n\n"

      . "PHYSICAL REQUIREMENTS (able to do)\n" . ($physical ? implode(', ', $physical) : '-') . "\n\n"

      . "EMPLOYMENT HISTORY\n" . ($employmentHistory ? implode("\n\n", $employmentHistory) : '  Not provided') . "\n\n"

      . "MESSAGE\n" . field('message') . "\n\n"

      . ($safeName ? "Resume saved on server as: uploads/resumes/$safeName\n" : "No resume attached.\n");

// --- Printable PDF copy, laid out like a paper application ---
// If it can't be generated for any reason, the email still goes out with the
// plain-text version above, so an application is never lost over the PDF.
$attachments = [];
try {
    // A missing file is a fatal error require can't recover from, so check first
    // (e.g. if includes/vendor/ didn't make it onto the server).
    if (!is_file(__DIR__ . '/includes/vendor/autoload.php')) {
        throw new RuntimeException('includes/vendor/ is missing, the PDF library is not installed');
    }
    require_once __DIR__ . '/includes/application-pdf.php';
    $resumeNote = $safeName ? "Saved on server as uploads/resumes/$safeName" : 'No resume attached.';
    $pdfName = preg_replace('/[^\p{L}\p{N} .,\-]/u', '', "Application - $name - $position - " . date('Y-m-d'));
    $attachments[] = ['name' => $pdfName . '.pdf', 'content' => render_application_pdf($_POST, $resumeNote)];
    $body = "A printable copy of this application is attached as a PDF.\n\n" . $body;
} catch (Throwable $e) {
    error_log('apply-handler: application PDF failed, sending without it, ' . $e->getMessage());
}

send_email(CAREERS_EMAIL, $subject, $body, $email, $name, $attachments);

// --- Confirmation email back to the applicant ---
$confirmSubject = 'We received your application, ' . header_safe(SITE_NAME);
$confirmBody = "Hi $name,\n\n"
    . "Thanks for applying for the $position position at " . SITE_NAME . " We've received your "
    . "application and our team will review it soon. If it looks like a good fit, we'll reach out "
    . "to you directly to set up next steps.\n\n"
    . "If you have any questions in the meantime, just reply to this email.\n\n"
    . SITE_NAME . "\n"
    . SITE_PHONE . "\n";
send_email($email, $confirmSubject, $confirmBody, CAREERS_EMAIL, SITE_NAME);

redirect_with(['sent' => 1]);
