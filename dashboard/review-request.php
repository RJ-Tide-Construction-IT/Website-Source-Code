<?php
// One request, for HR: the full details, the decision buttons, and for direct
// deposit changes a "Show bank details" button (each viewing is logged).
// Nobody can decide, or view the bank details of, their own request.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('handle_requests');

$request = find_request((int) ($_GET['id'] ?? 0));
if (!$request) {
    flash('error', 'That request wasn\'t found.');
    redirect(BASE_URL . '/dashboard/review.php');
}
$own  = (int) $request['user_id'] === (int) $user['id'];
$self = BASE_URL . '/dashboard/review-request.php?id=' . (int) $request['id'];
$bank = null; // decrypted bank numbers, only after "Show bank details"

if (form_submitted()) {
    $action = posted_text($_POST, 'action');
    if ($own) {
        flash('error', 'Another HR person or an Admin needs to review your own request.');
        redirect($self);
    }
    if ($action === 'reveal' && $request['type'] === 'direct_deposit' && $request['status'] === 'pending') {
        $bank = decrypt_sensitive($request['sensitive']);
        audit((int) $user['id'], 'bank_details_viewed', $user['name'] . ' viewed the bank details on ' . $request['employee_name'] . '\'s direct deposit request');
        if ($bank === null) {
            flash('error', 'The bank details can\'t be read (the encryption key may have changed). Reject this request and ask the employee to send it again.');
            redirect($self);
        }
        // Falls through to show the page with the numbers; never stored in the session or redirected.
    } elseif (isset(request_decisions($request['type'])[$action])) {
        $note = posted_text($_POST, 'note');
        if (strlen($note) > 1000) {
            flash('error', 'The note is too long.');
            redirect($self);
        }
        decide_request($user, $request, $action, $note);
        flash('success', request_type_label($request['type']) . ' request from ' . $request['employee_name'] . ' marked as '
            . request_status_label($action) . '. They\'ve been emailed.'
            . ($request['type'] === 'direct_deposit' ? ' The bank numbers have been erased.' : ''));
        redirect(BASE_URL . '/dashboard/review.php');
    } else {
        flash('error', 'That request can\'t be changed that way.');
        redirect($self);
    }
}

$reviewer = null;
if ($request['reviewed_by']) {
    $stmt = db()->prepare('SELECT name FROM users WHERE id = ?');
    $stmt->execute([$request['reviewed_by']]);
    $reviewer = $stmt->fetchColumn() ?: null;
}

$d = $request['details'];
$rows = ['Employee' => $request['employee_name'] . ' (' . $request['employee_email'] . ')', 'Submitted' => format_time($request['created_at'])];
if ($request['type'] === 'time_off') {
    $rows += ['Type' => TIME_OFF_KINDS[$d['kind']] ?? $d['kind'], 'First day off' => format_date($d['start']), 'Last day off' => format_date($d['end']),
              'Hours' => $d['hours'] !== '' ? $d['hours'] . ' (partial day)' : 'Full days'];
} elseif ($request['type'] === 'address') {
    $rows += ['New address' => trim($d['street'] . "\n" . $d['street2']) . "\n" . $d['city'] . ', ' . $d['state'] . ' ' . $d['zip'],
              'Starting' => format_date($d['effective']), 'Phone' => $d['phone'] !== '' ? $d['phone'] : 'Not changing'];
} else {
    $rows += ['Bank' => $d['bank_name'], 'Account type' => ucfirst($d['account_type'])];
    if ($bank) {
        $rows += ['Routing number' => $bank['routing'], 'Account number' => $bank['account']];
    } else {
        $rows += ['Account' => 'Ending ' . $d['account_last4']];
    }
}
if ($d['note'] !== '') {
    $rows['Employee\'s note'] = $d['note'];
}

header('Cache-Control: no-store');
dashboard_page_start(request_type_label($request['type']) . ' Request', $user);
?>
<p><a class="back-link" href="<?= BASE_URL ?>/dashboard/review.php">&larr; All requests</a></p>

<p><span class="badge status--<?= htmlspecialchars($request['status']) ?>"><?= htmlspecialchars(request_status_label($request['status'])) ?></span>
   <?php if ($reviewer): ?><span class="dash-meta">by <?= htmlspecialchars($reviewer) ?> on <?= format_time($request['reviewed_at']) ?></span><?php endif; ?></p>
<?php if ($request['review_note']): ?><p class="dash-meta">HR note: <?= htmlspecialchars($request['review_note']) ?></p><?php endif; ?>

<table class="dash-table dash-details<?= $bank ? ' dash-details--revealed' : '' ?>">
    <?php foreach ($rows as $label => $value): ?>
    <tr><th><?= htmlspecialchars($label) ?></th><td><?= nl2br(htmlspecialchars($value)) ?></td></tr>
    <?php endforeach; ?>
</table>

<?php if ($bank): ?>
<p class="dash-meta">Viewing the bank details was recorded in the activity log. They&rsquo;ll be erased as soon as you mark this request done or rejected.</p>
<?php endif; ?>

<?php if ($request['status'] === 'pending' && $own): ?>
<div class="form-note dash-notice">This is your own request, so another HR person or an Admin needs to review it.</div>
<?php elseif ($request['status'] === 'pending'): ?>
    <?php if ($request['type'] === 'direct_deposit' && !$bank): ?>
    <form method="post" class="dash-reveal">
        <?= csrf_field() ?>
        <button type="submit" name="action" value="reveal" class="btn btn--outline-dark">Show bank details</button>
        <span class="dash-meta">To enter them into payroll. Each viewing is recorded.</span>
    </form>
    <?php endif; ?>

    <form method="post" class="dash-form dash-decision">
        <?= csrf_field() ?>
        <div class="form-field">
            <label for="note">Note to the employee (optional)</label>
            <textarea id="note" name="note" maxlength="1000"></textarea>
        </div>
        <div class="dash-buttons">
            <?php foreach (request_decisions($request['type']) as $status => $label): ?>
            <button type="submit" name="action" value="<?= $status ?>" class="btn<?= in_array($status, ['denied', 'rejected'], true) ? ' btn--outline-dark' : '' ?>"><?= htmlspecialchars($label) ?></button>
            <?php endforeach; ?>
        </div>
        <?php if ($request['type'] !== 'time_off'): ?>
        <p class="dash-meta">Mark as done once the change has been entered into payroll.</p>
        <?php endif; ?>
    </form>
<?php endif; ?>
<?php dashboard_page_end(); ?>
