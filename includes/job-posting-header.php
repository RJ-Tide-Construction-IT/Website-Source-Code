<?php
// Shared opening markup for career posting pages (careers/*.php), every
// posting repeats the same hero, back-link/Apply-Now row, and job-meta box,
// so it lives here once instead of copy-pasted into all nine files.
//
// Expects, set by the calling page before requiring this file:
//   $pageTitle (required), used for both the browser title (via header.php)
//               and the <h1>, since every posting's hero heading is just its
//               job title.
//   $jobMeta   (optional), ordered ['Label' => 'Value', ...] shown as the
//               salary/reports-to row under the Apply Now button. Omit it
//               (or leave it empty) to skip that row entirely, the Project
//               Manager / Estimator posting has none.
//   $pageDescription (optional), a per-posting meta description. Omit it to
//               get a sensible generated default instead of every posting
//               falling back to the generic site-wide description.
if (!isset($pageDescription)) {
    $pageDescription = "Apply for the $pageTitle position at RJ Tide Construction Company, Inc. in Lawton, Iowa.";
}
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
page_hero($pageTitle);
?>

<section>
    <div class="container container--narrow">
        <a href="<?= BASE_URL ?>/careers.php" class="back-link">&larr; Go back</a>
        <a href="<?= BASE_URL ?>/employment.php" class="btn posting-apply">Apply Now</a>
        <div class="clear"></div>

        <?php if (!empty($jobMeta)): ?>
        <div class="job-meta">
            <?php foreach ($jobMeta as $label => $value): ?>
            <div><strong><?= htmlspecialchars($label) ?></strong><?= htmlspecialchars($value) ?></div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
