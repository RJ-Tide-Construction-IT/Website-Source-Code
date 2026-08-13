<?php
$pageTitle = 'Home';
$pageDescription = 'RJ Tide Construction Company, Inc. — full-service concrete, millwright services, and Ag/Industrial maintenance contractor based in Lawton, Iowa.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<section class="hero hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/index/web/hero-crew.jpg');">
    <div class="container">
        <h1>Your Contractor Of Choice For<br>Full-Service Concrete,<br>Millwright Services, &amp;<br>Industrial Maintenance</h1>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">What type of project can RJ Tide help you with today?</h2>
        <ul class="service-links">
            <li><a href="<?= BASE_URL ?>/services/concrete-services.php">Full-Service Concrete</a></li>
            <li><a href="<?= BASE_URL ?>/services/agricultural-services.php">Agricultural Services</a></li>
            <li><a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php">Ag/Industrial &amp; Millwright Maintenance</a></li>
        </ul>
    </div>
</section>

<section class="section--dark section--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/index/web/safety-section.jpg');">
    <div class="container">
        <h2 class="section-title">Building With Safety, Quality, and Care</h2>
        <p style="max-width:760px;margin:0 auto 1.5rem;text-align:center;">
            At RJ Tide Construction, we&rsquo;re committed to creating structures that are built to last.
            We prioritize the safety of our team, our clients, and the communities we serve while using
            sustainable practices that respect the environment and future generations.
        </p>
        <div style="text-align:center;">
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn--outline">Get In Touch</a>
        </div>
    </div>
</section>

<section class="section--muted">
    <div class="container">
        <h3 class="section-title">We Build the Right Way</h3>
        <p style="max-width:760px;margin:0 auto 2rem;text-align:center;">At RJ Tide Construction, every project reflects our commitment to quality, safety, and reliability.
           By combining proven building methods with modern innovation, we deliver results our clients can trust.</p>
        <div class="feature-grid">
            <div class="card"><h4>Quality Craftsmanship</h4></div>
            <div class="card"><h4>Reliable Timelines</h4></div>
            <div class="card"><h4>Modern Technology</h4></div>
            <div class="card"><h4>Modern Methods</h4></div>
        </div>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">Associations</h2>
        <div class="logo-strip">
            <?php foreach ($GLOBALS['ASSOCIATIONS'] as $assoc): ?>
            <a href="<?= htmlspecialchars($assoc['url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($assoc['name']) ?>">
                <img src="<?= BASE_URL ?>/assets/img/<?= $assoc['img'] ?>" alt="<?= htmlspecialchars($assoc['name']) ?>" loading="lazy">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
