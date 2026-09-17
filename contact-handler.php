<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/contact.php');
    exit;
}

// Honeypot, bots fill every field, humans never see this one.
if (!empty($_POST['website'])) {
    header('Location: ' . BASE_URL . '/contact.php?sent=1');
    exit;
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// "I'm interested in" is a checkbox group now (0 or more). Only keep values
// that match the known list, whatever's actually POSTed is user-controlled,
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

// Route by checked interest, Agricultural and Concrete go straight to
// their department; anything else (Ag/Industrial Maintenance, Other, or no
// box checked) falls back to the default inbox. If someone checks more than
// one routed interest, every matching department gets a copy rather than
// picking just one and risking a lead going to the wrong person.
$routeTo = [
    'Agricultural' => CONTACT_EMAIL_AGRICULTURAL,
    'Concrete'     => CONTACT_EMAIL_CONCRETE,
];
$recipients = array_values(array_unique(array_intersect_key($routeTo, array_flip($interests))));
if (!$recipients) {
    $recipients = [CONTACT_EMAIL_DEFAULT];
}

$sent = true;
foreach ($recipients as $recipient) {
    $sent = send_email($recipient, $subject, $body, $email, $name) && $sent;
}

header('Location: ' . BASE_URL . '/contact.php?' . ($sent ? 'sent=1' : 'error=1'));
exit;
