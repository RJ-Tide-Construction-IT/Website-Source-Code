<?php
// Renders a submitted job application as a printable PDF laid out like a paper
// application form (boxed fields, checkboxes, signature block). apply-handler.php
// attaches it to the HR email so every application can be printed and filed.
//
// To change the layout, edit the HTML/CSS inside render_application_pdf() below,
// it's regular HTML that the dompdf library (includes/vendor/) turns into a PDF.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// A single text answer from the form. Anything that isn't a plain string
// (e.g. an array POSTed by a bot) is treated as blank.
function app_pdf_text(array $data, string $key): string {
    return is_string($data[$key] ?? null) ? trim($data[$key]) : '';
}

function app_pdf_e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// One labeled box on the form, e.g. app_pdf_box('Phone', $phone, 30).
function app_pdf_box(string $label, string $value, int $widthPercent, int $colspan = 1): string {
    return '<td style="width:' . $widthPercent . '%" colspan="' . $colspan . '">'
         . '<span class="lbl">' . app_pdf_e($label) . '</span>'
         . '<span class="val">' . nl2br(app_pdf_e($value)) . '&nbsp;</span></td>';
}

// A printed checkbox, filled with an X when checked.
function app_pdf_check(bool $checked, string $label = ''): string {
    return '<span class="opt"><span class="cb">' . ($checked ? 'X' : '&nbsp;') . '</span>' . app_pdf_e($label) . '</span>';
}

// Yes / No checkbox pair for a radio question.
function app_pdf_yes_no(string $answer): string {
    return app_pdf_check($answer === 'Yes', 'Yes') . app_pdf_check($answer === 'No', 'No');
}

/**
 * @param array  $data        The submitted form fields ($_POST).
 * @param string $resumeNote  One line describing the resume, e.g. where it was saved.
 * @return string The PDF file contents.
 */
