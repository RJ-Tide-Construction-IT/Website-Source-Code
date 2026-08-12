<?php
$pageTitle = 'Contact Us';
$pageDescription = 'Get in touch with RJ Tide Construction Company, Inc. — Lawton, Iowa.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$submitted = isset($_GET['sent']);
$submitError = isset($_GET['error']);
?>

<section class="page-hero">
    <div class="container"><h1>Contact Us</h1></div>
</section>

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
            <div class="form-note form-note--success">Thanks — your message has been sent. We'll be in touch soon.</div>
        <?php elseif ($submitError): ?>
            <div class="form-note form-note--error">Something went wrong sending your message. Please try again or call us directly.</div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/contact-handler.php" method="post" style="max-width:640px;">
            <div class="form-field">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone">
            </div>
            <div class="form-field">
                <label for="interest">I'm interested in</label>
                <select id="interest" name="interest">
                    <option value="Concrete">Concrete</option>
                    <option value="Agricultural">Agricultural</option>
                    <option value="Ag / Industrial Maintenance">Ag / Industrial Maintenance</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-field">
                <label for="message">Message</label>
                <textarea id="message" name="message" required></textarea>
            </div>
            <!-- honeypot field: real users never fill this in -->
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Leave blank</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn">Send Message</button>
        </form>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
