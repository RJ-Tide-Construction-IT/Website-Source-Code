<?php
$pageTitle = 'Millwright Services';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1>Millwright</h1></div>
</section>

<section>
    <div class="container" style="max-width:800px;">
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
        <a href="<?= BASE_URL ?>/services/agricultural-services.php" class="back-link">&larr; Go back</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
