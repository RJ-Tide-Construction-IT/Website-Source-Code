<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/config.php';

// Every page sets these before including header.php:
//   $pageTitle       (required) e.g. "About Us"
//   $pageDescription (optional) meta description
if (!isset($pageTitle)) { $pageTitle = SITE_NAME; }
if (!isset($pageDescription)) { $pageDescription = 'Full-service concrete, millwright, and agricultural/industrial maintenance contractor based in Lawton, Iowa.'; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars(SITE_NAME) ?></title>
<meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
<link rel="icon" type="image/png" sizes="32x32" href="<?= BASE_URL ?>/assets/img/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="<?= BASE_URL ?>/assets/img/favicon/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="192x192" href="<?= BASE_URL ?>/assets/img/favicon/favicon-192x192.png">
<link rel="apple-touch-icon" sizes="180x180" href="<?= BASE_URL ?>/assets/img/favicon/apple-touch-icon.png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&family=Roboto+Slab:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<?php if (GA4_MEASUREMENT_ID !== ''): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars(GA4_MEASUREMENT_ID) ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '<?= htmlspecialchars(GA4_MEASUREMENT_ID) ?>');
</script>
<?php endif; ?>
<?php if (CLARITY_PROJECT_ID !== ''): ?>
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

<header class="site-header">
    <div class="container site-header__inner">
        <div class="site-header__brand">
            <a href="<?= BASE_URL ?>/index.php" class="site-header__logo">
                <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="<?= htmlspecialchars(SITE_NAME) ?>">
            </a>

            <a href="https://maps.google.com/?q=<?= urlencode(SITE_ADDRESS) ?>" class="site-header__address" target="_blank" rel="noopener noreferrer">
                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C7.86 2 4.5 5.36 4.5 9.5c0 5.5 6.5 11.5 7 12 .5-.5 7-6.5 7-12C18.5 5.36 15.14 2 12 2zm0 10.25a2.75 2.75 0 1 1 0-5.5 2.75 2.75 0 0 1 0 5.5z"/></svg>
                <?= htmlspecialchars(SITE_ADDRESS) ?>
            </a>
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

<main>
