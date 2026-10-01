<?php
// Microsoft sends people back here after they sign in (this exact address is
// registered as the "Redirect URI" in the Microsoft Entra app registration).
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

$login = BASE_URL . '/dashboard/login.php';
if (!ms_configured()) {
    redirect($login);
}

try {
    $who = ms_finish_login();
} catch (RuntimeException $e) {
    error_log('Employee Dashboard sign-in failed: ' . $e->getMessage());
    flash('error', 'Sign-in didn\'t work. Please try again.');
    redirect($login);
}

if (!sign_in_user($who['oid'], $who['email'], $who['name'])) {
    flash('error', 'Your dashboard access has been turned off. If you think this is a mistake, contact the office.');
    redirect($login);
}

redirect(safe_next($who['next']));
