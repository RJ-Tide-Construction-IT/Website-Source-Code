<?php
// Loaded first by every page in dashboard/. Pulls in the site config, secrets,
// and the dashboard's building blocks, then starts the sign-in session.
require_once dirname(__DIR__) . '/config.php';
if (is_file(dirname(__DIR__) . '/secrets.php')) {
    require_once dirname(__DIR__) . '/secrets.php';
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/accounts.php';
require_once __DIR__ . '/sign-in.php';
require_once __DIR__ . '/layout.php';

// Any unexpected error (e.g. the database can't be reached) shows a plain
// message instead of a blank page or PHP's error details, and the real error
// goes to the server's PHP error log. On your own computer (php -S), the real
// error is shown on the page too, since only you can see it there.
set_exception_handler(function (Throwable $e) {
    error_log('Employee Dashboard error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/plain; charset=UTF-8');
    }
    echo "Sorry, the Employee Dashboard ran into a problem. Please try again in a few minutes, "
       . "and let the office know if it keeps happening.";

    if (dashboard_is_local()) {
        echo "\n\nDetails (only shown on your own computer):\n" . $e->getMessage()
           . "\n" . $e->getFile() . ':' . $e->getLine();
        if (stripos($e->getMessage(), 'could not find driver') !== false) {
            echo "\n\nFix: PHP's SQLite support isn't turned on. Stop the server (Ctrl+C) and start it with:\n"
               . "    php -d extension=pdo_sqlite -S localhost:8000";
        }
    }
});

dashboard_require_main_address();
dashboard_start_session();
