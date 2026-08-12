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
                <li><a href="<?= BASE_URL ?>/services/millwright-services.php">Millwright Services</a></li>
                <li><a href="<?= BASE_URL ?>/services/agricultural-services.php">Agricultural Services</a></li>
                <li><a href="<?= BASE_URL ?>/services/ag-industrial-maintenance.php">Industrial Maintenance</a></li>
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
    <img id="lightboxImage" src="" alt="">
</div>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
