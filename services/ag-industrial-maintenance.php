<?php
$pageTitle = 'Ag / Industrial Maintenance';
$pageDescription = 'Industrial and agricultural maintenance, plus millwright installation and repair services from RJ Tide Construction.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$subServices = [
    'Industrial Maintenance' => '#industrial-maintenance',
    'Millwright'             => '#millwright',
];
?>

<section class="page-hero">
    <div class="container"><h1>Ag / Industrial Maintenance</h1></div>
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
        <p>At RJ Tide, we understand how challenging it can be to keep your agricultural and industrial
           operations running smoothly. The last thing you need is unexpected downtime or gaps in
           maintenance that slow down production. That&rsquo;s why we provide reliable, full-scope
           maintenance services designed to cover all your operational needs. From preventative care to
           emergency repairs, we work with our clients to ensure equipment, facilities, and systems stay
           operating at peak performance&mdash;so there are no surprises holding your business back.</p>

        <h3 id="millwright">Millwright</h3>
        <div class="subsection-photo">
            <video src="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse.mp4"
                   poster="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse-poster.jpg"
                   controls autoplay muted playsinline>
            </video>
        </div>
        <p>At RJ Tide Construction, our Millwright Services are designed to ensure the seamless
           installation, maintenance, and repair of industrial machinery and equipment. Our team of skilled
           millwrights brings precision and expertise to every project, ensuring optimal performance and
           longevity of your machinery.</p>
        <p><strong>Installation &amp; Alignment:</strong> Expert installation and precise alignment of
           machinery to enhance operational efficiency.</p>
        <p><strong>Maintenance &amp; Repair:</strong> Comprehensive maintenance and repair services to
           minimize downtime and extend equipment life.</p>
        <p><strong>Custom Solutions:</strong> Tailored solutions to meet the unique needs of your industrial
           operations.</p>
        <p><strong>Safety &amp; Compliance:</strong> Adherence to the highest safety standards and
           regulatory compliance to protect your workforce and assets.</p>
        <p>Trust RJ Tide Construction for reliable and efficient millwright services that keep your
           operations running smoothly.</p>
        <p>Reach out to our experts today by calling <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>

        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
