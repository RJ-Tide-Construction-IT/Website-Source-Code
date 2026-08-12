<?php
$pageTitle = 'Concrete Projects';
$pageDescription = 'Photos of RJ Tide Construction concrete work — flatwork, foundations, commercial, residential, and wastewater treatment concrete.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Flatwork',
        'note'   => '',
        'photos' => [
            ['img' => 'concrete/web/flatwork.jpg', 'caption' => 'Finishing an interior concrete flatwork pour', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Foundations',
        'note'   => '',
        'photos' => [
            ['img' => 'concrete/web/foundation.jpg', 'caption' => 'Forming a concrete foundation on-site', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Commercial',
        'note'   => '',
        'photos' => [
            ['img' => 'concrete/web/concrete-hero.jpg', 'caption' => 'Placing and finishing a large concrete slab pour', 'location' => ''],
            ['img' => 'concrete/web/industrial.jpg', 'caption' => 'Finishing an industrial concrete slab', 'location' => ''],
            ['img' => 'concrete/web/site-concrete.jpg', 'caption' => 'Pouring site concrete paving', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Residential',
        'note'   => 'Photos for this section are coming soon.',
        'photos' => [
            ['img' => 'concrete/web/residential-placeholder.jpg', 'caption' => 'Residential', 'location' => ''],
        ],
    ],
    [
        'label'  => 'Wastewater Treatment (WWTP)',
        'note'   => 'Photos for this section are coming soon.',
        'photos' => [
            ['img' => 'concrete/web/wwtp-placeholder.jpg', 'caption' => 'Wastewater Treatment (WWTP)', 'location' => ''],
        ],
    ],
];
?>

<section class="page-hero">
    <div class="container"><h1>Concrete</h1></div>
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
