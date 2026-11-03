<?php
// Builds a PDF from HTML with the dompdf library (includes/vendor/). Shared by
// the job application PDF (application-pdf.php) and the dashboard's safety form
// PDFs (dashboard/safety-forms.php), so both are locked down the same way:
// it never fetches web addresses, never runs PHP, and can only read files
// inside includes/ (like the logo).

function render_pdf(string $html): Dompdf\Dompdf {
    // A missing file is a fatal error require can't recover from, so check first
    // (e.g. if includes/vendor/ didn't make it onto the server).
    if (!is_file(__DIR__ . '/vendor/autoload.php')) {
        throw new RuntimeException('includes/vendor/ is missing, the PDF library is not installed');
    }
    require_once __DIR__ . '/vendor/autoload.php';

    $options = new Dompdf\Options();
    $options->set('isRemoteEnabled', false);
    $options->set('isPhpEnabled', false);
    $options->setChroot([__DIR__]);
    $options->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf\Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('letter', 'portrait');
    $dompdf->render();
    return $dompdf;
}
