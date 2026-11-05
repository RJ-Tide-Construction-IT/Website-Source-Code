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
    'Sure-Klean-Heavy-Duty-Paint-Stripper.pdf' => ['Sure Klean Heavy Duty Paint Stripper', 'PROSOCO'],
    'MasterSeal-NP-1-Aluminum-Gray.pdf' => ['MasterSeal NP 1 Aluminum Gray', 'BASF'],
    'MasterSeal-NP-1-Black.pdf' => ['MasterSeal NP 1 Black', 'BASF'],
    'MasterSeal-NP-1-Stone.pdf' => ['MasterSeal NP 1 Stone', 'BASF'],
    'MasterSeal-NP-1-White.pdf' => ['MasterSeal NP 1 White (also NP1 White)', 'BASF'],
    'MasterSeal-SL-1-Limestone.pdf' => ['MasterSeal SL 1 Limestone', 'BASF Canada'],
    'Novalink-35-Novalink-TX.pdf' => ['Novalink 35, Novalink TX', 'Chem Link'],
    'Vulkem-116-LV-Stone.pdf' => ['Vulkem 116 LV Stone', 'Tremco'],
    'Brick.pdf' => ['Brick', 'Glen-Gery'],
    'LM-Crystex.pdf' => ['L&M Crystex', 'LATICRETE'],
    'LM-Duragrout.pdf' => ['L&M Duragrout', 'LATICRETE'],
    'Por-Rok-Anchoring-Cement.pdf' => ['Por-Rok Anchoring Cement', 'CGM Building Products'],
    'Portland-Cement-Based-Concrete-Products.pdf' => ['Portland Cement Based Concrete Products', 'QUIKRETE'],
    'Portland-Lime-Mortars.pdf' => ['Portland Lime Mortars', 'SPEC MIX'],
    'Repair-And-Construction-Related-Products.pdf' => ['Repair & Construction Related Products (incl. Finish Stucco)', 'Precision Packaging/Materials Packaging Corp.'],
    'Silica-Sand-All-Grades.pdf' => ['Silica Sand, All Grades', 'Manley Bros. of Indiana'],
    '1100-Concrete-Curing-Compound.pdf' => ['1100 Concrete Curing Compound', 'W. R. Meadows'],
    '1600-White-Series.pdf' => ['1600-WHITE Series', 'W. R. Meadows'],
    'Bright-Kure-And-Seal-VOC.pdf' => ['Bright Kure & Seal VOC', 'Sierra Corp/TK Products'],
    'Cure-And-Seal-WB-25.pdf' => ['Cure & Seal WB 25', 'SpecChem'],
    'Kure-And-Seal-Water-Borne.pdf' => ['Kure and Seal Water Borne', 'Sierra Corp/TK Products'],
    'MasterKure-CC-180WB.pdf' => ['MasterKure CC 180WB', 'Master Builders Solutions'],
    'MasterKure-CC-250SB.pdf' => ['MasterKure CC 250SB', 'Master Builders Solutions'],
    'MasterKure-HD-200WB-Also-Kure-N-Harden.pdf' => ['MasterKure HD 200WB (also Kure-N-Harden)', 'BASF'],
    'MasterKure-HD-300WB-Also-Lapidolith.pdf' => ['MasterKure HD 300WB (also Lapidolith)', 'BASF'],
    'MasterSeal-615-Also-Hydrocide-700B.pdf' => ['MasterSeal 615 (also Hydrocide 700B)', 'BASF'],
    'US-600M.pdf' => ['US-600M', 'BASF'],
    '110-Micron-Moly-Racing-Oil-SAE-30-40-And-50.pdf' => ['110 Micron Moly Racing Oil SAE 30, 40 & 50', 'Schaeffer'],
    '274-Moly-EP-Synthetic-Plus-Grease-NLGI-00-And-0.pdf' => ['274 Moly EP Synthetic Plus Grease NLGI 00 & 0', 'Schaeffer'],
    '274-Moly-EP-Synthetic-Plus-Grease-NLGI-1.pdf' => ['274 Moly EP Synthetic Plus Grease NLGI 1', 'Schaeffer'],
    '274-Moly-EP-Synthetic-Plus-Grease-NLGI-2.pdf' => ['274 Moly EP Synthetic Plus Grease NLGI 2', 'Schaeffer'],
    'Acetylene.pdf' => ['Acetylene', 'Airgas'],
    'Chevron-Hydraulic-Oil-AW-32-46-68.pdf' => ['Chevron Hydraulic Oil AW 32, 46, 68', 'Chevron Canada'],
    'Cutting-Oil.pdf' => ['Cutting Oil', 'CRC Canada'],
    'Diesel-Fuel-All-Types.pdf' => ['Diesel Fuel, All Types', 'Hess'],
    'gasoline-all-grades.pdf' => ['Gasoline All Grades', 'Hess'],
    'Gunk-Carburetor-Parts-Cleaner.pdf' => ['Gunk Carburetor Parts Cleaner', 'RSC Chemical Solutions'],
    'Kerosene-K1-And-K2.pdf' => ['Kerosene K1 and K2', 'Hess'],
    'LM-EZ-Strip.pdf' => ['L&M EZ Strip', 'LATICRETE'],
    'Magic-Kote.pdf' => ['Magic Kote', 'Dayton Superior'],
    'OReilly-Synthetic-DOT-3-Brake-Fluid.pdf' => ['O\'Reilly Synthetic DOT 3 Brake Fluid', 'Omni Specialty Packaging'],
    'Oxygen.pdf' => ['Oxygen', 'Airgas'],
    'PB-Penetrating-Catalyst.pdf' => ['PB Penetrating Catalyst', 'Blaster'],
    'Propane.pdf' => ['Propane', 'Airgas'],
    'Stihl-HP-High-Performance-2-Cycle-Engine-Oil.pdf' => ['STIHL HP (High Performance) 2-Cycle Engine Oil', 'Omni Specialty Packaging for Stihl'],
    'Bituthene-Mastic.pdf' => ['Bituthene Mastic', 'GCP Applied Technologies'],
    'HIT-HY-200-A-V3.pdf' => ['HIT-HY 200-A V3', 'Hilti'],
    'Mel-Prime.pdf' => ['Mel-Prime', 'W. R. Meadows'],
    'Stego-Tape.pdf' => ['Stego Tape', 'Stego Industries'],
    'Deck-O-Foam.pdf' => ['Deck-O-Foam', 'W. R. Meadows'],
    'Manufactured-Products.pdf' => ['Manufactured Products (incl. Backer Rod, Expansion Joint Strips)', 'QUIKRETE'],
    'Right-Joint-Asphalt-Expansion-Joint.pdf' => ['Right-Joint Asphalt Expansion Joint', 'Right Pointe'],
    'CCW-MiraDRI-860-861.pdf' => ['CCW MiraDRI 860/861', 'Carlisle Coatings and Waterproofing'],
    'MasterSeal-610.pdf' => ['MasterSeal 610', 'Master Builders Solutions'],
    'Stego-Wrap.pdf' => ['Stego Wrap', 'Stego Industries'],
    'Waterstop-RX-101.pdf' => ['Waterstop-RX 101', 'CETCO'],
    'Duogard-Citrus.pdf' => ['Duogard Citrus', 'W. R. Meadows'],
    'Vulkem-171-Primer.pdf' => ['Vulkem 171 Primer', 'Tremco'],
    'LM-E-CON.pdf' => ['L&M E-CON', 'LATICRETE'],
    'MasterEmaco-A-660C.pdf' => ['MasterEmaco A 660C', 'Master Builders Solutions Canada'],
    'WRDA-35.pdf' => ['WRDA 35', 'GCP Applied Technologies'],
    'WRDA-79.pdf' => ['WRDA 79', 'GCP Applied Technologies'],
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
