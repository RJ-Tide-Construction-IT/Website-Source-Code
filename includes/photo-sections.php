<?php
// Renders a series of labeled photo sections for a project gallery page.
// Expects $sections, each: ['label', 'note', 'photos' => [...], 'layout' => 'carousel' (optional)].
//
// A section only becomes an actual carousel when it has 2+ photos — with
// just one photo there'd be nothing to navigate to, so it renders as a
// single enlarged photo instead (same box size, no dead arrow/dot buttons).
// A section with no photos yet renders just its note, no photo box.
foreach ($sections as $i => $section):
    $photoCount = count($section['photos']);
    $isCarousel = ($section['layout'] ?? '') === 'carousel' && $photoCount > 0;
    $isMultiSlide = $isCarousel && $photoCount > 1;
?>
<h2 class="section-title" style="<?= $i === 0 ? 'margin-top:2rem;' : 'margin-top:3.5rem;' ?>"><?= htmlspecialchars($section['label']) ?></h2>
<?php if (!empty($section['note'])): ?>
<p style="text-align:center;color:var(--color-text-muted);margin-top:-1.25rem;"><?= htmlspecialchars($section['note']) ?></p>
<?php endif; ?>
<?php if ($isCarousel): ?>
    <?php
    // A photo's location (when set) is folded into its caption text, since
    // the slideshow caption is a single line, not a figure/figcaption pair.
    $captionFor = function ($p) {
        return $p['caption'] . (!empty($p['location']) ? ' — ' . $p['location'] : '');
    };
    ?>
    <div class="slideshow">
        <?php foreach ($section['photos'] as $idx => $p): ?>
        <img class="slideshow__slide<?= $idx === 0 ? ' is-active' : '' ?>" data-caption="<?= htmlspecialchars($captionFor($p)) ?>" src="<?= BASE_URL ?>/assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['caption']) ?>" loading="lazy">
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
    <p class="slideshow__caption"><?= htmlspecialchars($captionFor($section['photos'][0])) ?></p>
<?php elseif ($photoCount > 0): ?>
    <div class="showcase-grid">
        <?php foreach ($section['photos'] as $p): ?>
        <figure>
            <img src="<?= BASE_URL ?>/assets/img/<?= $p['img'] ?>" alt="<?= htmlspecialchars($p['caption']) ?>" loading="lazy">
            <figcaption>
                <?= htmlspecialchars($p['caption']) ?>
                <?php if (!empty($p['location'])): ?>
                    <br><span style="font-weight:400;color:var(--color-text-muted);"><?= htmlspecialchars($p['location']) ?></span>
                <?php endif; ?>
            </figcaption>
        </figure>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php endforeach; ?>
