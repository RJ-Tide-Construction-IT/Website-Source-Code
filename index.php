<?php
$pageTitle = 'Home';
$pageDescription = 'RJ Tide Construction Company, Inc., full-service concrete, agricultural, and industrial/millwright contractor based in Lawton, Iowa.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<section class="hero hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/agriculture/web/sunrise-headhouse.jpg');">
    <div class="container">
        <h1>Your Contractor Of Choice For<br>Full-Service Concrete,<br>Agricultural, &amp;<br>Industrial Construction</h1>
    </div>
</section>

<section>
    <div class="container">
        <h2 class="section-title">What type of project can RJ Tide help you with today?</h2>
        <ul class="service-links">
            <li><a href="<?= BASE_URL ?>/services/concrete-services.php">Full-Service Concrete</a></li>
            <li><a href="<?= BASE_URL ?>/services/agricultural-services.php">Agricultural Services</a></li>
            <li><a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php">Industrial &amp; Millwright Maintenance</a></li>
        </ul>
    </div>
</section>

<section class="section--dark section--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/index/web/safety-section.jpg');">
    <div class="container">
        <h2 class="section-title">Building With Safety, Quality, and Care</h2>
        <p class="lead lead--tight">
            Every job starts with a safety-first mindset. Our crews follow rigorous training and site
            protocols to protect our team, our clients, and the communities where we build, using
            sustainable practices that respect the environment for generations to come.
        </p>
        <div class="text-center">
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn--outline">Get In Touch</a>
        </div>
    </div>
</section>

<section class="section--muted">
<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/build-right-way.php'; ?>
</section>

<section>
<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/associations.php'; ?>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
