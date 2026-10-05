<?php
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

$next     = safe_next($_GET['next'] ?? null);
$services = ready_sign_in_services();

if (current_user()) {
    redirect($next);
}
if (is_string($_GET['with'] ?? null) && isset($services[$_GET['with']])) {
    start_sign_in($_GET['with'], $next); // sends the browser to Microsoft or Google
}

$startUrl = fn(string $method) => BASE_URL . '/dashboard/login.php?with=' . $method . '&next=' . urlencode($next);

header('Cache-Control: no-store');
dashboard_page_start('Employee Sign In', null);
?>
<div class="dash-signin">
    <?php if (isset($_GET['signed_out'])): ?>
    <div class="form-note form-note--success">You&rsquo;re signed out.</div>
    <?php endif; ?>

    <?php if (!$services): ?>
    <div class="form-note form-note--error">Employee sign-in isn&rsquo;t available yet. Please check back soon, or contact the office.</div>
    <?php endif; ?>

    <?php if (isset($services['microsoft'])): ?>
    <div class="dash-signin__option">
        <a class="btn" href="<?= htmlspecialchars($startUrl('microsoft')) ?>">Sign in with Microsoft</a>
        <p class="dash-meta">For office staff, with your RJ Tide work account (the one you use for work email).</p>
    </div>
    <?php endif; ?>

    <?php if (isset($services['google'])): ?>
    <div class="dash-signin__option">
        <a class="btn btn--outline-dark" href="<?= htmlspecialchars($startUrl('google')) ?>">Sign in with Google</a>
        <p class="dash-meta">For employees without a company email, with your Google (Gmail) account.
           The first time, an Admin will need to approve your account.</p>
    </div>
    <?php endif; ?>
</div>
<?php dashboard_page_end(); ?>
