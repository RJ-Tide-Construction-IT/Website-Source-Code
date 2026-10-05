<?php
// Live-site address handling, which the local preview server can't show
// (there the dashboard always acts "local"). From the PHP command line it acts
// like the live server. Run by tests/run-tests.ps1 once per scenario:
//   php live-address.php redirect-uri       prints the sign-in return address
//   php live-address.php <host name>        prints "continues" or "redirected"
$root = dirname(__DIR__, 2);
require $root . '/includes/config.php';
require $root . '/tests/fixtures/fake-sign-in-settings.php';
require $root . '/includes/dashboard/db.php';
require $root . '/includes/dashboard/auth.php';
require $root . '/includes/dashboard/sign-in.php';

$scenario = $argv[1] ?? '';
$_SERVER['REQUEST_URI'] = '/dashboard/jobs.php?x=1';

if ($scenario === 'redirect-uri') {
    $_SERVER['HTTP_HOST'] = 'www.rjtide.com';
    $_SERVER['HTTPS'] = 'off'; // even if PHP can't see HTTPS on the host
    echo sign_in_redirect_uri();
    exit;
}

$_SERVER['HTTP_HOST'] = $scenario;
register_shutdown_function(function () {
    if (empty($GLOBALS['continued'])) {
        echo 'redirected';
    }
});
dashboard_require_main_address(); // exits (redirects) for any other address
$GLOBALS['continued'] = true;
echo 'continues';
