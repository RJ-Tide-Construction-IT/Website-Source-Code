<?php
$pageTitle = 'Safety Data Sheets';
$pageDescription = 'Safety Data Sheets (SDS) for products used on RJ Tide Construction projects, available to view or download as PDFs.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// This page builds itself from assets/img/safety-data-sheets/: each numbered
// folder is a category, and every PDF inside it is listed automatically.
// Categories with no PDFs yet stay off the page. Revision dates are left off
// on purpose: the date printed on the sheet itself is the one that counts.

// The heading shown for each folder. A folder not listed here gets a heading
// made from its name, minus the number.
$headings = [
    '1-RIGHT-TO-KNOW' => 'Right to Know',
    '2-ACIDS' => 'Acids',
    '3-CAULKING-SEALANT' => 'Caulking & Sealant',
    '4-CEMETOUS-MATERIALS' => 'Cementitious Materials',
    '5-CURES-SEALERS' => 'Cures & Sealers',
    '6-FUELS-GAS-LUBRICANTS-OIL' => 'Fuels, Gas, Lubricants & Oil',
    '7-ADHESIVE-EPOXY-GLUE' => 'Adhesive, Epoxy & Glue',
    '8-BACKER-ROD-EXPANSION-JOINTS-INSULATION' => 'Backer Rod, Expansion Joints & Insulation',
    '9-PLASTIC-MEMBRANES' => 'Plastic Membranes',
    '10-PAINTS-PRIMERS-SOLVENTS' => 'Paints, Primers & Solvents',
    '11-CONCRETE-AND-MIXERS' => 'Concrete & Mixers',
    '12-POWER-ACTUATED-TOOLS' => 'Power Actuated Tools',
    '13-MISC' => 'Miscellaneous',
];

// Optional nicer titles, by PDF filename: product, then maker. A PDF not listed
// here is shown under its filename, with dashes and underscores as spaces.
$titles = [
    '3011000-10h.pdf' => ['1100 Concrete Curing Compound', 'W. R. Meadows'],
    '3116250.pdf' => ['1600-WHITE Series', 'W. R. Meadows'],
    'bright-kure-and-seal-voc-sds.pdf' => ['Bright Kure & Seal VOC', 'Sierra Corp/TK Products'],
    'Cure-Seal-WB-25-SDS.pdf' => ['Cure & Seal WB 25', 'SpecChem'],
    'ks_wb.pdf' => ['Kure and Seal Water Borne', 'Sierra Corp/TK Products'],
    'master-builders-masterkure-cc-180wb-sds.pdf' => ['MasterKure CC 180WB', 'Master Builders Solutions'],
    'master-builders-masterkure-cc-250sb-sds.pdf' => ['MasterKure CC 250SB', 'Master Builders Solutions'],
    'MASTERKUREHD200WB.pdf' => ['MasterKure HD 200WB (also Kure-N-Harden)', 'BASF'],
    'MASTERKUREHD300WBALSOLAPIDOLITH.pdf' => ['MasterKure HD 300WB (also Lapidolith)', 'BASF'],
    'SDS_BASF_MasterSeal615-Also700B.pdf' => ['MasterSeal 615 (also Hydrocide 700B)', 'BASF'],
    'US-600M_30740149_SDS_GEN_US_en_2-0.pdf' => ['US-600M', 'BASF'],
];

$root = __DIR__ . '/assets/img/safety-data-sheets';
$folders = is_dir($root) ? preg_grep('/^[^.]/', scandir($root)) : [];
natsort($folders); // Natural order, so folder 10 comes after folder 9.

$categories = [];
foreach ($folders as $folder) {
    if (!is_dir("$root/$folder")) {
        continue;
    }
    $sheets = [];
    foreach (preg_grep('/\.pdf$/i', scandir("$root/$folder")) as $file) {
        $sheets[$file] = implode(' · ', $titles[$file] ?? [strtr(pathinfo($file, PATHINFO_FILENAME), '-_', '  ')]);
    }
    if ($sheets) {
        natcasesort($sheets);
        $categories[] = [
            'folder' => rawurlencode($folder),
            'heading' => $headings[$folder] ?? ucwords(strtolower(strtr(preg_replace('/^\d+-/', '', $folder), '-_', '  '))),
            'sheets' => $sheets,
        ];
    }
}
?>

<?php page_hero('Safety Data Sheets'); ?>

<section>
    <div class="container container--narrow">
        <p>These are the Safety Data Sheets (SDS) for products used on RJ Tide projects,
           grouped by type. Each sheet opens as a PDF in a new tab.</p>

        <?php if (count($categories) > 1): ?>
        <ul class="service-links">
            <?php foreach ($categories as $category): ?>
            <li><a href="#<?= $category['folder'] ?>"><?= htmlspecialchars($category['heading']) ?></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

        <?php foreach ($categories as $category): ?>
        <h2 id="<?= $category['folder'] ?>" class="dash-subheading"><?= htmlspecialchars($category['heading']) ?></h2>
        <ul class="job-list">
            <?php foreach ($category['sheets'] as $file => $title): ?>
            <li><a href="<?= BASE_URL ?>/assets/img/safety-data-sheets/<?= $category['folder'] ?>/<?= rawurlencode($file) ?>" target="_blank" rel="noopener"><?= htmlspecialchars($title) ?> <span>&rarr;</span></a></li>
            <?php endforeach; ?>
        </ul>
        <?php endforeach; ?>

        <p class="spaced-top">In a medical emergency, call 911. Each sheet lists an emergency
           phone number for its product.</p>
        <p>Need a sheet that isn&rsquo;t listed? Call <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>
           or email <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</p>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
