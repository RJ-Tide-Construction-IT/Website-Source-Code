<?php
// Review Requests (HR): everything waiting for review, oldest first, plus the
// most recently decided ones. Each opens on review-request.php.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('handle_requests');

$load = function (string $sql): array {
    return array_map(function ($r) {
        $r['details'] = json_decode($r['details'], true) ?: [];
        return $r;
    }, db()->query($sql)->fetchAll());
};
$base = 'SELECT r.*, u.name AS employee_name FROM requests r JOIN users u ON u.id = r.user_id';
$pending = $load("$base WHERE r.status = 'pending' ORDER BY r.id");
$recent  = $load("$base WHERE r.status <> 'pending' ORDER BY r.id DESC LIMIT 30");

$table = function (array $rows, bool $showStatus) { ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Submitted</th><th>Employee</th><th>Request</th><th>Details</th><?= $showStatus ? '<th>Status</th>' : '' ?><th></th></tr></thead>
        <tbody>
        <?php foreach ($rows as $request): ?>
            <tr>
                <td><?= format_time($request['created_at']) ?></td>
                <td><?= htmlspecialchars($request['employee_name']) ?></td>
                <td><?= htmlspecialchars(request_type_label($request['type'])) ?></td>
                <td><?= htmlspecialchars(request_summary($request)) ?></td>
                <?php if ($showStatus): ?>
                <td><span class="badge status--<?= htmlspecialchars($request['status']) ?>"><?= htmlspecialchars(request_status_label($request['status'])) ?></span></td>
                <?php endif; ?>
                <td><a href="<?= BASE_URL ?>/dashboard/review-request.php?id=<?= (int) $request['id'] ?>"><?= $showStatus ? 'View' : 'Review' ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php };

dashboard_page_start('Review Requests', $user);
?>
<h2 class="dash-subheading dash-subheading--first">Waiting for review (<?= count($pending) ?>)</h2>
<?php if ($pending) { $table($pending, false); } else { ?>
<p class="dash-meta">Nothing waiting. Nice.</p>
<?php } ?>

<h2 class="dash-subheading">Recently reviewed</h2>
<?php if ($recent) { $table($recent, true); } else { ?>
<p class="dash-meta">Nothing yet.</p>
<?php } ?>
<?php dashboard_page_end(); ?>
