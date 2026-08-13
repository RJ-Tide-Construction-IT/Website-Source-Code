<?php
$pageTitle = 'Projects';
$pageDescription = 'RJ Tide Construction project history since 2018 — concrete, millwright, and agricultural/industrial work across the Midwest.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$markets = [
    [
        'label' => 'Concrete',
        'blurb' => 'Flatwork, foundations, and site concrete for commercial and industrial sites.',
        'img'   => 'concrete/web/concrete-hero.jpg',
        'url'   => '/projects/concrete.php',
    ],
    [
        'label' => 'Agricultural',
        'blurb' => 'Grain handling and storage, plus the industrial maintenance and millwright work that keeps ag facilities running.',
        'img'   => 'agriculture/web/design-build-reality.jpg',
        'url'   => '/projects/agricultural.php',
    ],
    [
        'label' => 'Specialty Concrete',
        'blurb' => 'Decorative and specialty concrete work, built to a different standard.',
        'img'   => 'special-projects/web/chapel-facade.jpg',
        'url'   => '/projects/special-projects.php',
    ],
];
?>

<section class="page-hero">
    <div class="container">
        <h1>Project History</h1>
        <p style="color:var(--color-peach);margin-top:0.75rem;">
            Building Siouxland since 2010
        </p>
    </div>
</section>

<section>
    <div class="container" style="max-width:1100px;">
        <h2 class="section-title">Project Gallery</h2>
        <p style="text-align:center;color:var(--color-text-muted);max-width:640px;margin:0 auto 1rem;">
            A look at the kinds of work we do across concrete, agricultural, and industrial construction.
        </p>
        <div class="market-grid">
            <?php foreach ($markets as $m): ?>
            <a class="market-card" href="<?= BASE_URL . $m['url'] ?>">
                <img src="<?= BASE_URL ?>/assets/img/<?= $m['img'] ?>" alt="<?= htmlspecialchars($m['label']) ?>" loading="lazy">
                <div class="market-card__body">
                    <h3><?= htmlspecialchars($m['label']) ?></h3>
                    <p><?= htmlspecialchars($m['blurb']) ?></p>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
