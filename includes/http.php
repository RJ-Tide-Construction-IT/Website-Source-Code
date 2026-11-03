<?php
// Sends a POST request to another service (Brevo email, Cloudflare Turnstile,
// Microsoft/Google sign-in) and returns what came back:
//   ['status' => HTTP status code, 'body' => response text, 'error' => '' or why it couldn't connect]
// $fields is either an array (sent as a normal form submission) or a string
// (sent as-is, e.g. JSON, with a matching Content-Type in $headers).
// (dashboard/setup-check.php has its own copy on purpose, see the comment there.)
function http_post(string $url, $fields, array $headers = [], int $timeout = 10): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => is_array($fields) ? http_build_query($fields) : $fields,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => $timeout,
    ]);
    $body   = curl_exec($ch);
    $result = [
        'status' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
        'body'   => is_string($body) ? $body : '',
        'error'  => curl_error($ch),
    ];
    curl_close($ch);
    return $result;
}
