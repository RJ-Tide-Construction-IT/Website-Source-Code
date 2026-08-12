<?php
$pageTitle = 'Special Projects';
$pageDescription = 'Photos of RJ Tide Construction decorative and specialty concrete work.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// 'location' is left blank until real per-photo job-site locations are
// available; the gallery already renders it when a photo has one.
$photos = [
    ['img' => 'special-projects/web/relief-panel.jpg', 'caption' => 'Custom Statue of Saint Joseph', 'location' => 'Trinity Heights'],
    ['img' => 'special-projects/web/chapel-facade.jpg', 'caption' => 'Stone-veneer Chapel Facade with Decorative Concrete Detailing', 'location' => 'Trinity Heights'],
];
?>

<section class="page-hero">
    <div class="container"><h1>Special Projects</h1></div>
</section>

<section>
    <div class="container">
        <a href="<?= BASE_URL ?>/projects.php" class="back-link">&larr; Back to Projects</a>
        <div class="showcase-grid" style="margin-top:1.5rem;">
            <?php foreach ($photos as $p): ?>
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
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
