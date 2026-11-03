<?php
// Shows any job posting (careers/posting.php?id=...), the originals and ones
// added later, all edited on the Employee Dashboard's Job Postings page.
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

$id = is_string($_GET['id'] ?? null) ? $_GET['id'] : '';
$posting = job_postings()[$id] ?? null;

if (!$posting) {
    http_response_code(404);
    $pageTitle = 'Position Not Found';
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
    page_hero('Position Not Found');
    ?>
    <section>
        <div class="container container--narrow text-center">
            <p>This job posting isn&rsquo;t available anymore.</p>
            <a href="<?= BASE_URL ?>/careers.php" class="btn">See current openings</a>
        </div>
    </section>
    <?php
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
    exit;
}

$pageTitle = $posting['title'];
$canonicalPath = '/careers/posting.php?id=' . rawurlencode($id);
$jobMeta = posting_job_meta($posting);
require $_SERVER['DOCUMENT_ROOT'] . '/includes/job-posting-header.php';

echo render_posting_body($posting['body']);
if (!empty($posting['standard_sections'])) {
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/job-posting-standard-sections.php';
}

require $_SERVER['DOCUMENT_ROOT'] . '/includes/job-posting-footer.php';
