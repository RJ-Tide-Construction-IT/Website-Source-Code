<?php
$pageTitle = 'Agricultural Projects';
$pageDescription = 'Photos of RJ Tide Construction agricultural work — grain storage and handling, industrial maintenance, and millwright services.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Agricultural',
        'note'   => 'Photos for this section are coming soon.',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => 'Crew truck and trailer on-site at a grain storage project', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => 'Grain storage silos with crew working the overhead leg structure', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => '3D design concept of a grain handling and storage facility', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => 'Engineering elevation drawing of a grain leg and distributor structure', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => '2D engineering drawing of the grain storage facility', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => '3D design render of a grain storage silo and conveyor structure', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => '3D design render of the grain storage facility, alternate angle', 'location' => ''],
            ['img' => 'agriculture/web/placeholder.svg', 'caption' => 'Completed grain storage facility matching the original design', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Industrial Maintenance',
        'note'   => '',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'industrial-maintenance/web/20230516_145434.jpg', 'caption' => 'Grain leg headhouse and catwalk structure', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20230516_150046.jpg', 'caption' => 'Grain leg boot and distributor mechanism', 'location' => ''],
            ['img' => 'industrial-maintenance/web/img-2071.jpg', 'caption' => 'Crane setting a grain bin section into place', 'location' => ''],
            ['img' => 'industrial-maintenance/web/lptl9279.jpg', 'caption' => 'Tandem crane lift setting a grain bin section into place', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20240803_114332.jpg', 'caption' => 'Completed grain elevator and storage facility with silos', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20240803_074735.jpg', 'caption' => 'Crane and boom lift erecting a grain storage facility', 'location' => ''],
            ['img' => 'industrial-maintenance/web/20260423_121052.jpg', 'caption' => 'Crew erecting a new steel structure at a grain facility', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Millwright',
        'note'   => 'Photos for this section are coming soon.',
        'layout' => 'carousel',
        'photos' => [
            ['img' => 'millwright/web/placeholder-1.jpg', 'caption' => 'Installation & Alignment', 'location' => ''],
            ['img' => 'millwright/web/placeholder-2.jpg', 'caption' => 'Maintenance & Repair', 'location' => ''],
            ['img' => 'millwright/web/placeholder-3.jpg', 'caption' => 'Custom Solutions', 'location' => ''],
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
