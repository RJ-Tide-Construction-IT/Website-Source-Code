<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Every page sets these before including header.php:
//   $pageTitle       (required) e.g. "About Us"
//   $pageDescription (optional) meta description
//   $privatePage     (optional) true on Employee Dashboard pages: hides the page
//                    from search engines and skips Google Analytics / Clarity,
//                    so employee information is never sent to tracking tools.
//   $documentTitle   (optional) the whole browser-tab / Google title, used as-is
//                    instead of "$pageTitle | site name" (the homepage uses it).
//   $canonicalPath   (optional) this page's address for search engines, e.g.
//                    '/careers/posting.php?id=foreman'. Defaults to the current
//                    address without its ?query, so /contact.php?sent=1 and
//                    www.rjtide.com/contact.php all count as one page.
if (!isset($pageTitle)) { $pageTitle = SITE_NAME; }
if (!isset($pageDescription)) { $pageDescription = 'Full-service concrete, millwright, and agricultural/industrial maintenance contractor based in Lawton, Iowa.'; }
if (!isset($documentTitle)) { $documentTitle = $pageTitle . ' | ' . SITE_NAME; }
if (!isset($canonicalPath)) {
    $canonicalPath = preg_replace('#/index\.php$#', '/', strtok($_SERVER['REQUEST_URI'] ?? '/', '?'));
}
$canonicalUrl = SITE_URL . '/' . ltrim($canonicalPath, '/');
$privatePage = !empty($privatePage);
$loadAnalytics = !$privatePage;

// The title banner at the top of every inner page. Pages call it right after
// including this file, e.g.:
//   page_hero('Contact Us');                                        dark banner
//   page_hero('Agricultural', 'agriculture/web/jobsite-2.webp');    photo banner
// $image is relative to assets/img/. $extraHtml (optional) is extra markup shown
// under the title, written by the page itself, never visitor input.
function page_hero(string $title, ?string $image = null, string $extraHtml = ''): void {
    $class = $image ? 'page-hero page-hero--photo' : 'page-hero';
    $style = $image ? ' style="background-image:url(\'' . htmlspecialchars(BASE_URL . '/assets/img/' . $image) . '\');"' : '';
    echo '<section class="' . $class . '"' . $style . ">\n"
       . '    <div class="container"><h1>' . htmlspecialchars($title) . '</h1>' . $extraHtml . "</div>\n"
       . "</section>\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($documentTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<?php if ($privatePage): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<?php if (http_response_code() === 200): // not on "not found" pages ?>
<link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
<?php endif; ?>
<!-- Link preview card when a page is shared by text, Facebook, LinkedIn, etc. -->
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= htmlspecialchars(SITE_NAME) ?>">
<meta property="og:title" content="<?= htmlspecialchars($documentTitle) ?>">
<meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
<meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
<meta property="og:image" content="<?= SITE_URL ?>/assets/img/share.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/img/favicon/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/img/favicon/favicon-192x192.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/favicon/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= asset_url('/assets/css/style.css') ?>">
<?php if ($loadAnalytics && GA4_MEASUREMENT_ID !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(GA4_MEASUREMENT_ID) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars(GA4_MEASUREMENT_ID) ?>');
</script>
<?php endif; ?>
<?php if ($loadAnalytics && CLARITY_PROJECT_ID !== ''): ?>
<script>
  (function(c,l,a,r,i,t,y){
    c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
    t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
    y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
  })(window, document, "clarity", "script", "<?= htmlspecialchars(CLARITY_PROJECT_ID) ?>");
</script>
<?php endif; ?>
</head>
<body>
<a href="#main" class="skip-link">Skip to main content</a>

<header class="site-header">
    <div class="container site-header__inner">
        <div class="site-header__brand">
            <a href="<?= BASE_URL ?>/index.php" class="site-header__logo">
                <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="<?= htmlspecialchars(SITE_NAME) ?>">
            </a>

            <div class="site-header__contact">
                <a href="https://maps.google.com/?q=<?= urlencode(SITE_ADDRESS) ?>" class="site-header__pill site-header__address" target="_blank" rel="noopener noreferrer">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C7.86 2 4.5 5.36 4.5 9.5c0 5.5 6.5 11.5 7 12 .5-.5 7-6.5 7-12C18.5 5.36 15.14 2 12 2zm0 10.25a2.75 2.75 0 1 1 0-5.5 2.75 2.75 0 0 1 0 5.5z"/></svg>
                    <?= htmlspecialchars(SITE_ADDRESS) ?>
                </a>
                <a href="tel:<?= SITE_PHONE_TEL ?>" class="site-header__pill site-header__phone" aria-label="Call <?= SITE_PHONE ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.02-.24c1.12.37 2.33.57 3.57.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1C10.61 21 3 13.39 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z"/></svg>
                    <span class="site-header__phone-number"><?= SITE_PHONE ?></span>
                </a>
            </div>
        </div>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation" aria-expanded="false" aria-controls="mainNav">
            <span></span><span></span><span></span>
        </button>

        <nav class="main-nav" id="mainNav">
            <ul>
                <?php foreach ($GLOBALS['MAIN_NAV'] as $label => $href):
                    $isActive = basename($_SERVER['PHP_SELF']) === basename($href); ?>
                <li><a href="<?= BASE_URL . $href ?>" class="<?= $isActive ? 'is-active' : '' ?>"><?= htmlspecialchars($label) ?></a></li>
                <?php endforeach; ?>
            </ul>
            <a href="<?= BASE_URL ?>/contact.php" class="btn btn--nav-cta">Contact</a>
        </nav>
    </div>
</header>

<main id="main" tabindex="-1">
