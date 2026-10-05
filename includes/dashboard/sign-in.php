<?php
// "Sign in with Microsoft" and "Sign in with Google" for the Employee Dashboard.
//
//   Microsoft  office staff, with their RJ Tide Microsoft 365 account. Only
//              accounts from RJ Tide's own Microsoft 365 organization work.
//   Google     field employees without a company email, with any Google
//              account. New Google sign-ins can't do anything until an Admin
//              approves them on the Users page.
//
// Each one appears on the sign-in page once its settings are in
// includes/secrets.php (see the README's "Employee Dashboard setup"):
//   Microsoft: MS_TENANT_ID, MS_CLIENT_ID, MS_CLIENT_SECRET
//   Google:    GOOGLE_CLIENT_ID, GOOGLE_CLIENT_SECRET
//
// Both use the same standard (OpenID Connect): login.php sends the browser to
// the service with a one-time state, nonce and PKCE code challenge; the service
// sends it back to auth-callback.php with a code, which is exchanged
// server-to-server for the signed-in person's identity.

const GUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

// A sign-in has to be finished within this long after clicking the button.
const SIGN_IN_TIME_LIMIT = 600;

// Exception code for a sign-in that ran past SIGN_IN_TIME_LIMIT, so the return
// page can tell the person why, rather than a general "didn't work".
const SIGN_IN_EXPIRED = 1;

// How many sign-ins one browser can have in progress at once (e.g. a
// double-clicked button, or the sign-in page open in two tabs).
const MAX_PENDING_SIGN_INS = 5;

function setting(string $name): string {
    return defined($name) ? (string) constant($name) : '';
}

// The sign-in services, keyed by the short name stored on each user.
//   label      button text and how the Users page names it
//   ready      true once its settings are filled in
//   authorize / token   the service's sign-in and code-exchange addresses
//   identity   checks the service's ID token and returns [account id, email, name]
//              for the person, or throws if anything doesn't check out
function sign_in_services(): array {
    $tenant = setting('MS_TENANT_ID');
    return [
        'microsoft' => [
            'label'         => 'Microsoft',
            // Must be RJ Tide's own tenant ID (a GUID), never "common" or
            // "organizations", which would let any company's accounts sign in.
            'ready'         => preg_match(GUID_PATTERN, $tenant) && setting('MS_CLIENT_ID') !== '' && setting('MS_CLIENT_SECRET') !== '',
            'client_id'     => setting('MS_CLIENT_ID'),
            'client_secret' => setting('MS_CLIENT_SECRET'),
            'authorize'     => 'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/authorize',
            'token'         => 'https://login.microsoftonline.com/' . rawurlencode($tenant) . '/oauth2/v2.0/token',
            'identity'      => function (array $c) use ($tenant): array {
                require_claims([
                    'tenant'  => strcasecmp((string) ($c['tid'] ?? ''), $tenant) === 0,
                    'issuer'  => ($c['iss'] ?? null) === 'https://login.microsoftonline.com/' . strtolower($tenant) . '/v2.0',
                    'user id' => is_string($c['oid'] ?? null) && $c['oid'] !== '',
                ]);
                $email = strtolower((string) ($c['email'] ?? $c['preferred_username'] ?? ''));
                return [$c['oid'], $email, (string) ($c['name'] ?? $email)];
            },
        ],
        'google' => [
            'label'         => 'Google',
            'ready'         => setting('GOOGLE_CLIENT_ID') !== '' && setting('GOOGLE_CLIENT_SECRET') !== '',
            'client_id'     => setting('GOOGLE_CLIENT_ID'),
            'client_secret' => setting('GOOGLE_CLIENT_SECRET'),
            'authorize'     => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token'         => 'https://oauth2.googleapis.com/token',
            'identity'      => function (array $c): array {
                require_claims([
                    'issuer'         => in_array($c['iss'] ?? null, ['https://accounts.google.com', 'accounts.google.com'], true),
                    'user id'        => is_string($c['sub'] ?? null) && $c['sub'] !== '',
                    'verified email' => ($c['email_verified'] ?? false) === true && is_string($c['email'] ?? null),
                ]);
                $email = strtolower($c['email']);
                return [$c['sub'], $email, (string) ($c['name'] ?? $email)];
            },
        ],
    ];
}

// The services that are set up and can be offered on the sign-in page.
function ready_sign_in_services(): array {
    return array_filter(sign_in_services(), fn($service) => $service['ready']);
}

function require_claims(array $checks): void {
    foreach ($checks as $what => $ok) {
        if (!$ok) {
            throw new RuntimeException("ID token failed the $what check");
        }
    }
}

