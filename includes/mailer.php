<?php
// Sends transactional email via the Brevo HTTP API instead of PHP's mail(), which
// can't be tested locally, depends on the host having sendmail configured, and
// tends to land in spam with no SPF/DKIM alignment.
require_once __DIR__ . '/secrets.php';
require_once __DIR__ . '/http.php';

/**
 * @param array $attachments Optional files, each ['name' => 'file.pdf', 'content' => raw file bytes].
 * @return bool true if Brevo accepted the message, false otherwise.
 */
function send_email(string $toEmail, string $subject, string $textBody, ?string $replyToEmail = null, ?string $replyToName = null, array $attachments = []): bool {
    if (!defined('BREVO_API_KEY') || BREVO_API_KEY === '') {
        error_log('send_email: BREVO_API_KEY is not configured in includes/secrets.php');
        return false;
    }

    $payload = [
        'sender'      => ['name' => SITE_NAME, 'email' => SITE_EMAIL],
        'to'          => [['email' => $toEmail]],
        'subject'     => $subject,
        'textContent' => $textBody,
    ];
    if ($replyToEmail) {
        $payload['replyTo'] = array_filter([
            'email' => $replyToEmail,
            'name'  => $replyToName,
        ]);
    }
    foreach ($attachments as $file) {
        $payload['attachment'][] = ['name' => $file['name'], 'content' => base64_encode($file['content'])];
    }

    $result = http_post('https://api.brevo.com/v3/smtp/email', json_encode($payload), [
        'api-key: ' . BREVO_API_KEY,
        'Content-Type: application/json',
        'Accept: application/json',
    ]);

    if ($result['error']) {
        error_log('send_email: cURL error, ' . $result['error']);
        return false;
    }
    if ($result['status'] < 200 || $result['status'] >= 300) {
        error_log('send_email: Brevo returned HTTP ' . $result['status'] . ', ' . $result['body']);
        return false;
    }
    return true;
}
