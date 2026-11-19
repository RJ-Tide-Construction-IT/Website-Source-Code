<?php
// "We Build the Right Way" intro + feature cards, shared by the homepage and
// the About page. Each page wraps it in its own <section> (the homepage uses
// the grey background, About the plain one), so this file is just the inside.
//
// Each card: a small outline icon drawn in the site's red, a title, and one
// line of text. To change a card's wording, edit its 'title' or 'text' below.
$buildRightWayCards = [
    [
        'title' => 'Quality Craftsmanship',
        'text'  => 'Attention to detail on every pour, from flatwork to decorative finishes.',
        // award badge with a check mark
        'icon'  => '<circle cx="12" cy="8" r="6"/><polyline points="9.5 8 11.5 10 14.5 6.5"/><polyline points="8.5 13 7 22 12 19 17 22 15.5 13"/>',
    ],
    [
        'title' => 'Reliable Timelines',
        'text'  => 'Project managers with a combined 50 years of experience keep your project on track.',
        // clock
        'icon'  => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
    ],
    [
        'title' => 'Modern Technology',
        'text'  => '3D laser screed paving, plus design-build drawings and 3D renders.',
        // computer chip
        'icon'  => '<rect x="4" y="4" width="16" height="16" rx="2"/><rect x="9" y="9" width="6" height="6"/><path d="M9 1v3M15 1v3M9 20v3M15 20v3M20 9h3M20 15h3M1 9h3M1 15h3"/>',
    ],
    [
        'title' => 'Experienced Crews',
        'text'  => 'Trained, certified crews building across western Iowa since 2010.',
        // group of people
        'icon'  => '<circle cx="9" cy="7" r="4"/><path d="M2 21v-2a4 4 0 0 1 4-4h6a4 4 0 0 1 4 4v2"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>',
    ],
];
?>
    <div class="container">
        <h2 class="section-title">We Build the Right Way</h2>
        <p class="lead">From the first shovel of dirt to the final walkthrough, our crews bring decades of hands-on
           experience to every project. We pair proven construction methods with the latest equipment and
           technology to keep your project on schedule and within budget.</p>
        <div class="feature-grid">
            <?php foreach ($buildRightWayCards as $feature): ?>
            <div class="card">
                <svg class="card__icon" viewBox="0 0 24 24" width="40" height="40" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?= $feature['icon'] ?></svg>
                <h3><?= htmlspecialchars($feature['title']) ?></h3>
                <p><?= htmlspecialchars($feature['text']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
