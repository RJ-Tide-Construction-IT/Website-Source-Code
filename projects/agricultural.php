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
        'note'   => 'Photos for this section are coming soon.',
        'photos' => [
            ['img' => 'industrial-maintenance/web/placeholder-1.jpg', 'caption' => 'Preventative Care', 'location' => ''],
            ['img' => 'industrial-maintenance/web/placeholder-2.jpg', 'caption' => 'Emergency Repairs', 'location' => ''],
            ['img' => 'industrial-maintenance/web/placeholder-3.jpg', 'caption' => 'Scheduled Maintenance', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Millwright',
        'note'   => 'Photos for this section are coming soon.',
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

        <?php foreach ($sections as $i => $section): ?>
        <h2 class="section-title" style="<?= $i === 0 ? 'margin-top:2rem;' : 'margin-top:3.5rem;' ?>"><?= htmlspecialchars($section['label']) ?></h2>
        <?php if ($section['note']): ?>
        <p style="text-align:center;color:var(--color-text-muted);margin-top:-1.25rem;"><?= htmlspecialchars($section['note']) ?></p>
        <?php endif; ?>
        <div class="showcase-grid">
            <?php foreach ($section['photos'] as $p): ?>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['caption']) ?>" loading="lazy">
                <figcaption>
                    <?= htmlspecialchars($p['caption']) ?>
                    <?php if (!empty($p['location'])): ?>
                        <br><span style="font-weight:400;color:var(--color-text-muted);"><?= htmlspecialchars($p['location']) ?></span>
                    <?php endif; ?>
                </figcaption>
            </figure>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
