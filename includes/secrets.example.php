<?php
// TEMPLATE ONLY, no real values here. The real file is includes/secrets.php,
// which is never committed to git or uploaded by the deploy: create it on
// your computer and on the server (via FTP / the hosting file manager) by
// copying this file and filling in the values.

// ---------- Email (contact form + job applications) ----------
// Brevo dashboard > SMTP & API > API Keys.
define('BREVO_API_KEY', '');

// ---------- Employee Dashboard: Microsoft 365 sign-in ----------
// From the app registration in the Microsoft Entra admin center, see the
// README's "Employee Dashboard setup". Leave blank until that's set up.
define('MS_TENANT_ID', '');      // "Directory (tenant) ID". RJ Tide's is 87167729-2aaa-42ad-95f6-cdff8fed8047 (public, not a secret)
define('MS_CLIENT_ID', '');      // "Application (client) ID"
define('MS_CLIENT_SECRET', '');  // Certificates & secrets > client secret "Value"

// ---------- Employee Dashboard: Google sign-in (field crews) ----------
// From the OAuth client in Google Cloud Console, see the README's
// "Employee Dashboard setup". Leave blank to hide the Google button.
define('GOOGLE_CLIENT_ID', '');      // ends in .apps.googleusercontent.com
define('GOOGLE_CLIENT_SECRET', '');

// ---------- Employee Dashboard: encryption key for direct deposit ----------
// Encrypts the bank numbers in direct deposit change requests. Until it's set,
// the direct deposit form stays switched off. To make one, run this once and
// paste the result between the quotes (keep it secret, like a password):
//     php -r "echo base64_encode(random_bytes(32)), PHP_EOL;"
// Keep the same key afterwards: if it changes, direct deposit requests still
// waiting for review can't be read (HR rejects them and the employee resends).
define('DASHBOARD_ENCRYPTION_KEY', '');

// ---------- Employee Dashboard: database (optional) ----------
// Leave these out (or blank) to use the built-in SQLite file in
// uploads/dashboard/, which needs no setup. To use a MySQL database from the
// hosting control panel instead, fill all three in:
define('DASHBOARD_DB_DSN', '');  // e.g. mysql:host=localhost;dbname=rjtide_dashboard;charset=utf8mb4
define('DASHBOARD_DB_USER', '');
define('DASHBOARD_DB_PASS', '');

// ---------- Employee Dashboard: local testing (your own computer ONLY) ----------
// true turns on the local test sign-in and keeps the dashboard at localhost,
// with emails written to uploads/dashboard/local-test-emails.log instead of
// sent. Only works on PHP's preview server (php -S) from your own computer, and
// never belongs in the server's secrets.php. See the README.
define('DASHBOARD_LOCAL_TESTING', false);
