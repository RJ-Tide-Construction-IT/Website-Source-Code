<?php
// Job Postings: the postings added on the dashboard, with links to add more or
// edit them. (The built-in postings are pages in careers/ and are edited there.)
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('edit_postings');

$postings   = custom_job_postings();
$openTitles = open_job_titles();

dashboard_page_start('Job Postings', $user);
?>
<p>Postings added here work like the rest: they get their own page, and when marked as hiring they
   appear on the Careers page and in the job application&rsquo;s position list. The
   <?= count($GLOBALS['JOBS']) ?> original postings are built into the website itself, so they don&rsquo;t
   appear in this list (you can still open or close them on
   <a href="<?= BASE_URL ?>/dashboard/jobs.php">Job Openings</a>).</p>

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
                <td><?= format_time($posting['updated_at'] ?? null) ?><br><span class="dash-meta"><?= htmlspecialchars($posting['updated_by'] ?? '') ?></span></td>
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
