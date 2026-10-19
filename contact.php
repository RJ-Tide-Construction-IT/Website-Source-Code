<?php
$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with RJ Tide Construction Company, Inc., Lawton, Iowa.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/turnstile.php';

$submitted = isset($_GET['sent']);
$submitError = isset($_GET['error']);
?>

<?php page_hero('Contact Us'); ?>

<section>
    <div class="container">
        <div class="contact-details">
            <div>
                <h4>Address</h4>
                <p><?= htmlspecialchars(SITE_ADDRESS) ?></p>
            </div>
            <div>
                <h4>Phone</h4>
                <p><a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a></p>
            </div>
            <div>
                <h4>Email</h4>
                <p><a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
            </div>
        </div>

        <?php if ($submitted): ?>
            <div class="form-note form-note--success">Thanks, your message has been sent. We'll be in touch soon.</div>
        <?php elseif ($submitError): ?>
            <div class="form-note form-note--error">Something went wrong sending your message. Please try again or call us directly.</div>
        <?php endif; ?>

        <div class="contact-layout">
            <form id="contact-form" action="<?= BASE_URL ?>/contact-handler.php" method="post">
                <div class="form-field">
                    <label for="name">Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                <p class="form-field-hint">Please provide an email address or phone number so we can reach you.</p>
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email">
                </div>
                <div class="form-field">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone">
                </div>
                <div class="form-field">
                    <label>I'm interested in</label>
                    <div class="checkbox-group">
                        <?php foreach ($GLOBALS['CONTACT_INTERESTS'] as $interest): ?>
                        <label class="checkbox-option">
                            <input type="checkbox" name="interest[]" value="<?= htmlspecialchars($interest) ?>">
                            <?= htmlspecialchars($interest) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="form-field">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                <!-- honeypot field: real users never fill this in -->
                <div class="honeypot" aria-hidden="true">
                    <label for="website">Leave blank</label>
                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                </div>
                <?php turnstile_widget(); ?>
                <button type="submit" class="btn">Send Message</button>
            </form>

            <div class="map-embed">
                <iframe src="https://maps.google.com/maps?q=<?= urlencode(SITE_ADDRESS) ?>&output=embed"
                        loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        title="Map showing <?= htmlspecialchars(SITE_NAME) ?>'s location">
                </iframe>
            </div>
        </div>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
