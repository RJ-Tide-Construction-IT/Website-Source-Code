<?php
$pageTitle = 'Careers';
$pageDescription = 'Join the RJ Tide family. Actively accepting applications for craftsman, millwright, and leadership positions.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$jobs = [
    'Craftsman 1'                => '/careers/craftsman-1.php',
    'Craftsman 2'                => '/careers/craftsman-2.php',
    'Craftsman 3'                => '/careers/craftsman-3.php',
    'Concrete Foreman'           => '/careers/foreman.php',
    'Concrete Superintendent'    => '/careers/concrete-superintendent.php',
    'Millwright Superintendent'  => '/careers/millwright-superintendent.php',
    'Project Manager / Estimator'=> '/careers/project-manager-estimator.php',
    'Millwright 1'               => '/careers/millwright-1.php',
    'Millwright 2'               => '/careers/millwright-2.php',
];
?>

<section class="page-hero">
    <div class="container"><h1>Be a part of the RJ Tide family!</h1></div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">Actively accepting applications for these positions!</h2>
        <ul class="job-list">
            <?php foreach ($jobs as $title => $href): ?>
            <li><a href="<?= BASE_URL . $href ?>"><?= htmlspecialchars($title) ?> <span>&rarr;</span></a></li>
            <?php endforeach; ?>
        </ul>
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
        <p style="text-align:center;">RJ Tide is an Equal Opportunity Employer. Women and Minorities are encouraged to apply.</p>
        <div style="text-align:center;">
            <a href="<?= BASE_URL ?>/employment.php" class="btn">Apply Here</a>
        </div>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
