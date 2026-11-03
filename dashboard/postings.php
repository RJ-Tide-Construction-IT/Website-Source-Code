<?php
// Job Postings: every job posting, with links to add more or edit them.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('edit_postings');

$postings   = job_postings();
$openTitles = open_job_titles();

dashboard_page_start('Job Postings', $user);
?>
<p>Every job posting has its own page, and when marked as hiring it appears on the Careers page and
   in the job application&rsquo;s position list. To open or close several at once, use
   <a href="<?= BASE_URL ?>/dashboard/jobs.php">Job Openings</a>.</p>

<p><a class="btn" href="<?= BASE_URL ?>/dashboard/posting-edit.php">Add a job posting</a></p>

<?php if ($postings): ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Position</th><th>Hiring?</th><th>Last updated</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($postings as $id => $posting): ?>
            <tr>
                <td><?= htmlspecialchars($posting['title']) ?></td>
                <td><?= in_array($posting['title'], $openTitles, true) ? '<span class="badge badge--open">Listed</span>' : '<span class="badge">Not listed</span>' ?></td>
                <td><?php if (isset($posting['updated_at'])): ?><?= format_time($posting['updated_at']) ?><br><span class="dash-meta"><?= htmlspecialchars($posting['updated_by'] ?? '') ?></span><?php else: ?><span class="dash-meta">Original posting, not edited yet</span><?php endif; ?></td>
                <td class="dash-actions">
                    <a href="<?= BASE_URL ?>/dashboard/posting-edit.php?id=<?= urlencode($id) ?>">Edit</a>
                    <a href="<?= BASE_URL ?>/careers/posting.php?id=<?= urlencode($id) ?>" target="_blank" rel="noopener">View</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="dash-meta">No postings have been added yet.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
