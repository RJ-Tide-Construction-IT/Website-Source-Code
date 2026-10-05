<?php
// Like test-settings.php, but with made-up Microsoft and Google settings, for
// the tests that check the sign-in buttons and the setup check page. None of
// these are real, so no actual sign-in can succeed with them.
define('DASHBOARD_DEV_LOGIN', true);
define('BREVO_API_KEY', 'invalid-key-tests-never-send-email');
define('MS_TENANT_ID', '11111111-2222-3333-4444-555555555555');
define('MS_CLIENT_ID', 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee');
define('MS_CLIENT_SECRET', 'not-a-real-secret');
define('GOOGLE_CLIENT_ID', '123456789012-abcdefghij.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'not-a-real-secret');
define('DASHBOARD_DB_DSN', '');
