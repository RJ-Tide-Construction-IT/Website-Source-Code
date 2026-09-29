<?php
// "Associations" logo strip, shared by the homepage and the About page. The
// logo list itself lives in $GLOBALS['ASSOCIATIONS'] in includes/config.php.
// Each page wraps this in its own <section>, so this file is just the inside.
?>
    <div class="container">
        <h2 class="section-title">Associations</h2>
        <div class="logo-strip">
            <?php foreach ($GLOBALS['ASSOCIATIONS'] as $assoc): ?>
            <a href="<?= htmlspecialchars($assoc['url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($assoc['name']) ?>">
                <img src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($assoc['img']) ?>" alt="<?= htmlspecialchars($assoc['name']) ?>" loading="lazy">
            </a>
            <?php endforeach; ?>
        </div>
    </div>
