<?php
$pageTitle = 'Industrial';
$pageDescription = 'Industrial and agricultural maintenance, plus millwright installation and repair services from RJ Tide Construction.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$subServices = [
    'Industrial Maintenance' => '#industrial-maintenance',
    'Millwright'             => '#millwright',
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/industrial-maintenance/20250108_163555.jpg');">
    <div class="container"><h1>Industrial</h1></div>
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

        <div class="showcase-grid">
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/industrial-maintenance/20230516_145434.jpg" alt="Grain leg headhouse and catwalk structure" loading="lazy">
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/industrial-maintenance/20230516_150046.jpg" alt="Grain leg boot and distributor mechanism" loading="lazy">
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/IMG_2071.JPG" alt="Crane setting a grain bin section into place" loading="lazy">
            </figure>
            <figure>
                <img src="<?= BASE_URL ?>/assets/img/LPTL9279.JPG" alt="Tandem crane lift setting a grain bin section into place" loading="lazy">
            </figure>
        </div>

        <p><strong>Preventative Care:</strong> Scheduled inspections and upkeep that catch small issues
           before they turn into costly downtime. <strong>Scheduled Maintenance:</strong> Planned service
           work that keeps equipment and facilities running at peak performance year-round.
           <strong>Equipment &amp; Facility Repairs:</strong> Skilled repair work across mechanical,
           structural, and facility systems to keep operations moving. <strong>Downtime Reduction:</strong>
           Fast response and clear communication to get your facility back up and running with minimal
           disruption.</p>
        <p>Trust RJ Tide Construction to keep your agricultural and industrial operations running smoothly,
           season after season.</p>

        <h3 id="millwright">Millwright</h3>
        <p>RJ Tide knows that keeping your production running is most important. Our service crew of
           experts will do right by you and help keep you running smoothly.</p>
        <div class="subsection-photo">
            <video src="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse.mp4"
                   poster="<?= BASE_URL ?>/assets/img/millwright/web/lincolnway-timelapse-poster.jpg"
                   controls autoplay muted playsinline>
            </video>
        </div>
        <p>At RJ Tide Construction, our Millwright Services are designed to ensure the seamless
           installation, maintenance, and repair of industrial machinery and equipment. Our team prides
           itself on detailed proposals and designs for your service needs, supporting our millwright crew
           through every phase of the project. Our team of skilled millwrights brings precision and
           expertise to every project, ensuring optimal performance and longevity of your machinery.</p>
        <p><strong>Installation &amp; Alignment:</strong> Expert installation and precise alignment of
           machinery to enhance operational efficiency. <strong>Maintenance &amp; Repair:</strong>
           Comprehensive maintenance and repair services to minimize downtime and extend equipment life.
           <strong>Emergency Repairs:</strong> We respond quickly to breakdowns, often on site within hours,
           when immediate action is needed to get your facility back up and running.
           <strong>Custom Solutions:</strong> Tailored solutions to meet the unique needs of your industrial
           operations. <strong>Safety &amp; Compliance:</strong> Adherence to the highest safety standards
           and regulatory compliance to protect your workforce and assets.</p>
        <p>Trust RJ Tide Construction for reliable and efficient millwright services that keep your
           operations running smoothly.</p>
        <p>Reach out to our experts today by calling <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>

        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/vendor-carousel.php'; ?>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
