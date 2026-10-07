<?php
// My Requests: an employee's own time off, address and direct deposit
// requests, with buttons to submit new ones and cancel pending ones.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

if (form_submitted()) {
    $request = find_request((int) ($_POST['request_id'] ?? 0));
    if ($request && cancel_request($user, $request)) {
        flash('success', 'Cancelled your ' . request_type_label($request['type']) . ' request.');
    } else {
        flash('error', 'That request can\'t be cancelled (it may already have been reviewed).');
    }
    redirect(BASE_URL . '/dashboard/requests.php');
}

$stmt = db()->prepare('SELECT * FROM requests WHERE user_id = ? ORDER BY id DESC LIMIT 100');
$stmt->execute([$user['id']]);
$requests = array_map(function ($r) {
    $r['details'] = json_decode($r['details'], true) ?: [];
    return $r;
}, $stmt->fetchAll());

dashboard_page_start('My Requests', $user);
?>
<p>Send a request to HR. You&rsquo;ll get an email when it&rsquo;s been reviewed, and you can follow it here.</p>

<div class="dash-buttons request-actions">
    <a class="btn" href="<?= BASE_URL ?>/dashboard/request-new.php?type=time_off">Request time off</a>
    <a class="btn btn--outline-dark" href="<?= BASE_URL ?>/dashboard/request-new.php?type=address">Change my address</a>
    <?php if (encryption_ready()): ?>
    <a class="btn btn--outline-dark" href="<?= BASE_URL ?>/dashboard/request-new.php?type=direct_deposit">Change my direct deposit</a>
    <?php endif; ?>
</div>

<?php if ($requests): ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Submitted</th><th>Request</th><th>Details</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($requests as $request): ?>
            <tr>
                <td><?= format_time($request['created_at']) ?></td>
                <td><?= htmlspecialchars(request_type_label($request['type'])) ?></td>
                <td><?= htmlspecialchars(request_summary($request)) ?></td>
                <td>
                    <span class="badge status--<?= htmlspecialchars($request['status']) ?>"><?= htmlspecialchars(request_status_label($request['status'])) ?></span>
                    <?php if ($request['review_note']): ?><br><span class="dash-meta">HR: <?= htmlspecialchars($request['review_note']) ?></span><?php endif; ?>
                </td>
                <td>
                    <?php if ($request['status'] === 'pending'): ?>
                    <form method="post" onsubmit="return confirm('Cancel this request?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                        <button type="submit" class="dash-link-button">Cancel</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="dash-meta">You haven&rsquo;t sent any requests yet.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
