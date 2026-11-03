<?php
// My Documents: upload a safety form, certification, or payroll form, and see
// (and remove) your own uploads. The rules are in includes/dashboard/documents.php.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

$errors = [];
if (form_submitted()) {
    if (posted_text($_POST, 'action') === 'remove') {
        $doc = find_document((int) ($_POST['document_id'] ?? 0));
        if ($doc && (int) $doc['user_id'] === (int) $user['id'] && $doc['status'] === 'received') {
            close_document($user, $doc, 'removed');
            flash('success', 'Removed ' . $doc['title'] . '. The file has been deleted.');
        } else {
            flash('error', 'That document can\'t be removed (it may already have been processed).');
        }
        redirect(BASE_URL . '/dashboard/documents.php');
    }
    $errors = upload_document($user, $_POST, $_FILES['file'] ?? []);
    if (!$errors) {
        flash('success', 'Uploaded ' . posted_text($_POST, 'title') . '. It\'s been sent to '
            . (posted_text($_POST, 'type') === 'payroll' ? 'HR' : 'the safety team') . '.');
        redirect(BASE_URL . '/dashboard/documents.php');
    }
}
$v = fn(string $key) => htmlspecialchars(posted_text($_POST, $key));

$stmt = db()->prepare('SELECT * FROM documents WHERE user_id = ? ORDER BY id DESC LIMIT 100');
$stmt->execute([$user['id']]);
$docs = $stmt->fetchAll();
$maxBytes = document_max_bytes();

dashboard_page_start('My Documents', $user);
?>
<?php if (!encryption_ready()): ?>
<div class="form-note dash-notice">Document uploads aren&rsquo;t available on the dashboard yet. Please contact the office.</div>
<?php else: ?>
<p>Upload a safety form, a certification, or a payroll form like a W-4. Payroll forms go to HR and are
   deleted from the dashboard once HR has processed them; everything else goes to the safety team.
   A clear phone photo of a paper form is fine.</p>

<?php form_errors($errors); ?>

<form method="post" enctype="multipart/form-data" class="dash-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="upload">
    <div class="form-field">
        <label>What kind of document? *</label>
        <div class="checkbox-group">
            <?php foreach (DOCUMENT_TYPES as $key => $type): ?>
            <label class="checkbox-option"><input type="radio" name="type" value="<?= $key ?>" required<?= posted_text($_POST, 'type') === $key ? ' checked' : '' ?>> <?= htmlspecialchars($type['label']) ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-field">
            <label for="title">What is it? *</label>
            <input type="text" id="title" name="title" maxlength="150" required list="document-titles"
                   placeholder="e.g. W-4, Forklift certification" value="<?= $v('title') ?>">
            <datalist id="document-titles">
                <?php foreach (array_merge(['W-4', 'Iowa W-4', 'I-9'], $GLOBALS['APPLICATION_LICENSES']) as $suggestion): ?>
                <option value="<?= htmlspecialchars($suggestion) ?>">
                <?php endforeach; ?>
            </datalist>
        </div>
        <div class="form-field">
            <label for="expires_on">Expiration date (certifications only)</label>
            <input type="date" id="expires_on" name="expires_on" value="<?= $v('expires_on') ?>">
        </div>
    </div>
    <div class="form-field">
        <label for="file">File * <span class="dash-meta">(PDF or photo, up to <?= round($maxBytes / 1048576) ?>MB)</span></label>
        <input type="hidden" name="MAX_FILE_SIZE" value="<?= $maxBytes ?>">
        <input type="file" id="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.heic,application/pdf,image/*"
               data-max-bytes="<?= $maxBytes ?>" data-extensions="pdf,jpg,jpeg,png,heic,heif">
    </div>
    <div class="form-field">
        <label for="note">Note (optional)</label>
        <textarea id="note" name="note" maxlength="1000"><?= $v('note') ?></textarea>
    </div>
    <button type="submit" class="btn">Upload</button>
</form>
<?php endif; ?>

<h2 class="dash-subheading">Your uploads</h2>
<?php if ($docs): ?>
<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Uploaded</th><th>Type</th><th>Document</th><th>Status</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($docs as $doc): ?>
            <tr>
                <td><?= format_time($doc['created_at']) ?></td>
                <td><?= htmlspecialchars(document_type_label($doc['type'])) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?><?php if ($doc['expires_on']): ?><br><span class="dash-meta">Expires <?= format_date($doc['expires_on']) ?></span><?php endif; ?></td>
                <td><span class="badge status--<?= htmlspecialchars($doc['status']) ?>"><?= htmlspecialchars(document_status_label($doc['status'])) ?></span></td>
                <td class="dash-actions">
                    <?php if ($doc['status'] === 'received'): ?>
                    <form method="post" onsubmit="return confirm('Remove this document? The file will be deleted.');">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="remove">
                        <input type="hidden" name="document_id" value="<?= (int) $doc['id'] ?>">
                        <a href="<?= BASE_URL ?>/dashboard/document.php?id=<?= (int) $doc['id'] ?>" target="_blank" rel="noopener">Open</a>
                        <button type="submit" class="dash-link-button">Remove</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php else: ?>
<p class="dash-meta">You haven&rsquo;t uploaded anything yet.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
