<?php
// Job Postings: every job, whether it's being hired for (listed on the Careers
// page and in the job application's Position dropdown), and a one-click button
// to change that. Admins (edit_postings) can also add, edit, and delete postings
// (posting-edit.php). Unlisted postings still work for anyone with the link.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
// manage_jobs lets people in: every role with edit_postings also has
// manage_jobs (DASHBOARD_ROLES in config.php), so both kinds of user get here.
$user    = require_capability('manage_jobs');
$canEdit = user_can($user, 'edit_postings');
$self    = BASE_URL . '/dashboard/postings.php';

$postings = job_postings();

// One job at a time, sent as the state wanted (not "flip it"), so a double
// click or an out-of-date page can't undo someone else's change.
if (form_submitted()) {
    $title = $postings[posted_text($_POST, 'id')]['title'] ?? null;
    $open  = posted_text($_POST, 'open') === '1';
    if ($title === null) {
        flash('error', 'That posting wasn\'t found, it may have been deleted.');
    } elseif (set_job_open($title, $open, $user['name'])) {
        audit((int) $user['id'], 'job_openings', $user['name'] . ($open ? ' opened ' : ' closed ') . $title);
        flash('success', $title . ($open ? ' is now listed on the Careers page.' : ' is no longer listed on the Careers page.'));
    } else {
        flash('success', $title . ($open ? ' was already listed' : ' already wasn\'t listed') . ', nothing changed.');
    }
    redirect($self);
}

$openTitles = open_job_titles();
$saved      = saved_job_openings();

dashboard_page_start('Job Postings', $user);
?>
<p>Every job posting has its own page. Positions marked <strong>Hiring</strong> appear on the
   <a href="<?= BASE_URL ?>/careers.php" target="_blank" rel="noopener">Careers page</a> and in the job
   application&rsquo;s position list. Postings that aren&rsquo;t hiring still work if someone has the link,
   they just aren&rsquo;t listed.</p>
<?php if ($canEdit): ?>
<p><a class="btn" href="<?= BASE_URL ?>/dashboard/posting-edit.php">Add a job posting</a></p>
<?php endif; ?>

<?php if ($postings): ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Position</th><th>Hiring?</th><th>Last updated</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($postings as $id => $posting): $isOpen = in_array($posting['title'], $openTitles, true); ?>
            <tr>
                <td><?= htmlspecialchars($posting['title']) ?></td>
                <td class="dash-actions">
                    <form method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars((string) $id) ?>">
                        <input type="hidden" name="open" value="<?= $isOpen ? '0' : '1' ?>">
                        <?= $isOpen ? '<span class="badge badge--open">Hiring</span>' : '<span class="badge">Not hiring</span>' ?>
                        <button type="submit" class="btn btn--sm<?= $isOpen ? ' btn--outline-dark' : '' ?>"><?= $isOpen ? 'Stop hiring' : 'Start hiring' ?></button>
                    </form>
                </td>
                <td><?php if (isset($posting['updated_at'])): ?><?= format_time($posting['updated_at']) ?><br><span class="dash-meta"><?= htmlspecialchars($posting['updated_by'] ?? '') ?></span><?php else: ?><span class="dash-meta">Original posting, not edited yet</span><?php endif; ?></td>
                <td class="dash-actions">
                    <?php if ($canEdit): ?>
                    <a href="<?= BASE_URL ?>/dashboard/posting-edit.php?id=<?= urlencode((string) $id) ?>">Edit</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/careers/posting.php?id=<?= urlencode((string) $id) ?>" target="_blank" rel="noopener">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="dash-meta">No postings have been added yet.</p>
<?php endif; ?>
<?php if (is_array($saved) && isset($saved['updated_by'], $saved['updated_at'])): ?>
<p class="dash-meta">Hiring last changed by <?= htmlspecialchars($saved['updated_by']) ?> on <?= format_time($saved['updated_at']) ?>.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
