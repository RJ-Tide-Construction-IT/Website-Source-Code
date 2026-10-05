<?php
// Like the live site before any sign-in is set up: no Microsoft or Google
// settings, and the local test sign-in (and so the pretend pages) turned off.
define('DASHBOARD_DEV_LOGIN', false);
define('BREVO_API_KEY', 'invalid-key-tests-never-send-email');
define('MS_TENANT_ID', '');
define('MS_CLIENT_ID', '');
define('MS_CLIENT_SECRET', '');
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');
define('DASHBOARD_DB_DSN', '');
