<?php
// Renders a series of labeled photo sections for a project gallery page.
// Expects $sections, each: ['label', 'note' (optional), 'photos' => [...]].
//
// A section only gets arrows and dots when it has 2+ photos, with
// just one photo there'd be nothing to navigate to, so it renders as a
// single enlarged photo instead (same box size, no dead arrow/dot buttons).
// A section with no photos yet renders just its note, no photo box.
foreach ($sections as $section):
    $photoCount = count($section['photos']);
    $isMultiSlide = $photoCount > 1;
?>
<h2 class="section-title section-title--gallery"><?= htmlspecialchars($section['label']) ?></h2>
<?php if (!empty($section['note'])): ?>
<p class="section-note"><?= htmlspecialchars($section['note']) ?></p>
<?php endif; ?>
<?php if ($photoCount > 0): ?>
    <div class="slideshow">
        <?php foreach ($section['photos'] as $idx => $p): ?>
        <img class="slideshow__slide<?= $idx === 0 ? ' is-active' : '' ?>" src="<?= BASE_URL ?>/assets/img/<?= htmlspecialchars($p['img']) ?>" alt="<?= htmlspecialchars($p['caption']) ?>" loading="lazy">
        <?php endforeach; ?>
        <?php if ($isMultiSlide): ?>
        <button type="button" class="slideshow__arrow slideshow__arrow--prev" aria-label="Previous photo">&lsaquo;</button>
        <button type="button" class="slideshow__arrow slideshow__arrow--next" aria-label="Next photo">&rsaquo;</button>
        <div class="slideshow__dots">
            <?php foreach ($section['photos'] as $idx => $p): ?>
            <button type="button" class="slideshow__dot<?= $idx === 0 ? ' is-active' : '' ?>" aria-label="Show slide <?= $idx + 1 ?>"></button>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
<?php endforeach; ?>
