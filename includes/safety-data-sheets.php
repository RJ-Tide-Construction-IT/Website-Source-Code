<?php
// Shared by every Safety Data Sheet page: the main page (safety-data-sheets.php),
// the group pages (safety-data-sheets/*.php) and sitemap.php. Nothing here shows
// on a page by itself.

// The groups, in folder-number order. Each line is: the short name, which is also
// the name of its page in safety-data-sheets/, then the PDF folder inside
// assets/img/safety-data-sheets/, then the name shown on the site. A folder with
// no line here doesn't appear on the site.
const SDS_CATEGORIES = [
    'right-to-know' => ['folder' => '1-RIGHT-TO-KNOW', 'label' => 'Right to Know'],
    'acids' => ['folder' => '2-ACIDS', 'label' => 'Acids'],
    'caulking-sealant' => ['folder' => '3-CAULKING-SEALANT', 'label' => 'Caulking & Sealant'],
    'cementitious-materials' => ['folder' => '4-CEMETOUS-MATERIALS', 'label' => 'Cementitious Materials'],
    'cures-sealers' => ['folder' => '5-CURES-SEALERS', 'label' => 'Cures & Sealers'],
    'fuels-lubricants' => ['folder' => '6-FUELS-GAS-LUBRICANTS-OIL', 'label' => 'Fuels, Gas, Lubricants & Oil'],
    'adhesives' => ['folder' => '7-ADHESIVE-EPOXY-GLUE', 'label' => 'Adhesive, Epoxy & Glue'],
    'expansion-joints' => ['folder' => '8-BACKER-ROD-EXPANSION-JOINTS-INSULATION', 'label' => 'Backer Rod, Expansion Joints & Insulation'],
    'plastic-membranes' => ['folder' => '9-PLASTIC-MEMBRANES', 'label' => 'Plastic Membranes'],
    'paints-solvents' => ['folder' => '10-PAINTS-PRIMERS-SOLVENTS', 'label' => 'Paints, Primers & Solvents'],
    'concrete-mixers' => ['folder' => '11-CONCRETE-AND-MIXERS', 'label' => 'Concrete & Mixers'],
    'power-actuated-tools' => ['folder' => '12-POWER-ACTUATED-TOOLS', 'label' => 'Power Actuated Tools'],
    'miscellaneous' => ['folder' => '13-MISC', 'label' => 'Miscellaneous'],
];

// Optional nicer titles, by PDF filename: product, then maker. A PDF not listed
// here is shown under its filename, with dashes and underscores as spaces.
const SDS_TITLES = [
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
    'Diamond-Wheels.pdf' => ['Diamond Wheels', 'United Abrasives'],
];

// The PDF filenames in one group's folder. A folder that doesn't exist counts as
// empty, because git drops a folder once nothing is left in it.
function sds_pdf_files(string $slug): array {
    $folder = dirname(__DIR__) . '/assets/img/safety-data-sheets/' . SDS_CATEGORIES[$slug]['folder'];
    return is_dir($folder) ? preg_grep('/\.pdf$/i', scandir($folder)) : [];
}

// How many PDFs a group has. Groups with none stay off the main page and the sitemap.
function sds_sheet_count(string $slug): int {
    return count(sds_pdf_files($slug));
}

// One group's sheets, sorted by the name shown, each with its filename, name and
// address.
function sds_sheets(string $slug): array {
    $labels = [];
    foreach (sds_pdf_files($slug) as $file) {
        $labels[$file] = implode(' · ', SDS_TITLES[$file] ?? [strtr(pathinfo($file, PATHINFO_FILENAME), '-_', '  ')]);
    }
    natcasesort($labels);

    $folderUrl = BASE_URL . '/assets/img/safety-data-sheets/' . rawurlencode(SDS_CATEGORIES[$slug]['folder']) . '/';
    $sheets = [];
    foreach ($labels as $file => $label) {
        $sheets[] = ['file' => $file, 'label' => $label, 'url' => $folderUrl . rawurlencode($file)];
    }
    return $sheets;
}

// The emergency and "need a sheet?" note shown at the bottom of every Safety Data
// Sheet page.
function sds_contact_note(): void { ?>
        <p class="spaced-top">In a medical emergency, call 911. Each sheet lists an emergency
           phone number for its product.</p>
        <p>Need a sheet that isn&rsquo;t listed? Call <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>
           or email <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</p>
<?php }
