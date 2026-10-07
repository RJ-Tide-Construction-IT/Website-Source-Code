<?php
// LOCAL TESTING ONLY: sign in as anyone, switch between people, and read the
// emails the dashboard would have sent. Works only when local_testing() is
// true (see includes/dashboard/auth.php), and the deploy never uploads this file.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';

if (!local_testing()) {
    http_response_code(404);
    exit;
}

$home = BASE_URL . '/dashboard/local-sign-in.php';

if (form_submitted()) {
    $action = posted_text($_POST, 'action');

    if ($action === 'clear_emails') {
        @unlink(LOCAL_TEST_EMAIL_LOG);
        flash('success', 'Cleared the test emails.');
        redirect($home);
    }

    if ($action === 'switch') {
        // Sign in as someone who already exists in the local test data.
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([(int) ($_POST['user_id'] ?? 0)]);
        $person = $stmt->fetch();
        $result = $person ? sign_in_user($person['sign_in_method'], $person['account_id'], $person['email'], $person['name']) : 'missing';
    } else {
        $email = strtolower(posted_text($_POST, 'email'));
        $name  = posted_text($_POST, 'name');
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a name and a valid email.');
            redirect($home);
        }
        // Acts exactly like a real Microsoft or Google sign-in (Admin rules,
        // Google approvals), with a test account id that only exists locally.
        $method = posted_text($_POST, 'as') === 'google' ? 'google' : 'microsoft';
        $result = sign_in_user($method, "$method:local-test-$email", $email, $name);
        if ($result === 'new_awaiting_approval') {
            notify_admins_of_new_sign_in($name, $email);
        }
    }

    if ($result === 'signed_in') {
        redirect(BASE_URL . '/dashboard/');
    }
    flash($result === 'off' || $result === 'missing' ? 'error' : 'success', [
        'off'     => 'That person\'s access is turned off (turn it back on from the Users page).',
        'missing' => 'That person wasn\'t found.',
    ][$result] ?? 'That Google-style account is waiting for an Admin to approve it on the Users page.');
    redirect($home);
}

$people = db()->query('SELECT id, name, email, role, sign_in_method, active, awaiting_approval FROM users ORDER BY name')->fetchAll();
$emails = is_file(LOCAL_TEST_EMAIL_LOG)
    ? array_reverse(array_filter(array_map(fn($line) => json_decode($line, true), file(LOCAL_TEST_EMAIL_LOG, FILE_IGNORE_NEW_LINES))))
    : [];
$me = current_user();

dashboard_page_start('Local Testing', $me);
?>
<div class="form-note dash-notice">This page only exists on your own computer, while
    <code>DASHBOARD_LOCAL_TESTING</code> is on in your local <code>secrets.php</code>. It&rsquo;s never on the
    live site. No real emails are sent while testing; they&rsquo;re listed at the bottom instead.</div>

<div class="local-grid">
    <div>
        <h2 class="dash-subheading dash-subheading--first">Sign in as someone new</h2>
        <form method="post" class="dash-form">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="new">
            <div class="form-field"><label for="name">Name</label><input type="text" id="name" name="name" required></div>
            <div class="form-field"><label for="email">Email</label><input type="email" id="email" name="email" required></div>
            <div class="form-field">
                <label>Sign in as a</label>
                <div class="checkbox-group">
                    <label class="checkbox-option"><input type="radio" name="as" value="microsoft" checked> Microsoft account (office staff)</label>
                    <label class="checkbox-option"><input type="radio" name="as" value="google"> Google account (needs approval)</label>
                </div>
            </div>
            <button type="submit" class="btn">Sign in</button>
            <p class="dash-meta">As a Microsoft account, <?= htmlspecialchars(implode(', ', $GLOBALS['DASHBOARD_ADMIN_EMAILS'])) ?>
               is an Admin; anyone else starts as an Employee (an Admin can change roles on the Users page).</p>
        </form>
    </div>

    <div>
        <h2 class="dash-subheading dash-subheading--first">Switch to someone</h2>
        <?php if ($people): ?>
        <div class="dash-table-wrap">
            <table class="dash-table">
                <?php foreach ($people as $person): ?>
                <tr>
                    <td><?= htmlspecialchars($person['name']) ?><br>
                        <span class="dash-meta"><?= htmlspecialchars(role_label($person['role'])) ?> &middot; <?= htmlspecialchars(sign_in_method_label($person['sign_in_method'])) ?>
                        <?= $person['awaiting_approval'] ? '&middot; waiting for approval' : (!$person['active'] ? '&middot; access off' : '') ?></span></td>
                    <td>
                        <?php if ($me && (int) $me['id'] === (int) $person['id']): ?>
                        <span class="dash-meta">Signed in</span>
                        <?php else: ?>
                        <form method="post"><?= csrf_field() ?>
                            <input type="hidden" name="action" value="switch">
                            <input type="hidden" name="user_id" value="<?= (int) $person['id'] ?>">
                            <button type="submit" class="btn btn--sm btn--outline-dark">Sign in as</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <?php else: ?>
        <p class="dash-meta">Nobody yet. Sign in as someone new to start.</p>
        <?php endif; ?>
    </div>
</div>

<h2 class="dash-subheading">Emails the dashboard would have sent (<?= count($emails) ?>)</h2>
<?php if ($emails): ?>
<form method="post" class="local-clear"><?= csrf_field() ?>
    <input type="hidden" name="action" value="clear_emails">
    <button type="submit" class="dash-link-button">Clear these</button>
</form>
<?php foreach (array_slice($emails, 0, 25) as $email): ?>
<div class="local-email">
    <p class="dash-meta"><?= format_time($email['sent_at']) ?> &middot; to <?= htmlspecialchars($email['to']) ?></p>
    <p class="local-email__subject"><?= htmlspecialchars($email['subject']) ?></p>
    <pre><?= htmlspecialchars($email['body']) ?></pre>
</div>
<?php endforeach; ?>
<?php else: ?>
<p class="dash-meta">None yet. They&rsquo;ll appear here when, for example, someone sends a request or HR decides one.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
