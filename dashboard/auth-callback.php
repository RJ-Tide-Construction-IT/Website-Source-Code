<?php
// Microsoft and Google send people back here after they sign in (this exact
// address is registered as the redirect address with both services).
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

$login = BASE_URL . '/dashboard/login.php';

try {
    $who = finish_sign_in();
} catch (RuntimeException $e) {
    error_log('Employee Dashboard sign-in failed: ' . $e->getMessage());
    $message = $e->getCode() === SIGN_IN_EXPIRED
        ? 'Sign-in took too long (it needs to be finished within ' . (SIGN_IN_TIME_LIMIT / 60) . ' minutes). Please try again.'
        : 'Sign-in didn\'t work. Please try again.';
    if (dashboard_is_local()) {
        $message .= ' (Shown only on your own computer: ' . $e->getMessage() . '.)';
    }
    flash('error', $message);
    redirect($login);
}

switch (sign_in_user($who['method'], $who['account_id'], $who['email'], $who['name'])) {
    case 'signed_in':
        redirect(safe_next($who['next']));
    case 'off':
        flash('error', 'Your dashboard access has been turned off. If you think this is a mistake, contact the office.');
        redirect($login);
    case 'new_awaiting_approval':
        notify_admins_of_new_sign_in($who['name'], $who['email']);
        // fall through
    case 'awaiting_approval':
        flash('success', 'Thanks, ' . (strtok($who['name'], ' ') ?: $who['name']) . '. An Admin needs to approve your account before you can use the dashboard. Try signing in again once they have.');
        redirect($login);
}
