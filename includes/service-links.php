<?php
// Row of "jump to" buttons linking to the sections further down a service page.
// Expects, set by the page before requiring this file:
//   $subServices         ['Button label' => '#section-id', ...]
//   $compactServiceLinks (optional) true to keep every button on one row, used
//                        on the Concrete page where there are six of them.
?>
<section class="section--muted">
    <div class="container">
        <ul class="service-links<?= !empty($compactServiceLinks) ? ' service-links--compact' : '' ?>">
            <?php foreach ($subServices as $label => $href): ?>
            <li><a href="<?= htmlspecialchars($href) ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
