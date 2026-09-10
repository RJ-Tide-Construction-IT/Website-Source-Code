<?php
$pageTitle = 'Specialty Concrete';
$pageDescription = 'Photos of RJ Tide Construction decorative and specialty concrete work.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Specialty Concrete',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'special-projects/web/chapel-facade.jpg', 'caption' => 'Stone-veneer Chapel Facade with Decorative Concrete Detailing', 'location' => 'Trinity Heights'],
        ],
    ],
    [
        'label'  => 'Decorative Concrete',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'special-projects/web/relief-panel.jpg', 'caption' => 'Custom Statue of Saint Joseph', 'location' => 'Trinity Heights'],
        ],
    ],
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/special-projects/web/chapel-facade.jpg');">
    <div class="container"><h1>Specialty Concrete</h1></div>
</section>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
