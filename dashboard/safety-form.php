<?php
// Safety Forms: pick a form (safety-form.php), fill it out
// (safety-form.php?form=incident), and it's emailed as a PDF to the safety
// contact. The forms themselves are defined in includes/dashboard/safety-forms.php.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

$key  = is_string($_GET['form'] ?? null) ? $_GET['form'] : '';
$form = SAFETY_FORMS[$key] ?? null;

$errors  = [];
$answers = [];
if ($form && form_submitted()) {
    [$answers, $errors] = validate_safety_form($form, $_POST);
    if (!$errors) {
        $sent = submit_safety_form($user, $key, $answers);
        flash($sent ? 'success' : 'error', $sent
            ? 'Your ' . $form['name'] . ' was sent. Thank you for reporting it.'
            : 'Your ' . $form['name'] . ' was saved, but the email didn\'t go through. Please let the office know.');
        redirect(BASE_URL . '/dashboard/safety-form.php');
    }
}
$a = fn(string $k) => htmlspecialchars($answers[$k] ?? '');

dashboard_page_start($form ? $form['name'] : 'Safety Forms', $user);
?>
<?php if (!$form): ?>
<p>Fill out a safety form here and it&rsquo;s sent straight to the safety team as a PDF. You&rsquo;ll find a
   copy under <a href="<?= BASE_URL ?>/dashboard/documents.php">My Documents</a>.</p>
<div class="dash-tiles">
    <?php foreach (SAFETY_FORMS as $formKey => $f): ?>
    <a class="dash-tile" href="<?= BASE_URL ?>/dashboard/safety-form.php?form=<?= urlencode($formKey) ?>">
        <h3><?= htmlspecialchars($f['name']) ?></h3>
        <p><?= htmlspecialchars($f['description']) ?></p>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<p><a class="back-link" href="<?= BASE_URL ?>/dashboard/safety-form.php">&larr; All safety forms</a></p>
<p><?= htmlspecialchars($form['description']) ?></p>

<?php if ($errors): ?>
<div class="form-note form-note--error">
    <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<form method="post" class="dash-form">
    <?= csrf_field() ?>
    <?php foreach ($form['fields'] as $name => $field):
        [$label, $type, $required] = $field;
        $req = $required ? ' required' : ''; ?>
    <div class="form-field">
        <?php if ($type === 'yesno'): ?>
        <label><?= htmlspecialchars($label) ?><?= $required ? ' *' : '' ?></label>
        <div class="checkbox-group">
            <?php foreach (['Yes', 'No'] as $choice): ?>
            <label class="checkbox-option"><input type="radio" name="<?= $name ?>" value="<?= $choice ?>"<?= $req ?><?= ($answers[$name] ?? '') === $choice ? ' checked' : '' ?>> <?= $choice ?></label>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <label for="<?= $name ?>"><?= htmlspecialchars($label) ?><?= $required ? ' *' : '' ?></label>
        <?php if ($type === 'select'): ?>
        <select id="<?= $name ?>" name="<?= $name ?>"<?= $req ?>>
            <option value="">Choose&hellip;</option>
            <?php foreach ($field[3] as $option): ?>
            <option<?= ($answers[$name] ?? '') === $option ? ' selected' : '' ?>><?= htmlspecialchars($option) ?></option>
            <?php endforeach; ?>
        </select>
        <?php elseif ($type === 'textarea' || $type === 'names'): ?>
        <textarea id="<?= $name ?>" name="<?= $name ?>"<?= $req ?>><?= $a($name) ?></textarea>
        <?php else: ?>
        <input type="<?= $type ?>" id="<?= $name ?>" name="<?= $name ?>" value="<?= $a($name) ?>"<?= $req ?><?= $type === 'text' ? ' maxlength="200"' : '' ?>>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <div class="form-field">
        <label class="checkbox-option checkbox-option--strong">
            <input type="checkbox" name="confirm" value="1" required>
            I confirm this information is accurate to the best of my knowledge. *
        </label>
    </div>
    <button type="submit" class="btn">Send <?= htmlspecialchars($form['name']) ?></button>
</form>
<?php endif; ?>
<?php dashboard_page_end(); ?>
