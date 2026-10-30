<?php
// Shown for any address on the site that doesn't exist (wired up in
// web.config for the live IIS server, and .htaccess for Apache).
http_response_code(404);
$pageTitle = 'Page Not Found';
$pageDescription = 'The page you were looking for could not be found.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
?>

<?php page_hero('Page Not Found'); ?>

<section>
    <div class="container container--narrow text-center">
        <p>Sorry, we couldn&rsquo;t find that page. It may have moved, or the address may be mistyped.</p>
        <p>
            <a href="<?= BASE_URL ?>/" class="btn">Go to the homepage</a>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn--outline-dark">Contact us</a>
        </p>
        <p>Or call us at <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
