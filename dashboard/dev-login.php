<?php
// LOCAL TESTING ONLY: sign in as any name/email without Microsoft or Google,
// to try the dashboard on your own computer. You can pretend to be a company
// (Microsoft) account or a Google account, to try the approval process.
// Works only when dashboard_dev_login_allowed() is true (see
// includes/dashboard/layout.php), and the deploy never uploads this file.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

if (!dashboard_dev_login_allowed()) {
    http_response_code(404);
    exit;
}

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);

if (form_submitted()) {
    $email = strtolower(posted_text($_POST, 'email'));
    $name  = posted_text($_POST, 'name');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a name and a valid email.');
        redirect(BASE_URL . '/dashboard/dev-login.php?next=' . urlencode($next));
    }
    // A pretend Google account goes through the real Google rules (needs
    // approval, never an automatic Admin); a company account acts like Microsoft.
    $asGoogle = posted_text($_POST, 'as') === 'google';
    $result = $asGoogle
        ? sign_in_user('google', 'google:test-' . $email, $email, $name)
        : sign_in_user('test', 'test:' . $email, $email, $name);

    if ($result === 'signed_in') {
        redirect($next);
    }
    flash($result === 'off' ? 'error' : 'success', $result === 'off'
        ? 'That test account\'s access is turned off.'
        : 'That pretend Google account is waiting for an Admin to approve it (on the Users page).');
    redirect(BASE_URL . '/dashboard/login.php');
}

dashboard_page_start('Local Test Sign-In', null);
?>
<div class="dash-signin">
    <p>This page only exists on your own computer. Sign in as anyone to try the dashboard.
       As a company account, <strong><?= htmlspecialchars(implode(', ', $GLOBALS['DASHBOARD_ADMIN_EMAILS'])) ?></strong>
       is an Admin and any other email starts as an Employee. A pretend Google account waits for an
       Admin&rsquo;s approval, just like the real thing.</p>
    <form method="post" class="form-width dash-signin__form">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <div class="form-field"><label for="name">Name</label><input type="text" id="name" name="name" required></div>
        <div class="form-field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
        <div class="form-field">
            <label>Sign in as</label>
            <div class="checkbox-group">
                <label class="checkbox-option"><input type="radio" name="as" value="company" checked> Company account (like Microsoft)</label>
                <label class="checkbox-option"><input type="radio" name="as" value="google"> Google account</label>
            </div>
        </div>
        <button type="submit" class="btn">Sign in</button>
    </form>
</div>
<?php dashboard_page_end(); ?>
