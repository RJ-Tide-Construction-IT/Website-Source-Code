<?php
$pageTitle = 'Agricultural Projects';
$pageDescription = 'Photos of RJ Tide Construction agricultural work, grain storage and handling, industrial maintenance, and millwright services.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$sections = [
    [
        'label'  => 'Agricultural / Industrial',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'agriculture/web/scale-house-aerial.webp', 'caption' => 'Aerial view of a completed grain facility scale house and truck scale'],
            ['img' => 'agriculture/web/craig-facility.webp', 'caption' => 'Completed grain elevator and receiving facility'],
            ['img' => 'agriculture/web/headhouse-dusk.webp', 'caption' => 'Grain leg headhouse and catwalks at dusk'],
            ['img' => 'agriculture/web/distributor-golden-hour.webp', 'caption' => 'Distributor and spouting atop grain storage bins'],
            ['img' => 'agriculture/web/bin-fill-deck.webp', 'caption' => 'Bin fill deck and distributor equipment atop a grain facility'],
            ['img' => 'agriculture/web/crew-sukup-leg.webp', 'caption' => 'Millwright servicing a Sukup grain leg'],
            ['img' => 'agriculture/web/bagging-line-install.webp', 'caption' => 'Robotic bagging and palletizing line installation'],
            ['img' => 'industrial-maintenance/web/20230516_145434.webp', 'caption' => 'Grain leg headhouse and catwalk structure'],
            ['img' => 'industrial-maintenance/web/20230516_150046.webp', 'caption' => 'Grain leg boot and distributor mechanism'],
            ['img' => 'industrial-maintenance/web/img-2071.webp', 'caption' => 'Crane setting a grain bin section into place'],
            ['img' => 'industrial-maintenance/web/lptl9279.webp', 'caption' => 'Tandem crane lift setting a grain bin section into place'],
            ['img' => 'industrial-maintenance/web/20240803_114332.webp', 'caption' => 'Completed grain elevator and storage facility with silos'],
            ['img' => 'industrial-maintenance/web/20240803_074735.webp', 'caption' => 'Crane and boom lift erecting a grain storage facility'],
            ['img' => 'industrial-maintenance/web/20260423_121052.webp', 'caption' => 'Crew erecting a new steel structure at a grain facility'],
        ],
    ],
];
?>

<?php page_hero('Agricultural', 'agriculture/web/design-build-reality.webp'); ?>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
