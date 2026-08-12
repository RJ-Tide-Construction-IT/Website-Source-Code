<?php
// Sends transactional email via the Brevo HTTP API instead of PHP's mail(), which
// can't be tested locally, depends on the host having sendmail configured, and
// tends to land in spam with no SPF/DKIM alignment.
require_once __DIR__ . '/secrets.php';

/**
 * @return bool true if Brevo accepted the message, false otherwise.
 */
function send_email(string $toEmail, string $subject, string $textBody, ?string $replyToEmail = null, ?string $replyToName = null): bool {
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

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'api-key: ' . BREVO_API_KEY,
            'Content-Type: application/json',
            'Accept: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 10,
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        error_log('send_email: cURL error — ' . $curlErr);
        return false;
    }
    if ($status < 200 || $status >= 300) {
        error_log('send_email: Brevo returned HTTP ' . $status . ' — ' . $response);
        return false;
    }
    return true;
}
