<?php
$pageTitle = 'Concrete Projects';
$pageDescription = 'Photos of RJ Tide Construction concrete work, flatwork, foundations, site concrete, structural, and cast-in-place concrete.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Section labels match the sub-service categories on
// services/concrete-services.php so the two pages stay in sync.
$sections = [
    [
        'label'  => 'Flatwork',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/flatwork.jpg', 'caption' => 'Finishing an interior concrete flatwork pour'],
            ['img' => 'concrete/web/interior-slab-pour.jpg', 'caption' => 'Finishing an interior slab pour beside reinforced sections'],
        ],
    ],
    [
        'label'  => 'Foundations',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/foundation.jpg', 'caption' => 'Forming a concrete foundation on-site'],
        ],
    ],
    [
        'label'  => 'Site Concrete',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/site-concrete.jpg', 'caption' => 'Pouring site concrete paving'],
            ['img' => 'concrete/web/exterior-slab-placement.jpg', 'caption' => 'Crew placing a large exterior slab at a commercial building'],
            ['img' => 'concrete/web/paving-at-dusk.jpg', 'caption' => 'Fresh concrete paving placed beside a finished slab'],
        ],
    ],
    [
        'label'  => 'Structural',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'concrete/web/industrial.jpg', 'caption' => 'Finishing an industrial concrete slab'],
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

<?php page_hero('Concrete', 'concrete/web/concrete-hero.jpg'); ?>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
