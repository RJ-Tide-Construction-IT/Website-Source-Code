<?php
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

$next = safe_next($_GET['next'] ?? null);

if (current_user()) {
    redirect($next);
}
if (isset($_GET['start']) && ms_configured()) {
    ms_start_login($next); // sends the browser to Microsoft
}

header('Cache-Control: no-store');
dashboard_page_start('Employee Sign In', null);
?>
<div class="dash-signin">
    <?php if (isset($_GET['signed_out'])): ?>
    <div class="form-note form-note--success">You&rsquo;re signed out.</div>
    <?php endif; ?>

    <p>Sign in with your RJ Tide Microsoft 365 account, the same one you use for work email.</p>

    <?php if (ms_configured()): ?>
    <a class="btn" href="<?= BASE_URL ?>/dashboard/login.php?start=1&amp;next=<?= urlencode($next) ?>">Sign in with Microsoft</a>
    <?php else: ?>
    <div class="form-note form-note--error">Microsoft sign-in isn&rsquo;t set up on this server yet. See &ldquo;Employee Dashboard setup&rdquo; in the README.</div>
    <?php endif; ?>

    <?php if (dashboard_dev_login_allowed()): ?>
    <p class="dash-meta"><a href="<?= BASE_URL ?>/dashboard/dev-login.php?next=<?= urlencode($next) ?>">Local test sign-in</a> (only works on your own computer)</p>
    <?php endif; ?>
</div>
<?php dashboard_page_end(); ?>
