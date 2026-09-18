<?php
$pageTitle = 'Agricultural Projects';
$pageDescription = 'Photos of RJ Tide Construction agricultural work, grain storage and handling, industrial maintenance, and millwright services.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Agricultural / Industrial',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'agriculture/web/scale-house-aerial.jpg', 'caption' => 'Aerial view of a completed grain facility scale house and truck scale', 'location' => ''],
            ['img' => 'agriculture/web/craig-facility.jpg', 'caption' => 'Completed grain elevator and receiving facility', 'location' => ''],
            ['img' => 'agriculture/web/headhouse-dusk.jpg', 'caption' => 'Grain leg headhouse and catwalks at dusk', 'location' => ''],
            ['img' => 'agriculture/web/distributor-golden-hour.jpg', 'caption' => 'Distributor and spouting atop grain storage bins', 'location' => ''],
            ['img' => 'agriculture/web/bin-fill-deck.jpg', 'caption' => 'Bin fill deck and distributor equipment atop a grain facility', 'location' => ''],
            ['img' => 'agriculture/web/crew-sukup-leg.jpg', 'caption' => 'Millwright servicing a Sukup grain leg', 'location' => ''],
            ['img' => 'agriculture/web/bagging-line-install.jpg', 'caption' => 'Robotic bagging and palletizing line installation', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20230516_145434.jpg', 'caption' => 'Grain leg headhouse and catwalk structure', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20230516_150046.jpg', 'caption' => 'Grain leg boot and distributor mechanism', 'location' => ''],
            ['img' => 'industrial-maintenance/web/img-2071.jpg', 'caption' => 'Crane setting a grain bin section into place', 'location' => ''],
            ['img' => 'industrial-maintenance/web/lptl9279.jpg', 'caption' => 'Tandem crane lift setting a grain bin section into place', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20240803_114332.jpg', 'caption' => 'Completed grain elevator and storage facility with silos', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20240803_074735.jpg', 'caption' => 'Crane and boom lift erecting a grain storage facility', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20260423_121052.jpg', 'caption' => 'Crew erecting a new steel structure at a grain facility', 'location' => ''],
        ],
    ],
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/agriculture/web/design-build-reality.jpg');">
    <div class="container"><h1>Agricultural</h1></div>
</section>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>

        <?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/photo-sections.php'; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