// Where the services send people back to. Must exactly match the redirect
// address registered with Microsoft and with Google: always SITE_URL's address.
function sign_in_redirect_uri(): string {
    return SITE_URL . BASE_URL . '/dashboard/auth-callback.php';
}

function base64url(string $bytes): string {
    return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
}

// Sends the browser to the service's sign-in page. Never returns.
function start_sign_in(string $method, string $next): never {
    $service  = ready_sign_in_services()[$method] ?? null;
    if (!$service) {
        redirect(BASE_URL . '/dashboard/login.php');
    }
    $state    = base64url(random_bytes(32));
    $nonce    = base64url(random_bytes(32));
    $verifier = base64url(random_bytes(48));
    // Each sign-in in progress is kept under its own one-time state value, so
    // starting another one (double click, second tab) doesn't cancel this one.
    $pending = $_SESSION['pending_sign_ins'] ?? [];
    $pending[$state] = ['method' => $method, 'nonce' => $nonce, 'verifier' => $verifier, 'next' => $next, 'started' => time()];
    $_SESSION['pending_sign_ins'] = array_slice($pending, -MAX_PENDING_SIGN_INS, null, true);

    $separator = str_contains($service['authorize'], '?') ? '&' : '?';
    redirect($service['authorize'] . $separator . http_build_query([
        'client_id'             => $service['client_id'],
        'response_type'         => 'code',
        'redirect_uri'          => sign_in_redirect_uri(),
        'scope'                 => 'openid profile email',
        'state'                 => $state,
        'nonce'                 => $nonce,
        'code_challenge'        => base64url(hash('sha256', $verifier, true)),
        'code_challenge_method' => 'S256',
        'prompt'                => 'select_account',
    ]));
}

/**
 * Handles the service's redirect back to auth-callback.php.
 * @return array ['method', 'account_id', 'email', 'name', 'next'] for the signed-in person.
 * @throws RuntimeException with a message safe to log (never shown to visitors).
 */
function finish_sign_in(): array {
    // The returned state must match a sign-in this browser started. Each one
    // can only be finished once.
    $state   = is_string($_GET['state'] ?? null) ? $_GET['state'] : '';
    $pending = $_SESSION['pending_sign_ins'][$state] ?? null;
    unset($_SESSION['pending_sign_ins'][$state]);

    if (isset($_GET['error'])) {
        throw new RuntimeException('The sign-in service returned an error: ' . substr((string) $_GET['error'], 0, 100));
    }
    if (!$pending) {
        throw new RuntimeException('No matching sign-in in progress (state missing, unknown, or already used)');
    }
    if (time() - $pending['started'] > SIGN_IN_TIME_LIMIT) {
        throw new RuntimeException('Sign-in took longer than ' . (SIGN_IN_TIME_LIMIT / 60) . ' minutes', SIGN_IN_EXPIRED);
    }
    $service = ready_sign_in_services()[$pending['method']] ?? null;
    if (!$service) {
        throw new RuntimeException('Sign-in method ' . $pending['method'] . ' is no longer set up');
    }
    if (!is_string($_GET['code'] ?? null) || $_GET['code'] === '') {
        throw new RuntimeException('No authorization code returned');
    }

    // The ID token came straight from the service over a verified HTTPS
    // connection in exchange for this site's secret, so its claims can be read
    // directly (OpenID Connect Core 3.1.3.7). They're still checked below.
    $idToken = exchange_code($service, $_GET['code'], $pending['verifier']);
    $parts  = explode('.', $idToken);
    $claims = json_decode((string) base64_decode(strtr($parts[1] ?? '', '-_', '+/')), true);
    if (!is_array($claims)) {
        throw new RuntimeException('Unreadable ID token');
    }
    require_claims([
        'audience' => ($claims['aud'] ?? null) === $service['client_id'],
        'expiry'   => (int) ($claims['exp'] ?? 0) > time() - 60,
        'nonce'    => hash_equals($pending['nonce'], (string) ($claims['nonce'] ?? '')),
    ]);
    [$accountId, $email, $name] = $service['identity']($claims);

    return [
        'method'     => $pending['method'],
        'account_id' => $pending['method'] . ':' . $accountId,
        'email'      => $email,
        'name'       => $name,
        'next'       => $pending['next'],
    ];
}

// Trades the one-time code for the person's ID token, server-to-server.
function exchange_code(array $service, string $code, string $verifier): string {
    $ch = curl_init($service['token']);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query([
            'client_id'     => $service['client_id'],
            'client_secret' => $service['client_secret'],
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'redirect_uri'  => sign_in_redirect_uri(),
            'code_verifier' => $verifier,
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
    return $tokens['id_token'];
}
