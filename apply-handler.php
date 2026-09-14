<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

function redirect_with($params) {
    header('Location: ' . BASE_URL . '/employment.php?' . http_build_query($params));
    exit;
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

if ($name === '' || $phone === '' || $position === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(['error' => 'validation']);
}

if (empty($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK) {
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

// --- Keep a local backup record in case email delivery fails ---
// Fields are quoted for CSV and any leading =/+/-/@ is neutralized so a crafted
// applicant name/email can't turn into an executing formula when opened in Excel/Sheets.
$csvSafe = function ($v) {
    if (preg_match('/^[=+\-@]/', $v)) { $v = "'" . $v; }
    return '"' . str_replace('"', '""', $v) . '"';
};
$logLine = implode(',', array_map($csvSafe, [date('c'), $name, $email, $phone, $position, $safeName]));
file_put_contents($uploadDir . '../applications.csv', $logLine . "\n", FILE_APPEND | LOCK_EX);

// --- Email HR ---
$subject = 'New job application: ' . header_safe($position) . ', ' . header_safe($name);
$body    = "Name: $name\n"
         . "Email: $email\n"
         . "Phone: $phone\n"
         . "Position: $position\n\n"
         . "Message:\n$message\n\n"
         . "Resume saved on server as: uploads/resumes/$safeName\n";

send_email(SITE_EMAIL, $subject, $body, $email, $name);

redirect_with(['sent' => 1]);