function render_application_pdf(array $data, string $resumeNote): string {
    $t = fn(string $key) => app_pdf_text($data, $key);

    $startDate = $t('start_date');
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $startDate = date('m/d/Y', strtotime($startDate));
    }

    $licenses = array_filter((array) ($data['licenses'] ?? []), 'is_string');
    $physical = array_filter((array) ($data['physical'] ?? []), 'is_string');
    $employers = is_array($data['employer'] ?? null) ? $data['employer'] : [];
    $proficiencyLevels = ['Fair', 'Good', 'Excellent'];
    $experienceLevels  = ['None', 'Average', 'Above'];
    $logoPath = __DIR__ . '/pdf-logo.jpg';
    // The office is in Iowa, so stamp Central time regardless of the server's timezone.
    $submitted = new DateTime('now', new DateTimeZone('America/Chicago'));

    ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    @page { margin: 0.4in 0.5in 0.55in; }
    body { font-family: "DejaVu Sans", sans-serif; font-size: 8.5pt; color: #111; }
    .header { width: 100%; border-collapse: collapse; margin-bottom: 2pt; }
    .header td { vertical-align: middle; }
    .header h1 { font-size: 15pt; margin: 0; color: #0F172A; text-align: right; }
    .header .sub { text-align: right; font-size: 8pt; color: #3A3A3A; }
    h2 { font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.4pt; color: #0F172A;
         background: #E0E0E0; border-left: 4pt solid #DD183B; padding: 2.5pt 6pt; margin: 6pt 0 0;
         page-break-after: avoid; }
    table.grid { width: 100%; border-collapse: collapse; }
    table.grid td, table.grid th { border: 0.75pt solid #555; padding: 1.5pt 4pt 2pt; vertical-align: top; }
    table.grid th { font-size: 6.5pt; text-transform: uppercase; color: #444; font-weight: normal;
                    text-align: left; background: #F5F5F5; }
    .lbl { display: block; font-size: 6.5pt; text-transform: uppercase; color: #444; }
    .val { display: block; font-size: 9pt; }
    .opt { white-space: nowrap; margin-right: 10pt; }
    .cb { display: inline-block; width: 8pt; height: 8pt; border: 0.75pt solid #222; text-align: center;
          font-size: 7pt; line-height: 8pt; font-weight: bold; margin-right: 3pt; }
    .keep { page-break-inside: avoid; }
    /* Checkbox lists: an outer box only, no lines between the options. */
    table.options td { border-top: none; border-bottom: none; border-left: none; border-right: none; padding: 3pt 4pt; }
    table.options { border: 0.75pt solid #555; }
    .cert { font-size: 8pt; margin: 4pt 0; }
</style>
</head>
<body>

<table class="header">
    <tr>
        <td style="width:40%"><img src="<?= app_pdf_e($logoPath) ?>" style="height:48pt"></td>
        <td>
            <h1>Application for Employment</h1>
            <div class="sub"><?= app_pdf_e(SITE_NAME) ?></div>
            <div class="sub"><?= app_pdf_e(SITE_ADDRESS) ?> &middot; <?= app_pdf_e(SITE_PHONE) ?></div>
            <div class="sub">Submitted online <?= $submitted->format('m/d/Y \a\t g:i A T') ?></div>
        </td>
    </tr>
</table>

<h2>Personal Information</h2>
<table class="grid">
    <tr><?= app_pdf_box('Full Name', $t('name'), 40) ?><?= app_pdf_box('Phone', $t('phone'), 25) ?><?= app_pdf_box('Email', $t('email'), 35) ?></tr>
</table>
<table class="grid">
    <tr><?= app_pdf_box('Present Address', $t('address'), 45) ?><?= app_pdf_box('City', $t('city'), 25) ?><?= app_pdf_box('State', $t('state'), 12) ?><?= app_pdf_box('Zip', $t('zip'), 18) ?></tr>
</table>
<table class="grid">
    <tr>
        <?= app_pdf_box('Emergency Contact Name, Relationship & Phone', $t('emergency_contact'), 70) ?>
        <td style="width:30%"><span class="lbl">18 years of age or older?</span><?= app_pdf_yes_no($t('age_18')) ?></td>
    </tr>
</table>

<h2>Employment Desired</h2>
<table class="grid">
    <tr><?= app_pdf_box('Position Applying For', $t('position'), 45) ?><?= app_pdf_box('Date You Can Start', $startDate, 25) ?><?= app_pdf_box('Salary Desired', $t('salary_desired'), 30) ?></tr>
</table>
<table class="grid">
    <tr>
        <td style="width:33%"><span class="lbl">Employed now?</span><?= app_pdf_yes_no($t('employed_now')) ?></td>
        <td style="width:34%"><span class="lbl">Applied/worked for RJ Tide before?</span><?= app_pdf_yes_no($t('applied_before')) ?></td>
        <?= app_pdf_box('If yes, when?', $t('applied_before_when'), 33) ?>
    </tr>
    <tr>
        <td><span class="lbl">Can work weekends?</span><?= app_pdf_yes_no($t('weekends')) ?></td>
        <td><span class="lbl">Available for overtime?</span><?= app_pdf_yes_no($t('overtime')) ?></td>
        <td>
            <span class="lbl">Willing to travel? / Stay overnight?</span>
            Travel: <?= app_pdf_yes_no($t('travel')) ?><br>
            Overnight: <?= app_pdf_yes_no($t('overnight')) ?>
        </td>
    </tr>
</table>

<h2>General</h2>
<table class="grid">
    <tr><?= app_pdf_box('Special Training', $t('special_training'), 50) ?><?= app_pdf_box('Special Skills', $t('special_skills'), 50) ?></tr>
</table>
<table class="grid">
    <tr><th style="width:28%">Language</th><th style="width:24%">Speak</th><th style="width:24%">Read</th><th style="width:24%">Write</th></tr>
    <?php foreach (['primary_language' => 'Primary', 'other_language' => 'Other'] as $prefix => $kind): ?>
    <tr>
        <td><span class="lbl"><?= $kind ?></span><span class="val"><?= app_pdf_e($t($prefix)) ?>&nbsp;</span></td>
        <?php foreach (['speak', 'read', 'write'] as $skill): ?>
        <td><?php foreach ($proficiencyLevels as $level) { echo app_pdf_check($t($prefix . '_' . $skill) === $level, $level); } ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>

<div class="keep">
<h2>Education</h2>
<table class="grid">
    <tr><th style="width:20%">School</th><th style="width:45%">City / State</th><th style="width:35%">Graduate / Degree</th></tr>
    <?php foreach (['high_school' => 'High School', 'college' => 'College', 'other_education' => 'Other'] as $key => $label): ?>
    <tr><td><?= $label ?></td><td><?= app_pdf_e($t($key . '_city_state')) ?>&nbsp;</td><td><?= app_pdf_e($t($key . '_degree')) ?>&nbsp;</td></tr>
    <?php endforeach; ?>
</table>
</div>

<div class="keep">
<h2>Experience</h2>
<table class="grid">
    <tr><th style="width:40%">Skill</th><?php foreach ($experienceLevels as $level): ?><th style="width:20%"><?= $level ?></th><?php endforeach; ?></tr>
    <?php foreach ($GLOBALS['APPLICATION_EXPERIENCE_SKILLS'] as $key => $label): ?>
    <tr>
        <td><?= app_pdf_e($label) ?></td>
        <?php foreach ($experienceLevels as $level): ?>
        <td><?= app_pdf_check($t('experience_' . $key) === $level) ?></td>
        <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
</table>
</div>

<div class="keep">
<h2>Valid Licenses / Certifications</h2>
<table class="grid options">
    <?php foreach (array_chunk($GLOBALS['APPLICATION_LICENSES'], 4) as $row): ?>
    <tr><?php for ($c = 0; $c < 4; $c++): ?><td style="width:25%"><?= isset($row[$c]) ? app_pdf_check(in_array($row[$c], $licenses, true), $row[$c]) : '' ?></td><?php endfor; ?></tr>
    <?php endforeach; ?>
</table>
</div>

<div class="keep">
<h2>Physical Requirements <span style="text-transform:none;font-weight:normal;">(applicant checked each one they are able to do)</span></h2>
<table class="grid options">
    <?php foreach (array_chunk($GLOBALS['APPLICATION_PHYSICAL_REQUIREMENTS'], 2) as $row): ?>
    <tr><?php for ($c = 0; $c < 2; $c++): ?><td style="width:50%"><?= isset($row[$c]) ? app_pdf_check(in_array($row[$c], $physical, true), $row[$c]) : '' ?></td><?php endfor; ?></tr>
    <?php endforeach; ?>
</table>
</div>

<h2>Employment History <span style="text-transform:none;font-weight:normal;">(most recent first)</span></h2>
<?php for ($i = 1; $i <= 3; $i++):
    $job = is_array($employers[$i] ?? null) ? $employers[$i] : [];
    $j = fn(string $key) => app_pdf_text($job, $key); ?>
<table class="grid keep" style="margin-top:<?= $i === 1 ? 0 : 5 ?>pt">
    <tr><?= app_pdf_box("Employer #$i, Name & Address of Company", $j('company'), 60, 2) ?><?= app_pdf_box('Employed From', $j('from'), 20) ?><?= app_pdf_box('Employed To', $j('to'), 20) ?></tr>
    <tr><?= app_pdf_box('Title & Duties', $j('title'), 60, 2) ?><?= app_pdf_box('Starting Wage', $j('starting_wage'), 20) ?><?= app_pdf_box('Final Wage', $j('final_wage'), 20) ?></tr>
    <tr><?= app_pdf_box('Reason for Leaving', $j('reason'), 50) ?><?= app_pdf_box('Supervisor Name & Contact Phone', $j('supervisor'), 50, 3) ?></tr>
</table>
<?php endfor; ?>

<div class="keep">
<h2>Resume &amp; Additional Information</h2>
<table class="grid">
    <tr><?= app_pdf_box('Resume', $resumeNote, 100) ?></tr>
    <tr><?= app_pdf_box('Anything else the applicant would like us to know', $t('message'), 100) ?></tr>
</table>
</div>

<div class="keep">
<h2>Applicant Certification</h2>
<p class="cert"><?= app_pdf_check(!empty($data['certify'])) ?>
    I certify that the information provided in this application is complete and true to the best of my
    knowledge, and I authorize RJ Tide Construction to verify it with the references, schools, and
    employers listed above.</p>
<table class="grid">
    <tr><?= app_pdf_box('Applicant (signed electronically)', $t('name'), 65) ?><?= app_pdf_box('Date', $submitted->format('m/d/Y'), 35) ?></tr>
</table>

<h2>For Office Use Only</h2>
<table class="grid">
    <tr><?= app_pdf_box('Reviewed By', '', 35) ?><?= app_pdf_box('Date', '', 20) ?><?= app_pdf_box('Interview Date', '', 20) ?><td style="width:25%"><span class="lbl">Hired?</span><?= app_pdf_yes_no('') ?></td></tr>
    <tr><?= app_pdf_box('Notes', "\n", 100, 4) ?></tr>
</table>
</div>

</body>
</html>
<?php
    $html = ob_get_clean();

    $options = new Options();
    $options->set('isRemoteEnabled', false);   // never fetch URLs, only the local logo
    $options->set('isPhpEnabled', false);
    $options->setChroot([__DIR__]);            // may only read files inside includes/
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();

    // "Page X of Y" in the bottom margin of every page.
    $canvas = $dompdf->getCanvas();
    $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
    $canvas->page_text(
        $canvas->get_width() - 110, $canvas->get_height() - 28,
        'Page {PAGE_NUM} of {PAGE_COUNT}', $font, 7, [0.35, 0.35, 0.35]
    );
    $canvas->page_text(
        36, $canvas->get_height() - 28,
        'Application: ' . app_pdf_text($data, 'name') . ', ' . app_pdf_text($data, 'position'), $font, 7, [0.35, 0.35, 0.35]
    );

    return $dompdf->output();
}
