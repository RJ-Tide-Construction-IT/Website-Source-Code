<?php
$pageTitle = 'Privacy Policy';
$pageDescription = 'How RJ Tide Construction Company, Inc. collects and uses information from visitors to this website.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

// Update this whenever the wording below changes.
$lastUpdated = 'October 30, 2026';
?>

<?php page_hero('Privacy Policy'); ?>

<section>
    <div class="container container--narrow">
        <p><em>Last updated <?= $lastUpdated ?></em></p>

        <p><?= htmlspecialchars(SITE_NAME) ?> (&ldquo;RJ Tide&rdquo;, &ldquo;we&rdquo;) runs this website.
           This page explains what information we collect when you visit it or contact us through it,
           and how we use that information.</p>

        <h2>Information you send us</h2>
        <p><strong>Contact form.</strong> Your name, email address and/or phone number, the services
           you&rsquo;re interested in, and your message. We use it only to reply to you, and it&rsquo;s
           delivered to our staff by email.</p>
        <p><strong>Job applications.</strong> The details you enter on the application (such as contact
           information, address, work history, skills, and references to past employers) and any resume
           you upload. We use them only to consider you for employment. Applications are emailed to our
           hiring staff, and a copy of your resume and a short record of your contact details are kept on
           our web server, which is not publicly accessible.</p>
        <p>We don&rsquo;t sell the information you send us or share it with anyone for marketing.</p>

        <h2>Analytics and cookies</h2>
        <p>To understand how visitors use the site so we can improve it, we use:</p>
        <ul>
            <li><strong>Google Analytics</strong>, which uses cookies to count visits and see which pages
                are viewed, how visitors arrived, and roughly where they are located.
                See <a href="https://policies.google.com/technologies/partner-sites" target="_blank" rel="noopener noreferrer">how Google uses this information</a>.</li>
            <li><strong>Microsoft Clarity</strong>, which records how visitors move through pages (clicks,
                scrolling, and mouse movement) so we can find parts of the site that are confusing or broken.
                See the <a href="https://privacy.microsoft.com/privacystatement" target="_blank" rel="noopener noreferrer">Microsoft Privacy Statement</a>.</li>
        </ul>
        <p>You can block or delete cookies in your browser settings, and Google offers a
           <a href="https://tools.google.com/dlpage/gaoptout" target="_blank" rel="noopener noreferrer">browser add-on to opt out of Google Analytics</a>.
           The site works the same either way.</p>

        <h2>Other services this site uses</h2>
        <ul>
            <li><strong>Cloudflare Turnstile</strong> checks that contact form and application
                submissions come from a person, not an automated program.</li>
            <li><strong>Brevo</strong> delivers the emails sent from our forms.</li>
            <li><strong>Google Maps</strong> shows our location on the Contact page.</li>
            <li><strong>Google Fonts</strong> provides the fonts the site is displayed in.</li>
        </ul>
        <p>These services receive basic technical information, such as your IP address and browser type,
           when they load or are used.</p>

        <h2>Questions</h2>
        <p>If you have questions about this policy, or want us to delete information you&rsquo;ve sent us,
           contact us at <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a> or
           <a href="tel:<?= SITE_PHONE_TEL ?>"><?= SITE_PHONE ?></a>.</p>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
