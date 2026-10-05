<?php
$pageTitle = 'Specialty Concrete';
$pageDescription = 'Photos of RJ Tide Construction decorative and specialty concrete work.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' (optional) isn't shown on the page yet, it's recorded here so
// it's ready if photo captions are ever added to the gallery.
$sections = [
    [
        'label'  => 'Specialty Concrete',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'special-projects/web/chapel-facade.jpg', 'caption' => 'Stone-veneer Chapel Facade with Decorative Concrete Detailing', 'location' => 'Trinity Heights'],
            ['img' => 'special-projects/web/chapel-polished-floor.jpg', 'caption' => 'Polished Decorative Concrete Floor in a Stone Chapel'],
        ],
    ],
    [
        'label'  => 'Decorative Concrete',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'special-projects/web/relief-panel.jpg', 'caption' => 'Custom Statue of Saint Joseph', 'location' => 'Trinity Heights'],
            ['img' => 'special-projects/web/chapel-engraved-floor.jpg', 'caption' => 'Engraved Decorative Concrete Floor Around a Baptismal Font'],
        ],
    ],
];
?>

<?php page_hero('Specialty Concrete', 'special-projects/web/chapel-facade.jpg'); ?>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
