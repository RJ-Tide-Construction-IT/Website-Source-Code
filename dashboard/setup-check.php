<?php
// Employee Dashboard setup check: open /dashboard/setup-check.php on the live
// site to confirm the server has everything the dashboard needs. Shows only
// pass/fail results, never secret values.
//
// Deliberately doesn't load the rest of the dashboard, and avoids newer PHP
// syntax, so it still runs (and can say so) on a server whose PHP is too old
// for the dashboard itself.
require_once dirname(__DIR__) . '/includes/config.php';
if (is_file(dirname(__DIR__) . '/includes/secrets.php')) {
    require_once dirname(__DIR__) . '/includes/secrets.php';
}
header('Cache-Control: no-store');

$checks = [];
function setup_check(array &$checks, string $label, bool $ok, string $fix = ''): bool {
    $checks[] = ['label' => $label, 'ok' => $ok, 'fix' => $fix];
    return $ok;
}

// PHP version
setup_check($checks, 'PHP 8.1 or newer', PHP_VERSION_ID >= 80100,
    'This server runs PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '. In the hosting control panel, switch the site to PHP 8.1 or newer.');

// Data folder (database file, sign-in sessions, job openings/postings)
$writable = false;
if (is_dir(DASHBOARD_DATA_DIR) || @mkdir(DASHBOARD_DATA_DIR, 0755, true)) {
    $probe = DASHBOARD_DATA_DIR . '/.write-test';
    $writable = @file_put_contents($probe, 'ok') !== false;
    @unlink($probe);
}
setup_check($checks, 'The site can save files in uploads/dashboard/', $writable,
    'Give the website permission to write to the uploads folder (the same permission resume uploads need).');

// Database
$useMysql = defined('DASHBOARD_DB_DSN') && DASHBOARD_DB_DSN !== '';
if ($useMysql) {
    if (setup_check($checks, 'MySQL support (pdo_mysql)', extension_loaded('pdo_mysql'), 'Ask the host to enable the pdo_mysql PHP extension.')) {
        $connected = false;
        try {
            new PDO(DASHBOARD_DB_DSN, DASHBOARD_DB_USER, DASHBOARD_DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 5]);
            $connected = true;
        } catch (Throwable $e) {
            error_log('Dashboard setup check: MySQL connection failed: ' . $e->getMessage());
        }
        setup_check($checks, 'Connects to the MySQL database', $connected,
            'Check the DASHBOARD_DB_DSN / _USER / _PASS lines in includes/secrets.php. The exact error is in the PHP error log.');
    }
} else {
    setup_check($checks, 'SQLite support (pdo_sqlite), for the built-in database', extension_loaded('pdo_sqlite'),
        'Ask the host to enable the pdo_sqlite PHP extension, or use a MySQL database instead (see includes/secrets.example.php).');
}

// Sign-in services (Microsoft for office staff, Google for field crews).
// Each is optional, but at least one must be set up.
setup_check($checks, 'Can make secure web requests (curl)', function_exists('curl_init'), 'Ask the host to enable the curl PHP extension.');

// setup_setting() and the ID-format checks below repeat small pieces of
// includes/dashboard/sign-in.php on purpose: that file needs PHP 8.1, and this
// page must still run on older PHP so it can report that the version is too old.
function setup_setting(string $name): string {
    return defined($name) ? (string) constant($name) : '';
}

// Fetches a page from a sign-in service (following redirects), to prove the
// server can reach it. Returns [reached?, HTTP status, decoded JSON, raw page].
function setup_fetch(string $url): array {
    if (!function_exists('curl_init')) {
        return [false, 0, null, ''];
    }
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS      => 5,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'Mozilla/5.0 (RJ Tide dashboard setup check)',
    ]);
    $response = (string) curl_exec($ch);
    $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);
    if ($curlErr) {
        error_log('Dashboard setup check: could not reach ' . $url . ': ' . $curlErr);
    }
    return [$curlErr === '', $status, json_decode($response, true), $response];
}

$guid      = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';
$msAny     = setup_setting('MS_TENANT_ID') . setup_setting('MS_CLIENT_ID') . setup_setting('MS_CLIENT_SECRET') !== '';
$googleAny = setup_setting('GOOGLE_CLIENT_ID') . setup_setting('GOOGLE_CLIENT_SECRET') !== '';

setup_check($checks, 'At least one sign-in method (Microsoft or Google) is set up', $msAny || $googleAny,
    'Add the Microsoft settings (MS_...) and/or the Google settings (GOOGLE_...) to includes/secrets.php on the server. See includes/secrets.example.php.');

