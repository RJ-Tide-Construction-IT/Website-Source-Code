<?php
$pageTitle = 'Safety Data Sheets';
$pageDescription = 'Safety Data Sheets (SDS) for products used on RJ Tide Construction projects, available to view or download as PDFs.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// One line per sheet, in alphabetical order by product. The PDFs live in
// assets/img/safety-data-sheets/. A PDF put in that folder doesn't show up
// here until it's added to this list. Revision dates are left off on purpose:
// the date printed on the sheet itself is the one that counts.
$sheets = [
    ['product' => '1100 Concrete Curing Compound', 'maker' => 'W. R. Meadows', 'file' => '3011000-10h.pdf'],
    ['product' => '1600-WHITE Series', 'maker' => 'W. R. Meadows', 'file' => '3116250.pdf'],
    ['product' => 'Bright Kure & Seal VOC', 'maker' => 'Sierra Corp/TK Products', 'file' => 'bright-kure-and-seal-voc-sds.pdf'],
    ['product' => 'Cure & Seal WB 25', 'maker' => 'SpecChem', 'file' => 'Cure-Seal-WB-25-SDS.pdf'],
    ['product' => 'Kure and Seal Water Borne', 'maker' => 'Sierra Corp/TK Products', 'file' => 'ks_wb.pdf'],
    ['product' => 'MasterKure CC 180WB', 'maker' => 'Master Builders Solutions', 'file' => 'master-builders-masterkure-cc-180wb-sds.pdf'],
    ['product' => 'MasterKure CC 250SB', 'maker' => 'Master Builders Solutions', 'file' => 'master-builders-masterkure-cc-250sb-sds.pdf'],
    ['product' => 'MasterKure HD 200WB (also Kure-N-Harden)', 'maker' => 'BASF', 'file' => 'MASTERKUREHD200WB.pdf'],
    ['product' => 'MasterKure HD 300WB (also Lapidolith)', 'maker' => 'BASF', 'file' => 'MASTERKUREHD300WBALSOLAPIDOLITH.pdf'],
    ['product' => 'MasterSeal 615 (also Hydrocide 700B)', 'maker' => 'BASF', 'file' => 'SDS_BASF_MasterSeal615-Also700B.pdf'],
    ['product' => 'US-600M', 'maker' => 'BASF', 'file' => 'US-600M_30740149_SDS_GEN_US_en_2-0.pdf'],
];
?>

<?php page_hero('Safety Data Sheets'); ?>

<section>
    <div class="container container--narrow">
        <p>These are the Safety Data Sheets (SDS) for products used on RJ Tide projects.
           Each sheet opens as a PDF in a new tab.</p>

        <ul class="job-list">
            <?php foreach ($sheets as $sheet): ?>
            <li><a href="<?= BASE_URL ?>/assets/img/safety-data-sheets/<?= rawurlencode($sheet['file']) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($sheet['product']) ?> &middot; <?= htmlspecialchars($sheet['maker']) ?> <span>&rarr;</span></a></li>
            <?php endforeach; ?>
        </ul>

        <p class="spaced-top">In a medical emergency, call 911. Each sheet lists an emergency
           phone number for its product.</p>
        <p>Need a sheet that isn&rsquo;t listed? Call <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>
           or email <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</p>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
