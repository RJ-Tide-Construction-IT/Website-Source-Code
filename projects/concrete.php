<?php
$pageTitle = 'Concrete Projects';
$pageDescription = 'Photos of RJ Tide Construction concrete work, flatwork, foundations, site concrete, structural, and cast-in-place concrete.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Section labels match the sub-service categories on
// services/concrete-services.php so the two pages stay in sync.
// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Flatwork',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/flatwork.jpg', 'caption' => 'Finishing an interior concrete flatwork pour', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Foundations',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/foundation.jpg', 'caption' => 'Forming a concrete foundation on-site', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Site Concrete',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/site-concrete.jpg', 'caption' => 'Pouring site concrete paving', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Structural',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/industrial.jpg', 'caption' => 'Finishing an industrial concrete slab', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Cast In Place',
        'note'   => 'Photos for this section are coming soon.',
        'layout' => 'carousel',
        'photos' => [],
    ],
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/concrete/web/concrete-hero.jpg');">
    <div class="container"><h1>Concrete</h1></div>
</section>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
