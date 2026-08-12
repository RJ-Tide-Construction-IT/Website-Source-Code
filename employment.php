<?php
$pageTitle = 'Job Application';
$pageDescription = 'Apply to join RJ Tide Construction Company, Inc.';
require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';

$jobs = [
    'Craftsman 1', 'Craftsman 2', 'Craftsman 3', 'Concrete Foreman',
    'Concrete Superintendent', 'Millwright Superintendent',
    'Project Manager / Estimator', 'Millwright 1', 'Millwright 2', 'Other',
];

$submitted   = isset($_GET['sent']);
$submitError = $_GET['error'] ?? null;
$errorMessages = [
    'validation' => 'Please fill in all required fields with a valid email address.',
    'file'       => 'Your resume must be a PDF or Word document under 5MB.',
    'upload'     => 'We couldn\'t save your resume upload. Please try again.',
];
?>

<section class="page-hero">
    <div class="container">
        <h1>Job Application</h1>
        <a href="<?= BASE_URL ?>/careers.php" class="btn btn--outline" style="margin-top:1rem;">&larr; Go back to Careers</a>
    </div>
</section>

<section>
    <div class="container">
        <?php if ($submitted): ?>
            <div class="form-note form-note--success">Thanks for applying — we've received your application and will be in touch.</div>
        <?php elseif ($submitError): ?>
            <div class="form-note form-note--error"><?= htmlspecialchars($errorMessages[$submitError] ?? 'Something went wrong. Please try again.') ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/apply-handler.php" method="post" enctype="multipart/form-data" style="max-width:640px;">
            <div class="form-field">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-field">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-field">
                <label for="phone">Phone</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-field">
                <label for="position">Position Applying For</label>
                <select id="position" name="position" required>
                    <?php foreach ($jobs as $job): ?>
                    <option value="<?= htmlspecialchars($job) ?>"><?= htmlspecialchars($job) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-field">
                <label for="resume">Resume (PDF or Word, max 5MB)</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>
            </div>
            <div class="form-field">
                <label for="message">Anything else you'd like us to know?</label>
                <textarea id="message" name="message"></textarea>
            </div>
            <div style="position:absolute;left:-9999px;" aria-hidden="true">
                <label for="website">Leave blank</label>
                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
            </div>
            <button type="submit" class="btn">Submit Application</button>
        </form>
    </div>
</section>

<?php require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php'; ?>
