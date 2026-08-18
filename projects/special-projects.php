<?php
$pageTitle = 'Specialty Concrete';
$pageDescription = 'Photos of RJ Tide Construction decorative and specialty concrete work.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$sections = [
    [
        'label'  => 'Specialty Concrete',
        'photos' => [
            ['img' => 'special-projects/web/chapel-facade.jpg', 'caption' => 'Stone-veneer Chapel Facade with Decorative Concrete Detailing', 'location' => 'Trinity Heights'],
        ],
    ],
    [
        'label'  => 'Decorative Concrete',
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

        <?php foreach ($sections as $i => $section): ?>
        <h2 class="section-title" style="<?= $i === 0 ? 'margin-top:2rem;' : 'margin-top:3.5rem;' ?>"><?= htmlspecialchars($section['label']) ?></h2>
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
