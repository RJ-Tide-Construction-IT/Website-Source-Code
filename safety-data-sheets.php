<?php
$pageTitle = 'Safety Data Sheets';
$pageDescription = 'Safety Data Sheets (SDS) for products used on RJ Tide Construction projects, available to view or download as PDFs.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/safety-data-sheets.php';

// The main Safety Data Sheets page: one button per group, each going to that
// group's own page in safety-data-sheets/. Groups with no PDFs yet stay off this
// page. The group list and sheet titles live in includes/safety-data-sheets.php.
?>

<?php page_hero('Safety Data Sheets'); ?>

<section>
    <div class="container container--narrow">
        <p>These are the Safety Data Sheets (SDS) for products used on RJ Tide projects,
           grouped by type. Pick a group to see its sheets. Each sheet opens as a PDF in
           a new tab.</p>

        <ul class="service-links">
            <?php foreach (SDS_CATEGORIES as $slug => $category): ?>
            <?php if (sds_sheet_count($slug)): ?>
            <li><a href="<?= BASE_URL ?>/safety-data-sheets/<?= $slug ?>.php"><?= htmlspecialchars($category['label']) ?></a></li>
            <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <?php sds_contact_note(); ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
