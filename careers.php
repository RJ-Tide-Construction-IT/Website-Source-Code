<?php
$pageTitle = 'Careers';
$pageDescription = 'Join the RJ Tide family. Actively accepting applications for craftsman, millwright, and leadership positions.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// The full job list (with 'open' flags) lives in includes/config.php as
// $GLOBALS['JOBS'], shared with employment.php's Position dropdown so the
// two can't drift out of sync. To stop showing a position, edit it there.
$jobs = $GLOBALS['JOBS'];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/agriculture/web/sunrise-headhouse.jpg');">
    <div class="container"><h1>Be a part of the RJ Tide family!</h1></div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">Actively accepting applications for these positions!</h2>
        <?php $openJobs = array_filter($jobs, fn($job) => $job['open']); ?>
        <?php if ($openJobs): ?>
        <ul class="job-list">
            <?php foreach ($openJobs as $job): ?>
            <li><a href="<?= BASE_URL . $job['href'] ?>"><?= htmlspecialchars($job['title']) ?> <span>&rarr;</span></a></li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p style="text-align:center;color:var(--color-text-muted);">We don&rsquo;t have any open positions listed right now, but
           we&rsquo;re always interested in hearing from skilled tradespeople. Check back soon, or
           <a href="<?= BASE_URL ?>/employment.php">reach out anyway</a>.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section--muted">
    <div class="container">
        <h2 class="section-title">RJ Tide Employees Enjoy the Following Benefits:</h2>
        <ul style="max-width:700px;margin:0 auto;">
            <li>Competitive Pay &amp; Benefits &ndash; Fair compensation to attract and keep top talent.</li>
            <li>ESOP (Employee Stock Ownership Plan) &ndash; Every employee is an owner; success is shared.</li>
            <li>Health Insurance &amp; Life Insurance &ndash; Coverage and peace of mind for employees and families.</li>
            <li>Year-Round Job Security &ndash; No seasonal layoffs, consistent work all year.</li>
            <li>PTO (Paid Time Off) &ndash; Supporting work-life balance.</li>
            <li>Advancement &amp; Education Opportunities &ndash; Grow personally and professionally.</li>
            <li>Safety First &ndash; Incentives and programs to ensure everyone makes it home safe.</li>
            <li>Employee Referral Bonuses &ndash; Rewarding team members for bringing in top talent.</li>
        </ul>
        <p style="text-align:center;margin-top:2.5rem;"><strong>RJ Tide is an Equal Opportunity Employer. Women and Minorities are encouraged to apply.</strong></p>
        <div style="text-align:center;">
            <a href="<?= BASE_URL ?>/employment.php" class="btn">Apply Here</a>
        </div>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