if ($msAny) {
    $tenant = setup_setting('MS_TENANT_ID');
    if (setup_check($checks, 'Microsoft: all three settings are filled in', $tenant !== '' && setup_setting('MS_CLIENT_ID') !== '' && setup_setting('MS_CLIENT_SECRET') !== '',
            'MS_TENANT_ID, MS_CLIENT_ID and MS_CLIENT_SECRET are all needed.')) {
        $idsOk = setup_check($checks, 'Microsoft: Tenant ID and Client ID look right', preg_match($guid, $tenant) && preg_match($guid, setup_setting('MS_CLIENT_ID')),
            'Both should look like 1a2b3c4d-1234-5678-9abc-1234567890ab. Copy them from the app registration\'s Overview page in Microsoft Entra.');
        setup_check($checks, 'Microsoft: client secret is the secret Value (not its ID)', !preg_match($guid, setup_setting('MS_CLIENT_SECRET')),
            'MS_CLIENT_SECRET looks like an ID. Copy the secret\'s Value column instead (create a new secret if it\'s no longer shown).');
        if ($idsOk) {
            [$reached, $status, $doc] = setup_fetch('https://login.microsoftonline.com/' . rawurlencode($tenant) . '/v2.0/.well-known/openid-configuration');
            if (setup_check($checks, 'Microsoft: the server can reach Microsoft sign-in', $reached,
                    'The server couldn\'t connect to login.microsoftonline.com. The exact error is in the PHP error log; the host may need to allow outgoing HTTPS.')) {
                setup_check($checks, 'Microsoft: recognizes the Tenant ID', $status === 200 && strpos((string) ($doc['issuer'] ?? ''), strtolower($tenant)) !== false,
                    'Microsoft doesn\'t know this Tenant ID. Copy the Directory (tenant) ID again from the app registration\'s Overview page.');
            }
        }
    }
}

if ($googleAny) {
    $googleId = setup_setting('GOOGLE_CLIENT_ID');
    if (setup_check($checks, 'Google: both settings are filled in', $googleId !== '' && setup_setting('GOOGLE_CLIENT_SECRET') !== '',
            'GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET are both needed.')) {
        setup_check($checks, 'Google: Client ID looks right', (bool) preg_match('/^[0-9]+-[a-z0-9]+\.apps\.googleusercontent\.com$/', $googleId),
            'GOOGLE_CLIENT_ID should end in .apps.googleusercontent.com. Copy it from the OAuth client in Google Cloud Console.');
        setup_check($checks, 'Google: client secret isn\'t the Client ID', setup_setting('GOOGLE_CLIENT_SECRET') !== $googleId,
            'GOOGLE_CLIENT_SECRET is the same as the Client ID. Copy the Client secret from the OAuth client instead.');
        [$reached] = setup_fetch('https://accounts.google.com/.well-known/openid-configuration');
        if (setup_check($checks, 'Google: the server can reach Google sign-in', $reached,
                'The server couldn\'t connect to accounts.google.com. The exact error is in the PHP error log; the host may need to allow outgoing HTTPS.')) {
            // Google reports a wrong Client ID or an unregistered return address
            // right on its sign-in page, before anyone signs in, so both can be
            // checked here. (Microsoft only reports these after someone signs in.)
            $returnAddress = SITE_URL . BASE_URL . '/dashboard/auth-callback.php';
            [, , , $page] = setup_fetch('https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
                'client_id' => $googleId, 'response_type' => 'code', 'scope' => 'openid', 'redirect_uri' => $returnAddress,
            ]));
            if (setup_check($checks, 'Google: recognizes the Client ID', !preg_match('/invalid_client|deleted_client|OAuth client was not found/', $page),
                    'Google doesn\'t know this Client ID (or it was deleted). Copy the Client ID again from Google Cloud Console > Google Auth Platform > Clients.')) {
                setup_check($checks, 'Google: accepts the return address ' . $returnAddress, strpos($page, 'redirect_uri_mismatch') === false,
                    'In Google Cloud Console, open the OAuth client and add exactly ' . $returnAddress . ' under Authorized redirect URIs, then save.');
            }
        }
    }
}

$allOk = !in_array(false, array_column($checks, 'ok'), true);

$pageTitle   = 'Dashboard Setup Check';
$privatePage = true;
require dirname(__DIR__) . '/includes/header.php';
page_hero('Dashboard Setup Check');
?>
<section class="dashboard">
    <div class="container container--narrow">
        <?php if ($allOk): ?>
        <div class="form-note form-note--success">Everything checks out. Employees can sign in at
            <a href="<?= htmlspecialchars(SITE_URL . BASE_URL) ?>/dashboard/"><?= htmlspecialchars(SITE_URL . BASE_URL) ?>/dashboard/</a>.</div>
        <?php else: ?>
        <div class="form-note form-note--error">Some things need attention before employees can sign in. Each one says how to fix it.</div>
        <?php endif; ?>

        <ul class="setup-checks">
            <?php foreach ($checks as $check): ?>
            <li class="<?= $check['ok'] ? 'is-ok' : 'is-bad' ?>">
                <span class="setup-checks__mark" aria-hidden="true"><?= $check['ok'] ? '&#10003;' : '&#10007;' ?></span>
                <span><strong><?= htmlspecialchars($check['label']) ?></strong> (<?= $check['ok'] ? 'OK' : 'needs fixing' ?>)
                <?php if (!$check['ok']): ?><br><span class="dash-meta"><?= htmlspecialchars($check['fix']) ?></span><?php endif; ?></span>
            </li>
            <?php endforeach; ?>
        </ul>

        <p class="dash-meta">This page only shows pass/fail results, never passwords or keys. Checks that
           depend on an earlier one only appear once that one passes.</p>
    </div>
</section>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
