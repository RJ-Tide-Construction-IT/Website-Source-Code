<?php
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

// Sign-out is a form button (POST with a form token) rather than a plain link,
// so another website can't sign employees out by embedding the link.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/dashboard/');
}
verify_csrf();
sign_out();
redirect(BASE_URL . '/dashboard/login.php?signed_out=1');
