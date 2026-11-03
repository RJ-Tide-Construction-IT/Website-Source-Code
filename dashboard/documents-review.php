<?php
// Employee Documents: what employees have uploaded, limited to the types this
// person's role reviews (Safety: safety forms and certifications; HR: payroll
// forms; Admins: all). Safety also sees certifications that are about to
// expire. HR marks payroll forms processed, which deletes the file.
// Nobody processes or removes their own upload here (they can on My Documents).
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user  = require_login();
$types = reviewable_document_types($user);
if (!$types) {
    require_capability('safety_documents'); // shows the "Not Allowed" page
}
$self = BASE_URL . '/dashboard/documents-review.php';

if (form_submitted()) {
    $doc    = find_document((int) ($_POST['document_id'] ?? 0));
    $status = posted_text($_POST, 'action') === 'done' ? 'done' : 'removed';
    if (!$doc || !in_array($doc['type'], $types, true) || $doc['status'] !== 'received'
        || (int) $doc['user_id'] === (int) $user['id'] || ($status === 'done' && $doc['type'] !== 'payroll')) {
        flash('error', 'That document can\'t be changed that way.');
    } else {
        close_document($user, $doc, $status);
        flash('success', ($status === 'done' ? 'Marked ' . $doc['employee_name'] . '\'s ' . $doc['title'] . ' as processed. They\'ve been emailed'
                                             : 'Removed ' . $doc['employee_name'] . '\'s ' . $doc['title'])
            . ', and the file has been deleted.');
    }
    redirect($self);
}

$marks = implode(',', array_fill(0, count($types), '?'));
$load  = function (string $where, string $order, int $limit) use ($types, $marks): array {
    $stmt = db()->prepare("SELECT d.*, u.name AS employee_name FROM documents d JOIN users u ON u.id = d.user_id
                           WHERE d.type IN ($marks) AND $where ORDER BY $order LIMIT $limit");
    $stmt->execute($types);
    return $stmt->fetchAll();
};
$current  = $load("d.status = 'received'", 'd.id DESC', 300);
$recent   = $load("d.status <> 'received'", 'd.reviewed_at DESC', 30);
$expiring = in_array('certification', $types, true) ? expiring_certifications() : [];
$today    = gmdate('Y-m-d');

$table = function (array $docs, bool $closed) use ($user, $today) { ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Uploaded</th><th>Employee</th><th>Type</th><th>Document</th><th><?= $closed ? 'Status' : '' ?></th></tr></thead>
        <tbody>
        <?php foreach ($docs as $doc): ?>
            <tr>
                <td><?= format_time($doc['created_at']) ?></td>
                <td><?= htmlspecialchars($doc['employee_name']) ?></td>
                <td><?= htmlspecialchars(document_type_label($doc['type'])) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?>
                    <?php if ($doc['expires_on']): ?><br><span class="dash-meta"><?= $doc['expires_on'] < $today ? 'Expired' : 'Expires' ?> <?= format_date($doc['expires_on']) ?></span><?php endif; ?>
                    <?php if ($doc['note'] !== ''): ?><br><span class="dash-meta">Note: <?= htmlspecialchars($doc['note']) ?></span><?php endif; ?></td>
                <td class="dash-actions">
                    <?php if ($closed): ?>
                    <span class="badge status--<?= htmlspecialchars($doc['status']) ?>"><?= htmlspecialchars(document_status_label($doc['status'])) ?></span>
                    <span class="dash-meta"><?= format_time($doc['reviewed_at']) ?></span>
                    <?php else: ?>
                    <form method="post" onsubmit="return confirm('This permanently deletes the file. Continue?');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="document_id" value="<?= (int) $doc['id'] ?>">
                        <a href="<?= BASE_URL ?>/dashboard/document.php?id=<?= (int) $doc['id'] ?>" target="_blank" rel="noopener">Open</a>
                        <?php if ((int) $doc['user_id'] !== (int) $user['id']): ?>
                        <?php if ($doc['type'] === 'payroll'): ?>
                        <button type="submit" name="action" value="done" class="dash-link-button">Mark processed</button>
                        <?php else: ?>
                        <button type="submit" name="action" value="remove" class="dash-link-button">Remove</button>
                        <?php endif; ?>
                        <?php endif; ?>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php };

dashboard_page_start('Employee Documents', $user);
?>
<?php if (in_array('payroll', $types, true)): ?>
<p>Payroll forms can contain Social Security numbers. Once a form has been entered into payroll, mark it
   processed: the file is permanently deleted and the employee is emailed. Each time you open someone&rsquo;s
   payroll form, it&rsquo;s recorded in the activity log.</p>
<?php endif; ?>

<?php if ($expiring): ?>
<h2 class="dash-subheading dash-subheading--first">Certifications expiring within <?= CERT_WARNING_DAYS ?> days</h2>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Employee</th><th>Certification</th><th>Expires</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($expiring as $doc): $expired = $doc['expires_on'] < $today; ?>
            <tr>
                <td><?= htmlspecialchars($doc['employee_name']) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?></td>
                <td><span class="badge status--<?= $expired ? 'expired' : 'expiring' ?>"><?= $expired ? 'Expired' : 'Expires' ?> <?= format_date($doc['expires_on']) ?></span></td>
                <td><a href="<?= BASE_URL ?>/dashboard/document.php?id=<?= (int) $doc['id'] ?>" target="_blank" rel="noopener">Open</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="dash-meta">When the employee uploads the renewed certification under the same name, it drops off this list.</p>
<?php endif; ?>

<h2 class="dash-subheading<?= $expiring ? '' : ' dash-subheading--first' ?>">Current documents (<?= count($current) ?>)</h2>
<?php if ($current) { $table($current, false); } else { ?>
<p class="dash-meta">Nothing here right now.</p>
<?php } ?>

<?php if ($recent): ?>
<h2 class="dash-subheading">Recently processed or removed</h2>
<?php $table($recent, true); ?>
<?php endif; ?>
<?php dashboard_page_end(); ?>
