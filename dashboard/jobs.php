<?php
// Job Openings: check the positions currently being hired for. Checked jobs
// are listed on the public Careers page and in the job application's Position
// dropdown. The jobs are the built-in postings ($GLOBALS['JOBS'] in
// includes/config.php) plus any added on the Job Postings page.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('manage_jobs');

$titles = array_column(all_jobs(), 'title');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $before = open_job_titles();
    $posted = array_filter((array) ($_POST['open'] ?? []), 'is_string');
    $open   = array_values(array_intersect($titles, $posted)); // only real job titles, in list order
    save_job_openings($open, $user['name']);

    $opened = array_diff($open, $before);
    $closed = array_diff($before, $open);
    if ($opened || $closed) {
        $changes = array_merge(
            array_map(fn($t) => "opened $t", $opened),
            array_map(fn($t) => "closed $t", $closed)
        );
        audit((int) $user['id'], 'job_openings', $user['name'] . ' ' . implode(', ', $changes));
        flash('success', 'Saved. The Careers page now lists ' . count($open) . ' open ' . (count($open) === 1 ? 'position' : 'positions') . '.');
    } else {
        flash('success', 'No changes to save.');
    }
    redirect(BASE_URL . '/dashboard/jobs.php');
}

$jobs  = jobs_with_status();
$saved = saved_job_openings();

dashboard_page_start('Job Openings', $user);
?>
<p>Check every position you&rsquo;re hiring for right now. Checked positions appear on the
   <a href="<?= BASE_URL ?>/careers.php" target="_blank" rel="noopener">Careers page</a> and in the job
   application&rsquo;s position list. Unchecked postings still work if someone has the link, they just
   aren&rsquo;t listed.</p>
<?php if (user_can($user, 'edit_postings')): ?>
<p>Need a position that isn&rsquo;t listed? <a href="<?= BASE_URL ?>/dashboard/posting-edit.php">Add a job posting</a>.</p>
<?php endif; ?>

<form method="post">
    <?= csrf_field() ?>
    <div class="dash-table-wrap">
        <table class="dash-table">
            <thead><tr><th>Hiring?</th><th>Position</th><th>Posting</th></tr></thead>
            <tbody>
            <?php foreach ($jobs as $i => $job): ?>
                <tr>
                    <td><input type="checkbox" id="job<?= $i ?>" name="open[]" value="<?= htmlspecialchars($job['title']) ?>"<?= $job['open'] ? ' checked' : '' ?>></td>
                    <td><label for="job<?= $i ?>"><?= htmlspecialchars($job['title']) ?></label></td>
                    <td><a href="<?= BASE_URL . htmlspecialchars($job['href']) ?>" target="_blank" rel="noopener">View posting</a></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <p><button type="submit" class="btn">Save</button></p>
</form>

<?php if (is_array($saved) && isset($saved['updated_by'], $saved['updated_at'])): ?>
<p class="dash-meta">Last changed by <?= htmlspecialchars($saved['updated_by']) ?> on <?= format_time($saved['updated_at']) ?>.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
