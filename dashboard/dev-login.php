<?php
// LOCAL TESTING ONLY: sign in as any name/email without Microsoft, to try the
// dashboard on your own computer before Microsoft sign-in is set up.
// Works only when dashboard_dev_login_allowed() is true (see
// includes/dashboard/layout.php), and the deploy never uploads this file.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

if (!dashboard_dev_login_allowed()) {
    http_response_code(404);
    exit;
}

$next = safe_next($_GET['next'] ?? $_POST['next'] ?? null);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(posted_text($_POST, 'email'));
    $name  = posted_text($_POST, 'name');
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash('error', 'Enter a name and a valid email.');
        redirect(BASE_URL . '/dashboard/dev-login.php?next=' . urlencode($next));
    }
    if (!sign_in_user('dev:' . $email, $email, $name)) {
        flash('error', 'That test account is deactivated.');
        redirect(BASE_URL . '/dashboard/login.php');
    }
    redirect($next);
}

dashboard_page_start('Local Test Sign-In', null);
?>
<div class="dash-signin">
    <p>This page only exists on your own computer. Sign in as anyone to try the dashboard.
       <strong><?= htmlspecialchars(implode(', ', $GLOBALS['DASHBOARD_ADMIN_EMAILS'])) ?></strong> signs in as an Admin;
       any other email starts as an Employee.</p>
    <form method="post" class="form-width" style="text-align:left;">
        <?= csrf_field() ?>
        <input type="hidden" name="next" value="<?= htmlspecialchars($next) ?>">
        <div class="form-field"><label for="name">Name</label><input type="text" id="name" name="name" required></div>
        <div class="form-field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
        <button type="submit" class="btn">Sign in</button>
    </form>
</div>
<?php dashboard_page_end(); ?>
