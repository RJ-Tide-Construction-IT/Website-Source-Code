<?php
// Add or edit a job posting (posting-edit.php for a new one,
// posting-edit.php?id=... to edit). Postings are stored by
// includes/job-postings.php and shown publicly by careers/posting.php.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('edit_postings');

$postings = custom_job_postings();
$id = is_string($_GET['id'] ?? null) ? $_GET['id'] : null;
if ($id !== null && !isset($postings[$id])) {
    flash('error', 'That posting wasn\'t found, it may have been deleted.');
    redirect(BASE_URL . '/dashboard/postings.php');
}
$existing   = $id !== null ? $postings[$id] : null;
$openTitles = open_job_titles();

// Starting text for a new posting, showing the formatting by example.
$exampleBody = <<<TEXT
# Summary / Objective
Describe the role in a few sentences: what the person does and who they work with.

# Essential Functions
- First main duty
- Second main duty
- Third main duty

# Required Education and Experience
- Requirement

# Work Environment
Describe the job site conditions, hours, and any travel.
TEXT;

$form = [
    'title'             => $existing['title'] ?? '',
    'salary'            => $existing['salary'] ?? '',
    'classification'    => $existing['classification'] ?? '',
    'reports_to'        => $existing['reports_to'] ?? '',
    'body'              => $existing['body'] ?? $exampleBody,
    'standard_sections' => $existing ? !empty($existing['standard_sections']) : true,
    'open'              => $existing ? in_array($existing['title'], $openTitles, true) : true,
];
$errors  = [];
$preview = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = posted_text($_POST, 'action');

    if ($action === 'delete' && $existing) {
        unset($postings[$id]);
        save_custom_job_postings($postings);
        save_job_openings(array_diff($openTitles, [$existing['title']]), $user['name']);
        audit((int) $user['id'], 'posting_deleted', $user['name'] . ' deleted the job posting ' . $existing['title']);
        flash('success', 'Deleted the ' . $existing['title'] . ' posting.');
        redirect(BASE_URL . '/dashboard/postings.php');
    }

    $form = [
        'title'             => posted_text($_POST, 'title'),
        'salary'            => posted_text($_POST, 'salary'),
        'classification'    => posted_text($_POST, 'classification'),
        'reports_to'        => posted_text($_POST, 'reports_to'),
        'body'              => posted_text($_POST, 'body'),
        'standard_sections' => !empty($_POST['standard_sections']),
        'open'              => !empty($_POST['open']),
    ];

    // Titles identify jobs everywhere (openings, the application's position
    // list), so they must be unique across built-in and added postings.
    $otherTitles = array_map('strtolower', array_diff(array_column(all_jobs(), 'title'), [$existing['title'] ?? null]));
    if ($form['title'] === '') {
        $errors[] = 'Enter a job title.';
    } elseif (strlen($form['title']) > 100) {
        $errors[] = 'Keep the job title under 100 characters.';
    } elseif (in_array(strtolower($form['title']), $otherTitles, true) || strcasecmp($form['title'], 'Other') === 0) {
        $errors[] = 'There\'s already a posting called "' . $form['title'] . '". Use a different title.';
    }
    foreach (['salary' => 'Salary range', 'classification' => 'Classification', 'reports_to' => 'Reports to'] as $key => $label) {
        if (strlen($form[$key]) > 100) {
            $errors[] = "Keep \"$label\" under 100 characters.";
        }
    }
    if ($form['body'] === '') {
        $errors[] = 'Add a description of the job.';
    } elseif (strlen($form['body']) > 20000) {
        $errors[] = 'The description is too long (20,000 characters max).';
    }

    if (!$errors && $action === 'save') {
        $postingId = $id ?? new_posting_id($form['title']);
        $postings[$postingId] = [
            'title'             => $form['title'],
            'salary'            => $form['salary'],
            'classification'    => $form['classification'],
            'reports_to'        => $form['reports_to'],
            'body'              => $form['body'],
            'standard_sections' => $form['standard_sections'],
            'updated_by'        => $user['name'],
            'updated_at'        => db_now(),
        ];
        save_custom_job_postings($postings);

        // Keep "hiring now" in step, including if the title changed.
        $open = array_diff($openTitles, [$existing['title'] ?? null]);
        if ($form['open']) {
            $open[] = $form['title'];
        }
        save_job_openings($open, $user['name']);

        if ($existing) {
            $renamed = $existing['title'] !== $form['title'] ? ' (renamed from ' . $existing['title'] . ')' : '';
            audit((int) $user['id'], 'posting_updated', $user['name'] . ' edited the job posting ' . $form['title'] . $renamed);
        } else {
            audit((int) $user['id'], 'posting_created', $user['name'] . ' added the job posting ' . $form['title']);
        }
        flash('success', 'Saved the ' . $form['title'] . ' posting.' . ($form['open'] ? ' It\'s listed on the Careers page.' : ' It isn\'t listed on the Careers page yet.'));
        redirect(BASE_URL . '/dashboard/postings.php');
    }

    $preview = !$errors;
}

