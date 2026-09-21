</main>

<footer class="site-footer">
    <div class="container site-footer__inner">
        <div class="site-footer__col">
            <h3><?= htmlspecialchars(SITE_NAME) ?></h3>
            <p><?= htmlspecialchars(SITE_ADDRESS) ?></p>
            <p><a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a></p>
            <p><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
        </div>

        <div class="site-footer__col">
            <h4>Services</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/services/concrete-services.php">Full-Service Concrete</a></li>
                <li><a href="<?= BASE_URL ?>/services/agricultural-services.php">Agricultural Services</a></li>
                <li><a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php">Industrial &amp; Millwright Maintenance</a></li>
            </ul>
        </div>

        <div class="site-footer__col">
            <h4>Company</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>/about.php">About</a></li>
                <li><a href="<?= BASE_URL ?>/projects.php">Projects</a></li>
                <li><a href="<?= BASE_URL ?>/careers.php">Careers</a></li>
                <li><a href="<?= BASE_URL ?>/contact.php">Contact Us</a></li>
            </ul>
        </div>
    </div>

    <div class="site-footer__bottom container">
        <p>&copy; <?= date('Y') ?> <?= htmlspecialchars(SITE_NAME) ?>. All rights reserved.</p>
    </div>
</footer>

<div class="lightbox-overlay" id="lightboxOverlay">
    <button class="lightbox-overlay__close" id="lightboxClose" aria-label="Close">&times;</button>
    <button type="button" class="lightbox-overlay__arrow lightbox-overlay__arrow--prev" id="lightboxPrev" aria-label="Previous photo">&lsaquo;</button>
    <img id="lightboxImage" src="" alt="">
    <button type="button" class="lightbox-overlay__arrow lightbox-overlay__arrow--next" id="lightboxNext" aria-label="Next photo">&rsaquo;</button>
</div>

<div class="cookie-banner" id="cookieBanner" hidden>
    <p>We use cookies to understand how visitors use this site, so we can improve it. <a href="mailto:<?= SITE_EMAIL ?>">Contact us</a> with questions.</p>
    <button type="button" class="btn btn--sm" id="cookieBannerDismiss">Got it</button>
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
