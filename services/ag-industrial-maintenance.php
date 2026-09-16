<?php
$pageTitle = 'Industrial';
$pageDescription = 'Industrial and agricultural maintenance, plus millwright installation and repair services from RJ Tide Construction.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$subServices = [
    'Industrial Maintenance' => '#industrial-maintenance',
    'Millwright'             => '#millwright',
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/industrial-maintenance/web/20250108_163555.jpg');">
    <div class="container"><h1>Industrial Maintenance</h1></div>
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
        <h3 id="industrial-maintenance">Industrial Maintenance</h3>
        <p>At RJ Tide, we know how costly unexpected downtime can be. That&rsquo;s why we provide reliable,
           full-scope maintenance for your agricultural and industrial operations, from routine upkeep to
           on-call repairs, so your equipment, facilities, and systems keep running at peak performance.</p>

        <div class="showcase-grid">
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/industrial-maintenance/web/img-2071.jpg" alt="Crane setting a grain bin section into place" loading="lazy">
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/industrial-maintenance/web/lptl9279.jpg" alt="Tandem crane lift setting a grain bin section into place" loading="lazy">
            </figure>
        </div>

        <p>Our maintenance work covers the full range, from preventative care and scheduled inspections
           that catch small issues before they turn into costly downtime, to planned service work that
           keeps equipment and facilities running at peak performance year-round. When something does
           break, our team steps in with skilled repair work across mechanical, structural, and facility
           systems, responding quickly and communicating clearly so your facility is back up and running
           with minimal disruption.</p>
        <p>Trust RJ Tide Construction to keep your agricultural and industrial operations running smoothly,
           season after season.</p>

        <h3 id="millwright" style="margin-top:3rem;">Millwright</h3>
        <p>RJ Tide knows that keeping your production running is most important. Our service crew of
           experts will do right by you and help keep you running smoothly.</p>
        <div class="subsection-photo">
            <video src="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse.mp4"
                   poster="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse-poster.jpg"
                   controls autoplay muted playsinline>
            </video>
        </div>
        <p>Our millwright services cover the seamless installation, maintenance, and repair of agricultural
           and industrial machinery, from grain elevators and feed mills to industrial plants. Our in-house
           design-build team supports the crew through every phase of the project, so no job is too big or
           too small, whether it&rsquo;s a full facility build or a single breakdown.</p>
        <p>On site, our millwrights handle the rigging and crane coordination to set grain bins, legs, and
           structural components into place, then install and align the grain legs, distributors, and
           conveyor systems that keep material moving, bringing that same precision to every piece of
           machinery and drive component along the way. Our in-house welding and fabrication shop builds
           and repairs components to spec, and when harvest can&rsquo;t wait, we&rsquo;re ready with fast,
           reliable repairs. Once new equipment is in place, we stay on site through commissioning to make
           sure it runs right from day one.</p>
        <p>Trust RJ Tide Construction for reliable and efficient millwright services that keep your
           operations running smoothly.</p>
        <p>Reach out to our experts today by calling <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>

        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
