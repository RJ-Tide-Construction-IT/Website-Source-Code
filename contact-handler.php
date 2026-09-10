<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/contact.php');
    exit;
}

// Honeypot — bots fill every field, humans never see this one.
if (!empty($_POST['website'])) {
    header('Location: ' . BASE_URL . '/contact.php?sent=1');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// "I'm interested in" is a checkbox group now (0 or more). Only keep values
// that match the known list — whatever's actually POSTed is user-controlled,
// so this can't be used to inject arbitrary text into the notification email.
$interests = array_values(array_intersect((array) ($_POST['interest'] ?? []), $GLOBALS['CONTACT_INTERESTS']));
$interest  = $interests ? implode(', ', $interests) : 'Not specified';

if ($name === '' || $email === '' || $message === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . BASE_URL . '/contact.php?error=1');
    exit;
}

$subject = 'New contact form submission from ' . header_safe($name);
$body    = "Name: $name\n"
         . "Email: $email\n"
         . "Phone: $phone\n"
         . "Interested in: $interest\n\n"
         . "Message:\n$message\n";

$sent = send_email(SITE_EMAIL, $subject, $body, $email, $name);

header('Location: ' . BASE_URL . '/contact.php?' . ($sent ? 'sent=1' : 'error=1'));
exit;
