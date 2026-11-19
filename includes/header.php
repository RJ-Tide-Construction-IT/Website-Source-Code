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
//   $noindexPage     (optional) true to keep a page out of search results while
//                    still counting visits (closed job postings, empty Safety
//                    Data Sheet groups). Unlike $privatePage, analytics stay on.
//   $canonicalPath   (optional) this page's address for search engines, e.g.
//                    '/careers/posting.php?id=foreman'. Defaults to this page's
//                    file address in lower case, so /contact.php?sent=1,
//                    /Contact.php and /contact.php/extra all count as one page
//                    (www.rjtide.com is redirected in config.php).
if (!isset($pageTitle)) { $pageTitle = SITE_NAME; }
if (!isset($pageDescription)) { $pageDescription = 'Full-service concrete, millwright, and agricultural/industrial maintenance contractor based in Lawton, Iowa.'; }
if (!isset($documentTitle)) { $documentTitle = $pageTitle . ' | ' . SITE_NAME; }
if (!isset($canonicalPath)) {
    $canonicalPath = preg_replace(['#(\.php)/.*$#', '#/index\.php$#'], ['$1', '/'], strtolower($_SERVER['SCRIPT_NAME'] ?? '/'));
}
$canonicalUrl = SITE_URL . '/' . ltrim($canonicalPath, '/');
$privatePage = !empty($privatePage);
$noindexPage = !empty($noindexPage);
$loadAnalytics = !$privatePage;
$siteTheme = site_theme(); // 'light', 'dark', or '' to follow the device

// The photo behind a banner, as a real image so browsers find it right away and
// load it first (a CSS background is found late, which is what made the
// homepage slow). Phones get a tall crop, everything else a wide one, so each
// photo needs <name>-phone.webp and <name>-wide.webp next to <name>.webp
// (README, "Adding a new photo"). $lazy is for photos further down the page.
function banner_photo(string $image, bool $lazy = false): string {
    $base = htmlspecialchars(BASE_URL . '/assets/img/' . preg_replace('/\.webp$/', '', $image));
    $loading = $lazy ? 'loading="lazy" decoding="async"' : 'fetchpriority="high"';
    return '<picture><source media="(max-width: 640px)" srcset="' . $base . '-phone.webp" width="850" height="878">'
         . '<img class="banner-photo" src="' . $base . '-wide.webp" width="1600" height="740" alt="" ' . $loading . '></picture>';
}

// The title banner at the top of every inner page. Pages call it right after
// including this file, e.g.:
//   page_hero('Contact Us');                                        dark banner
//   page_hero('Agricultural', 'agriculture/web/jobsite-2.webp');    photo banner
// $image is relative to assets/img/ and needs its -phone and -wide variants (see
// banner_photo above). $extraHtml (optional) is extra markup shown
// under the title, written by the page itself, never visitor input.
function page_hero(string $title, ?string $image = null, string $extraHtml = ''): void {
    $class = $image ? 'page-hero page-hero--photo' : 'page-hero';
    echo '<section class="' . $class . '">' . ($image ? banner_photo($image) : '') . "\n"
       . '    <div class="container"><h1>' . htmlspecialchars($title) . '</h1>' . $extraHtml . "</div>\n"
       . "</section>\n";
}

// The logo, with the white-lettered copy shown when the site is dark (README,
// "Dark mode"). With no choice made the browser picks by the device setting;
// after the light/dark button has been used, PHP picks from the cookie so the
// right logo shows straight away. main.js switches every data-dark-logo source
// when the button is pressed. $class is an optional CSS class for the <img>.
function theme_logo(string $class = ''): string {
    $media = ['dark' => 'all', 'light' => 'not all'][site_theme()] ?? '(prefers-color-scheme: dark)';
    $logo = BASE_URL . '/assets/img/logo-192';
    return '<picture><source data-dark-logo media="' . $media . '" srcset="' . $logo . '-dark.webp" width="470" height="192">'
         . '<img src="' . $logo . '.webp" width="470" height="192" alt="' . htmlspecialchars(SITE_NAME) . '"'
         . ($class !== '' ? ' class="' . $class . '"' : '') . '></picture>';
}
?>
<!DOCTYPE html>
<html lang="en"<?= $siteTheme ? ' data-theme="' . $siteTheme . '"' : '' ?>>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<!-- The site follows the device's light/dark setting unless the visitor has used the light/dark button (colours in style.css). -->
<meta name="color-scheme" content="<?= $siteTheme ?: 'light dark' ?>">
<title><?= htmlspecialchars($documentTitle) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<?php if ($privatePage): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<?php if ($noindexPage): // kept out of search results, so no canonical either ?>
<meta name="robots" content="noindex">
<?php elseif (http_response_code() === 200): // not on "not found" pages ?>
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
<!-- The two main font files load first, so text appears in the right font sooner (see the @font-face rules in style.css). -->
<link rel="preload" href="<?= BASE_URL ?>/assets/fonts/roboto-latin.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="<?= BASE_URL ?>/assets/fonts/roboto-slab-latin.woff2" as="font" type="font/woff2" crossorigin>
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
            <a href="<?= BASE_URL ?>/" class="site-header__logo">
                <?= theme_logo() ?>
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

        <!-- Light/dark switch (main.js). Moon in light mode ("switch to dark"), sun in dark mode (style.css).
             Without a saved choice PHP can't know the device setting, so main.js sets the exact label on load. -->
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="<?= ['dark' => 'Switch to light mode', 'light' => 'Switch to dark mode'][$siteTheme] ?? 'Switch between light and dark mode' ?>">
            <svg class="theme-toggle__moon" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 3a9 9 0 1 0 9 9c0-.46-.04-.92-.1-1.36a5.39 5.39 0 0 1-4.4 2.26 5.4 5.4 0 0 1-3.14-9.8c-.44-.06-.9-.1-1.36-.1z"/></svg>
            <svg class="theme-toggle__sun" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58a.996.996 0 0 0-1.41 0 .996.996 0 0 0 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0s.39-1.03 0-1.41L5.99 4.58zm12.37 12.37a.996.996 0 0 0-1.41 0 .996.996 0 0 0 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0a.996.996 0 0 0 0-1.41l-1.06-1.06zm1.06-10.96a.996.996 0 0 0 0-1.41.996.996 0 0 0-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06zM7.05 18.36a.996.996 0 0 0 0-1.41.996.996 0 0 0-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41s1.03.39 1.41 0l1.06-1.06z"/></svg>
        </button>

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
