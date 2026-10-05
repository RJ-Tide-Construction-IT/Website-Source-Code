<?php
// Loaded before every page while the tests run (PHP's auto_prepend_file).
// Settings defined here win over includes/secrets.php, so the tests behave the
// same on any computer: the test sign-in is on, no sign-in service is set up,
// and the email key is invalid so nothing a test does can send real email.
define('DASHBOARD_DEV_LOGIN', true);
define('BREVO_API_KEY', 'invalid-key-tests-never-send-email');
define('MS_TENANT_ID', '');
define('MS_CLIENT_ID', '');
define('MS_CLIENT_SECRET', '');
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('DASHBOARD_DB_DSN', ''); // always the built-in SQLite file
