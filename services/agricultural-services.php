<?php
$pageTitle = 'Agricultural Services';
$pageDescription = 'Agricultural design build, grain handling and storage construction, and concrete support for ag facilities from RJ Tide Construction.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$subServices = [
    'Design Build'          => '#design-build',
    'Millwright Support'    => '#millwright-support',
    'Concrete Support'      => '#concrete-support',
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/agriculture/web/jobsite-2.jpg');">
    <div class="container"><h1>Agricultural</h1></div>
</section>

<section class="section--muted">
    <div class="container">
        <ul class="service-links">
            <?php foreach ($subServices as $label => $href): ?>
            <li><a href="<?= $href ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section>
    <div class="container" style="max-width:800px;">
        <h3 id="design-build">Design Build</h3>
        <p>At RJ Tide, we understand how challenging it can be to find a partner who can manage every
           aspect of your agricultural project from start to finish. From concept to completion, we listen
           to your needs, develop a strategic plan, and manage budgets, proposals, scheduling, and team
           coordination to deliver the project you need, on time and with precision. We provide a higher
           level of service through detailed, transparent proposals, so you can be confident every part of
           your project is covered, with no coordination surprises from planning through harvest.</p>
        <p>RJ Tide Construction Company provides design build services to deliver the right project to
           meet your grain handling and storage needs. We work closely with you to identify the pain
           points in your current facility along with current and future expansion opportunities, then our
           design team provides solutions to minimize or eliminate those issues and set you up for future
           growth. Our goal is to deliver the best-valued project, one that weighs future operation and
           maintenance cost alongside upfront construction cost.</p>

        <div class="showcase-grid" style="grid-template-columns:repeat(3, 1fr);">
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-drawing-2.jpg" alt="2D engineering drawing of the grain storage facility" loading="lazy" style="aspect-ratio:3/2;height:auto;">
                <figcaption>Engineering Drawing</figcaption>
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-3d-render.jpg" alt="3D design concept of a grain handling and storage facility" loading="lazy" style="aspect-ratio:3/2;height:auto;">
                <figcaption>3D Concept</figcaption>
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/agriculture/web/design-build-reality.jpg" alt="Completed grain storage facility matching the original design" loading="lazy" style="aspect-ratio:3/2;height:auto;">
                <figcaption>Built Reality!</figcaption>
            </figure>
        </div>

        <h3 id="millwright-support" style="margin-top:3rem;">Millwright Support</h3>
        <p>Our <a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php#millwright">millwright team</a>
           keeps your grain handling and storage equipment running, from installing and aligning legs,
           distributors, and conveyor systems to fast, reliable repairs when harvest can&rsquo;t wait.
           Whether it&rsquo;s routine maintenance or an emergency breakdown, we bring the same precision and
           reliability to every job.</p>
        <h3 id="concrete-support" style="margin-top:3rem;">Concrete Support for Ag Facilities</h3>
        <p>Working closely with our agricultural team, our <a href="<?= BASE_URL ?>/services/concrete-services.php">concrete division</a>
           delivers comprehensive solutions for your projects. Whether you need repairs, replacements, or
           new structures, we provide the same reliable support and quality workmanship start to finish.</p>
        <p>Reach out to our experts today by calling <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>

        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
        <a href="<?= BASE_URL ?>/projects.php" class="back-link" style="margin-left:24px;">View our Projects &rarr;</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
