<?php
// The shared layout for every Safety Data Sheet group page. Each small file in
// safety-data-sheets/ sets $sdsSlug to its group's short name, then loads this.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/safety-data-sheets.php';

// A page file whose short name isn't in the group list shows "Page Not Found".
$category = SDS_CATEGORIES[$sdsSlug] ?? null;
if (!$category) {
    require $_SERVER['DOCUMENT_ROOT'] . '/404.php';
    exit;
}

$pageTitle = $category['label'] . ' Safety Data Sheets';
$pageDescription = $category['label'] . ' Safety Data Sheets (SDS) for products used on RJ Tide Construction projects, available to view or download as PDFs.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$sheets = sds_sheets($sdsSlug);
?>

<?php page_hero($pageTitle, null, '<a href="' . BASE_URL . '/safety-data-sheets.php" class="btn btn--outline page-hero__action">&larr; All Safety Data Sheets</a>'); ?>

<section>
    <div class="container container--narrow">
        <?php if ($sheets): ?>
        <p>Each sheet opens as a PDF in a new tab.</p>
        <ul class="job-list">
            <?php foreach ($sheets as $sheet): ?>
            <li><a href="<?= $sheet['url'] ?>" target="_blank" rel="noopener"><?= htmlspecialchars($sheet['label']) ?> <span>&rarr;</span></a></li>
            <?php endforeach; ?>
        </ul>
        <?php else: ?>
        <p>No sheets have been added to this group yet.</p>
        <?php endif; ?>

        <?php sds_contact_note(); ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
