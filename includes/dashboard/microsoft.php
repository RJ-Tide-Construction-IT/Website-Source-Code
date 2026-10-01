<?php
// "Sign in with Microsoft" for the Employee Dashboard, using the company's
// Microsoft 365 (Entra ID) accounts. Only accounts from RJ Tide's own
// Microsoft 365 organization are accepted.
//
// Needs three values in includes/secrets.php, from the app registration in the
// Microsoft Entra admin center (see the README's "Employee Dashboard setup"):
//   MS_TENANT_ID, MS_CLIENT_ID, MS_CLIENT_SECRET
//
// Flow: login.php sends the browser to Microsoft with a one-time state, nonce
// and PKCE code challenge; Microsoft sends it back to auth-callback.php with a
// code, which is exchanged server-to-server for the signed-in person's identity.

function ms_configured(): bool {
    foreach (['MS_TENANT_ID', 'MS_CLIENT_ID', 'MS_CLIENT_SECRET'] as $name) {
        if (!defined($name) || constant($name) === '') {
            return false;
        }
    }
    // Must be the organization's own tenant ID (a GUID), never "common" or
    // "organizations", which would let any company's accounts sign in.
    return (bool) preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', MS_TENANT_ID);
}

function ms_redirect_uri(): string {
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    return ($https ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/dashboard/auth-callback.php';
}

function base64url(string $bytes): string {
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

// Sends the browser to Microsoft's sign-in page. Never returns.
function ms_start_login(string $next): never {
    $state    = base64url(random_bytes(32));
    $nonce    = base64url(random_bytes(32));
    $verifier = base64url(random_bytes(48));
    $_SESSION['ms_login'] = ['state' => $state, 'nonce' => $nonce, 'verifier' => $verifier, 'next' => $next, 'started' => time()];

    $query = http_build_query([
        'client_id'             => MS_CLIENT_ID,
        'response_type'         => 'code',
        'redirect_uri'          => ms_redirect_uri(),
        'response_mode'         => 'query',
        'scope'                 => 'openid profile email',
        'state'                 => $state,
        'nonce'                 => $nonce,
        'code_challenge'        => base64url(hash('sha256', $verifier, true)),
        'code_challenge_method' => 'S256',
        'prompt'                => 'select_account',
    ]);
    redirect('https://login.microsoftonline.com/' . rawurlencode(MS_TENANT_ID) . '/oauth2/v2.0/authorize?' . $query);
}

/**
 * Handles Microsoft's redirect back to auth-callback.php.
 * @return array ['oid', 'email', 'name', 'next'] for the signed-in person.
 * @throws RuntimeException with a message safe to log (never shown to visitors).
 */
function ms_finish_login(): array {
    $pending = $_SESSION['ms_login'] ?? null;
    unset($_SESSION['ms_login']); // one use only

    if (isset($_GET['error'])) {
        throw new RuntimeException('Microsoft returned an error: ' . substr((string) $_GET['error'], 0, 100));
    }
    if (!$pending || time() - $pending['started'] > 600) {
        throw new RuntimeException('No sign-in in progress, or it took longer than 10 minutes');
    }
    if (!is_string($_GET['state'] ?? null) || !hash_equals($pending['state'], $_GET['state'])) {
        throw new RuntimeException('State mismatch');
    }
    if (!is_string($_GET['code'] ?? null) || $_GET['code'] === '') {
        throw new RuntimeException('No authorization code returned');
    }

    $ch = curl_init('https://login.microsoftonline.com/' . rawurlencode(MS_TENANT_ID) . '/oauth2/v2.0/token');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'client_id'     => MS_CLIENT_ID,
            'client_secret' => MS_CLIENT_SECRET,
            'grant_type'    => 'authorization_code',
            'code'          => $_GET['code'],
            'redirect_uri'  => ms_redirect_uri(),
            'code_verifier' => $pending['verifier'],
        ]),
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        throw new RuntimeException('Token request failed: ' . $curlErr);
    }
    $tokens = json_decode((string) $response, true);
    if ($status !== 200 || !is_string($tokens['id_token'] ?? null)) {
        throw new RuntimeException('Token request returned HTTP ' . $status . ': ' . substr((string) ($tokens['error_description'] ?? $response), 0, 300));
    }

    // The ID token came straight from Microsoft over a verified HTTPS
    // connection in exchange for this app's secret, so its claims can be read
    // directly (OpenID Connect Core 3.1.3.7), they're still checked below.
    $parts  = explode('.', $tokens['id_token']);
    $claims = json_decode((string) base64_decode(strtr($parts[1] ?? '', '-_', '+/')), true);
    if (!is_array($claims)) {
        throw new RuntimeException('Unreadable ID token');
    }

    $now = time();
    $checks = [
        'audience' => ($claims['aud'] ?? null) === MS_CLIENT_ID,
        'tenant'   => strcasecmp((string) ($claims['tid'] ?? ''), MS_TENANT_ID) === 0,
        'issuer'   => ($claims['iss'] ?? null) === 'https://login.microsoftonline.com/' . strtolower(MS_TENANT_ID) . '/v2.0',
        'expiry'   => (int) ($claims['exp'] ?? 0) > $now - 60,
        'nonce'    => hash_equals($pending['nonce'], (string) ($claims['nonce'] ?? '')),
        'user id'  => is_string($claims['oid'] ?? null) && $claims['oid'] !== '',
    ];
    foreach ($checks as $what => $ok) {
        if (!$ok) {
            throw new RuntimeException("ID token failed the $what check");
        }
    }

    $email = (string) ($claims['email'] ?? $claims['preferred_username'] ?? '');
    return [
        'oid'   => $claims['oid'],
        'email' => strtolower($email),
        'name'  => (string) ($claims['name'] ?? $email),
        'next'  => $pending['next'],
    ];
}