dashboard_page_start($existing ? 'Edit Job Posting' : 'Add a Job Posting', $user);
?>
<p><a class="back-link" href="<?= BASE_URL ?>/dashboard/postings.php">&larr; All job postings</a></p>

<?php if ($errors): ?>
<div class="form-note form-note--error">
    <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="post" class="dash-form">
    <?= csrf_field() ?>
    <div class="form-field">
        <label for="title">Job title *</label>
        <input type="text" id="title" name="title" maxlength="100" required value="<?= htmlspecialchars($form['title']) ?>">
    </div>
    <div class="form-row">
        <div class="form-field">
            <label for="salary">Salary range</label>
            <input type="text" id="salary" name="salary" maxlength="100" placeholder="e.g. Starting at $25.00" value="<?= htmlspecialchars($form['salary']) ?>">
        </div>
        <div class="form-field">
            <label for="classification">Classification</label>
            <input type="text" id="classification" name="classification" maxlength="100" placeholder="e.g. Non-Exempt" value="<?= htmlspecialchars($form['classification']) ?>">
        </div>
        <div class="form-field">
            <label for="reports_to">Reports to</label>
            <input type="text" id="reports_to" name="reports_to" maxlength="100" placeholder="e.g. Superintendent" value="<?= htmlspecialchars($form['reports_to']) ?>">
        </div>
    </div>

    <div class="form-field">
        <label for="body">Job description *</label>
        <div class="dash-help">
            <strong>Formatting:</strong> start a line with <code># </code> for a section heading,
            <code>- </code> for a bullet point. Leave a blank line between paragraphs.
        </div>
        <textarea id="body" name="body" class="dash-posting-body" required><?= htmlspecialchars($form['body']) ?></textarea>
    </div>

    <div class="form-field">
        <label class="checkbox-option"><input type="checkbox" name="standard_sections" value="1"<?= $form['standard_sections'] ? ' checked' : '' ?>>
            Add the standard Work Authorization, EEO, and Other Duties sections at the end</label>
        <label class="checkbox-option"><input type="checkbox" name="open" value="1"<?= $form['open'] ? ' checked' : '' ?>>
            Hiring now (list it on the Careers page and the job application)</label>
    </div>

    <div class="dash-buttons">
        <button type="submit" name="action" value="preview" class="btn btn--outline-dark">Preview</button>
        <button type="submit" name="action" value="save" class="btn">Save</button>
        <?php if ($existing): ?>
        <a href="<?= BASE_URL ?>/careers/posting.php?id=<?= urlencode($id) ?>" target="_blank" rel="noopener">View the live posting</a>
        <?php endif; ?>
    </div>
</form>

<?php if ($preview): ?>
<div class="dash-preview">
    <p class="dash-preview__label">Preview (not saved yet)</p>
    <h2><?= htmlspecialchars($form['title']) ?></h2>
    <?php $meta = posting_job_meta($form); if ($meta): ?>
    <div class="job-meta">
        <?php foreach ($meta as $label => $value): ?>
        <div><strong><?= htmlspecialchars($label) ?></strong><?= htmlspecialchars($value) ?></div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <?= render_posting_body($form['body']) ?>
    <?php if ($form['standard_sections']) require $_SERVER['DOCUMENT_ROOT'] . '/includes/job-posting-standard-sections.php'; ?>
</div>
<?php endif; ?>

<?php if ($existing): ?>
<form method="post" class="dash-delete" onsubmit="return confirm('Delete the <?= htmlspecialchars(addslashes($existing['title'])) ?> posting? Anyone with the link will see &quot;not available&quot;. This can\'t be undone.');">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="delete">
    <button type="submit" class="dash-delete__button">Delete this posting</button>
</form>
<?php endif; ?>
<?php dashboard_page_end(); ?>
