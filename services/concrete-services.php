<?php
$pageTitle = 'Full-Service Concrete';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$subServices = [
    'Flatwork'                 => '#flatwork',
    'Foundations'              => '#foundations',
    'Site Concrete'            => '#site-concrete',
    'Industrial / Structural'  => '#industrial-structural',
    'Specialty Concrete'       => '#specialty-concrete',
    'Decorative Concrete'      => '#decorative-concrete',
];
?>

<section class="page-hero page-hero--photo" style="background-image:url('<?= BASE_URL ?>/assets/img/concrete/web/concrete-hero.jpg');">
    <div class="container"><h1>Full-Service Concrete</h1></div>
</section>

<section>
    <div class="container" style="max-width:800px;">
        <p>At RJ Tide, we have heard from our clients how frustrating it can be to find someone who will
           handle all the concrete requirements of their project from start to finish. There is nothing
           worse than thinking you have a complete concrete package, only to get hit with surprise gaps in
           the scope of work coverage. We provide a higher level of service by creating detailed scope
           proposals so you can be sure that you have complete concrete coverage. RJ Tide works with our
           clients to make sure that there are no coordination surprises once you get into construction.</p>
    </div>
</section>

<section class="section--muted">
    <div class="container">
        <ul class="service-links">
            <?php foreach ($subServices as $label => $href): ?>
            <li><a href="<?= $href ?>"><?= htmlspecialchars($label) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section>
    <div class="container" style="max-width:800px;">
        <h3 id="flatwork">Flatwork</h3>
        <p>We do anything from smooth concrete finished slabs to concrete stairs. We know what it takes to
           get the job done right. Our experts will provide not only the best looking, but the best quality
           concrete finishing you will get in western Iowa.</p>
        <p>Our commercial floor slabs are of the highest quality. Our certified flatwork crew will pour and
           finish slabs that are built to last. Reach out to one of our project managers today to review
           your plans and allow our team to provide smooth floor slabs for your project.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/concrete/web/flatwork.jpg" alt="RJ Tide crew finishing an interior concrete flatwork pour" loading="lazy">
        </div>

        <h3 id="foundations">Foundations</h3>
        <p>RJ Tide Construction understands that concrete drives construction schedules. You can&rsquo;t
           afford to lose critical path time because your concrete contractor can&rsquo;t keep up. We are
           concrete foundation specialists with expertise in commercial and industrial projects. From basic
           additions to large-scale commercial builds, we are not afraid to tackle complex jobs and we have
           the experience to complete them successfully and on time.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/concrete/web/foundation.jpg" alt="RJ Tide crew forming a concrete foundation on-site" loading="lazy">
        </div>

        <h3 id="site-concrete">Site Concrete</h3>
        <p>Finding a concrete contractor that can perform all aspects of site concrete, including paving,
           sidewalks and CIP site walls, is challenging enough. Finding one that can perform each of these
           different scopes with the quality expectations each requires is even more difficult. RJ Tide can
           tackle each of these site concrete components with the eye to detail you expect.</p>
        <p>RJ Tide Construction Co., Inc. understands that concrete paving represents a major investment in
           your project with the expectation of the added durability and life cycle benefits compared to
           other paving options. We use 3D laser screed technology for the preparation and pouring of PCC
           paving to make sure you are getting the correct drainage and thicknesses specified. We also
           understand that PCC paving, like all paving, is only as good as the subgrade on which it is
           placed. We work diligently with your project team to make sure that the entire system will
           perform according to design expectations.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/concrete/web/site-concrete.jpg" alt="RJ Tide crew pouring site concrete paving" loading="lazy">
        </div>

        <h3 id="industrial-structural">Industrial / Structural</h3>
        <p>Industrial / heavy structural projects may seem like a daunting task, but RJ Tide Construction
           can help ease those concerns. Our Project Managers, with a combined 50 years of project
           management experience, can help ensure the project stays on track and on time.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/concrete/web/industrial.jpg" alt="RJ Tide crew finishing an industrial concrete slab" loading="lazy">
        </div>

        <h3 id="specialty-concrete">Specialty Concrete</h3>
        <p>RJ Tide has a habit of taking on unique projects that, frankly, a lot of other concrete
           contractors don't want to touch. Do you have a special project? Doing something unique with
           concrete? Talk to our experts to help form your idea into a reality. No matter the size of the
           project, our qualified staff can form your special project into something worth showing off.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/special-projects/web/chapel-facade.jpg" alt="Stone-veneer chapel facade with decorative concrete detailing" loading="lazy">
        </div>

        <h3 id="decorative-concrete">Decorative Concrete</h3>
        <p>Beyond structural work, our crews bring the same attention to detail to decorative and
           architectural concrete, finishes and details meant to be seen, not just walked or driven on.
           From custom relief work to finishing touches on a monument or memorial, we treat decorative
           concrete as a craft in its own right.</p>

        <div class="subsection-photo">
            <img src="<?= BASE_URL ?>/assets/img/special-projects/web/relief-panel.jpg" alt="Bronze relief memorial panel set in a decorative concrete monument" loading="lazy">
        </div>
    </div>
</section>

<section class="section--muted">
    <div class="container">
        <a href="<?= BASE_URL ?>/index.php" class="back-link">&larr; Go back to main menu</a>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
