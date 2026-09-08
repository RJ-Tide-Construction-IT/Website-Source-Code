<?php
$pageTitle = 'Agricultural Services';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/agriculture/web/jobsite-2.jpg');">
    <div class="container"><h1>Agricultural</h1></div>
</section>

<section style="padding-bottom:16px;">
    <div class="container" style="max-width:800px;">
        <p>At RJ Tide, we understand how challenging it can be to find a partner who can manage all aspects
           of your agricultural project from start to finish. Nothing is more frustrating than realizing
           that critical pieces of work were left out or overlooked. We provide a higher level of service
           by developing detailed, transparent proposals so you can be confident every part of your
           agricultural needs is covered. RJ Tide works closely with our clients to eliminate coordination
           surprises and ensure smooth, efficient progress from planning through harvest.</p>
    </div>
</section>

<section style="padding-top:0;padding-bottom:0;">
    <div class="container">
        <div class="showcase-grid" style="grid-template-columns:repeat(3, 1fr);">
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-drawing-2.jpg" alt="2D engineering drawing of the grain storage facility" loading="lazy" style="height:340px;">
                <figcaption>Engineering Drawing</figcaption>
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-concept.jpg" alt="3D design concept of a grain handling and storage facility" loading="lazy" style="height:340px;">
                <figcaption>3D Concept</figcaption>
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-reality.jpg" alt="Completed grain storage facility matching the original design" loading="lazy" style="height:340px;">
                <figcaption>Built Reality!</figcaption>
            </figure>
        </div>
    </div>
</section>

<section style="padding-top:16px;">
    <div class="container" style="max-width:800px;">
        <h3>Design Build</h3>
        <p>From concept to completion, we listen to your needs and develop a strategic plan for your
           facility. From budgets and proposals to scheduling and team coordination, we manage the entire
           process to deliver the project you need, on time and with precision.</p>
        <p>RJ Tide Construction Company provides design build services to deliver the right project to
           meet your grain handling and storage needs. We will work closely with you to determine what
           your current pain points are within your current facility and identify current and future
           expansion opportunities. Our design team will provide solutions to minimize or eliminate your
           current issues and set you up for future growth. Our goal is to provide the best valued project
           that considers future operation and maintenance cost as well as upfront construction cost.</p>

        <h3>Concrete Support for Ag Facilities</h3>
        <p>Working closely with our agricultural team, our <a href="<?= BASE_URL ?>/services/concrete-services.php">concrete division</a>
           delivers comprehensive solutions for your projects. Whether you need repairs, replacements, or
           new structures, we provide the same reliable support and quality workmanship start to finish.</p>
    </div>
</section>

<section class="section--muted">
    <div class="container">
        <ul class="service-links">
            <li><a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php#millwright">Millwright Services</a></li>
        </ul>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<section class="section--muted">
    <div class="container">
        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
