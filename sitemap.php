<?php
// The list of pages search engines should index (robots.txt points here).
// Built on each visit, so open job postings, including ones added on the
// Employee Dashboard, show up as soon as they're switched on.
// When you add a new public page, add it to $pages below.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$pages = [
    '/',
    '/about.php',
    '/services/concrete-services.php',
    '/services/agricultural-services.php',
    '/services/ag-industrial-maintenance.php',
    '/projects.php',
    '/projects/concrete.php',
    '/projects/agricultural.php',
    '/projects/special-projects.php',
    '/careers.php',
    '/employment.php',
    '/contact.php',
    '/privacy.php',
];
foreach (jobs_with_status() as $job) {
    if ($job['open']) {
        $pages[] = $job['href'];
    }
}

header('Content-Type: application/xml; charset=utf-8');
echo '<?xml version="1.0" encoding="UTF-8"?>', "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', "\n";
foreach ($pages as $path) {
    echo '  <url><loc>', htmlspecialchars(SITE_URL . $path, ENT_XML1), '</loc></url>', "\n";
}
echo '</urlset>', "\n";
