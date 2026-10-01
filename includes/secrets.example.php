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
define('MS_TENANT_ID', '');      // "Directory (tenant) ID", looks like 1a2b3c4d-....
define('MS_CLIENT_ID', '');      // "Application (client) ID"
define('MS_CLIENT_SECRET', '');  // Certificates & secrets > client secret "Value"

// ---------- Employee Dashboard: database (optional) ----------
// Leave these out (or blank) to use the built-in SQLite file in
// uploads/dashboard/, which needs no setup. To use a MySQL database from the
// hosting control panel instead, fill all three in:
define('DASHBOARD_DB_DSN', '');  // e.g. mysql:host=localhost;dbname=rjtide_dashboard;charset=utf8mb4
define('DASHBOARD_DB_USER', '');
define('DASHBOARD_DB_PASS', '');

// ---------- Local testing only ----------
// true lets you use dashboard/dev-login.php on YOUR OWN COMPUTER to try the
// dashboard without Microsoft sign-in. It's ignored on the live server
// regardless, but never set it there anyway.
define('DASHBOARD_DEV_LOGIN', false);
