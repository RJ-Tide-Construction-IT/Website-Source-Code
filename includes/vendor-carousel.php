<?php
// Renders the "Our Vendors" auto-advancing logo carousel. Included on every
// page that lists $GLOBALS['VENDORS'] so the markup/behavior lives in one
// place instead of being duplicated per service page.
//
// The vendor list is rendered twice — the second pass is a hidden clone
// appended after the real slides, purely so the JS can scroll past the end
// of the real list onto visually-identical clone slides and snap back
// unseen, instead of visibly jumping back to slide 1.
$vendorCount = count($GLOBALS['VENDORS']);
?>
<section class="section--muted" style="padding:40px 0;">
    <div class="container">
        <h2 class="section-title">Our Vendors</h2>
        <div class="vendor-carousel" data-interval="5000">
            <button type="button" class="vendor-carousel__arrow vendor-carousel__arrow--prev" aria-label="Previous vendors">&lsaquo;</button>
            <div class="vendor-carousel__viewport">
                <ul class="vendor-carousel__track" data-real-count="<?= $vendorCount ?>">
                    <?php foreach ($GLOBALS['VENDORS'] as $vendor): ?>
                    <li class="vendor-carousel__slide">
                        <a class="vendor-carousel__box" href="<?= htmlspecialchars($vendor['url']) ?>" target="_blank" rel="noopener noreferrer" title="<?= htmlspecialchars($vendor['name']) ?>">
                            <img src="<?= BASE_URL ?>/assets/img/vendors/<?= $vendor['img'] ?>" alt="<?= htmlspecialchars($vendor['name']) ?>" loading="lazy">
                        </a>
                    </li>
                    <?php endforeach; ?>
                    <?php foreach ($GLOBALS['VENDORS'] as $vendor): ?>
                    <li class="vendor-carousel__slide" aria-hidden="true">
                        <a class="vendor-carousel__box" href="<?= htmlspecialchars($vendor['url']) ?>" tabindex="-1" title="<?= htmlspecialchars($vendor['name']) ?>">
                            <img src="<?= BASE_URL ?>/assets/img/vendors/<?= $vendor['img'] ?>" alt="" loading="lazy">
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <button type="button" class="vendor-carousel__arrow vendor-carousel__arrow--next" aria-label="Next vendors">&rsaquo;</button>
        </div>
    </div>
</section>
