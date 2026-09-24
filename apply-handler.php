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
function field(string $key): string {
    $value = trim($_POST[$key] ?? '');
    return $value === '' ? '-' : $value;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/employment.php');
    exit;
}

// Honeypot
if (!empty($_POST['website'])) {
    redirect_with(['sent' => 1]);
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$position = trim($_POST['position'] ?? '');
$message  = trim($_POST['message'] ?? '');
$certify  = !empty($_POST['certify']);

if ($name === '' || $phone === '' || $position === '' || !$certify || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(['error' => 'validation']);
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

    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/resumes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

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
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/resumes/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
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
$postedEmployers = $_POST['employer'] ?? [];
for ($i = 1; $i <= 3; $i++) {
    $entry = $postedEmployers[$i] ?? [];
    $company = trim($entry['company'] ?? '');
    if ($company === '') continue;
    $employmentHistory[] = "  Employer #$i: $company\n"
        . '    Employed: ' . trim($entry['from'] ?? '-') . ' to ' . trim($entry['to'] ?? '-') . "\n"
        . '    Title & Duties: ' . trim($entry['title'] ?? '-') . "\n"
        . '    Wage: ' . trim($entry['starting_wage'] ?? '-') . ' starting, ' . trim($entry['final_wage'] ?? '-') . " final\n"
        . '    Reason for Leaving: ' . trim($entry['reason'] ?? '-') . "\n"
        . '    Supervisor: ' . trim($entry['supervisor'] ?? '-');
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

send_email(CAREERS_EMAIL, $subject, $body, $email, $name);

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
