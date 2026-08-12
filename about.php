<?php
$pageTitle = 'About';
$pageDescription = 'More than 15 years of quality construction. Learn about RJ Tide Construction Company, Inc.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container"><h1>More Than 15 Years of Quality Construction!</h1></div>
</section>

<section>
    <div class="container" style="max-width:800px;">
        <div style="margin-bottom:2rem;">
            <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="<?= htmlspecialchars(SITE_NAME) ?>" style="max-width:320px;width:100%;height:auto;margin:0 auto;">
        </div>

        <p>RJ Tide Construction was founded in 2010 as a full-service concrete contractor. The founding
           partners all came from general contracting and design-build backgrounds and had grown tired
           of not being able to find competent concrete contractors capable of being a one-stop option
           for all aspects of concrete construction.</p>

        <p>Through the years, RJ Tide established its reputation for being the &ldquo;easy button&rdquo;
           for general contractors and owners in Iowa, Nebraska, and South Dakota. RJ Tide is known for
           not only meeting tough schedules and pouring high quality concrete, our attention to detail
           and knowledge of general contracting provides added value to our clients through
           constructability reviews, value engineering, and proactive coordination with the other trades.</p>

        <p>Over time, RJ Tide added Ag Millwright Services and Industrial Maintenance to our areas of
           expertise to better serve our customer needs. RJ Tide has continued to expand by adding
           design-build and general contracting services for our customers along with our traditional
           concrete and millwright services.</p>

        <p>Contact RJ Tide today to see how we can help you!</p>

        <div style="text-align:center;margin-top:2.5rem;">
            <a href="<?= BASE_URL ?>/contact.php" class="btn">Get In Touch</a>
        </div>
    </div>
</section>

<section class="section--muted">
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

<section>
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

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
