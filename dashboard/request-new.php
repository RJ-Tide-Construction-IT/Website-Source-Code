<?php
// New request form: request-new.php?type=time_off | address | direct_deposit.
// Checking and saving are in includes/dashboard/requests.php.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

$type = is_string($_GET['type'] ?? null) ? $_GET['type'] : '';
if (!isset(REQUEST_TYPES[$type])) {
    redirect(BASE_URL . '/dashboard/requests.php');
}

$errors = [];
$values = [];
if (form_submitted()) {
    [$details, $sensitive, $errors] = validate_request($type, $_POST);
    if (!$errors) {
        create_request($user, $type, $details, $sensitive);
        flash('success', 'Your ' . request_type_label($type) . ' request was sent to HR. You\'ll get an email once it\'s reviewed.');
        redirect(BASE_URL . '/dashboard/requests.php');
    }
    $values = $details; // to refill the form; never includes bank numbers
}
$v = fn(string $key) => htmlspecialchars((string) ($values[$key] ?? ''));

$titles = ['time_off' => 'Request Time Off', 'address' => 'Change My Address', 'direct_deposit' => 'Change My Direct Deposit'];
dashboard_page_start($titles[$type], $user);
?>
<p><a class="back-link" href="<?= BASE_URL ?>/dashboard/requests.php">&larr; My requests</a></p>

<?php if ($errors): ?>
<div class="form-note form-note--error">
    <?php foreach ($errors as $error): ?><div><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($type === 'direct_deposit' && !encryption_ready()): ?>
<div class="form-note dash-notice">Direct deposit changes aren&rsquo;t available on the dashboard yet. Please contact the office.</div>
<?php else: ?>
<form method="post" class="dash-form" autocomplete="<?= $type === 'direct_deposit' ? 'off' : 'on' ?>">
    <?= csrf_field() ?>

    <?php if ($type === 'time_off'): ?>
    <div class="form-field">
        <label>Type of time off *</label>
        <div class="checkbox-group">
            <?php foreach (TIME_OFF_KINDS as $key => $label): ?>
            <label class="checkbox-option"><input type="radio" name="kind" value="<?= $key ?>"<?= ($values['kind'] ?? '') === $key ? ' checked' : '' ?> required> <?= htmlspecialchars($label) ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="form-row">
        <div class="form-field"><label for="start">First day off *</label><input type="date" id="start" name="start" value="<?= $v('start') ?>" required></div>
        <div class="form-field"><label for="end">Last day off *</label><input type="date" id="end" name="end" value="<?= $v('end') ?>" required></div>
        <div class="form-field"><label for="hours">Hours (partial days only)</label><input type="number" id="hours" name="hours" min="0.5" max="24" step="0.5" value="<?= $v('hours') ?>" placeholder="Leave blank for full days"></div>
    </div>
    <div class="form-field">
        <label for="note">Note (required for &ldquo;Other&rdquo;)</label>
        <textarea id="note" name="note" maxlength="1000"><?= $v('note') ?></textarea>
    </div>

    <?php elseif ($type === 'address'): ?>
    <div class="form-field"><label for="street">Street address *</label><input type="text" id="street" name="street" maxlength="150" value="<?= $v('street') ?>" autocomplete="address-line1" required></div>
    <div class="form-field"><label for="street2">Apartment, unit, PO box</label><input type="text" id="street2" name="street2" maxlength="100" value="<?= $v('street2') ?>" autocomplete="address-line2"></div>
    <div class="form-row">
        <div class="form-field"><label for="city">City *</label><input type="text" id="city" name="city" maxlength="100" value="<?= $v('city') ?>" autocomplete="address-level2" required></div>
        <div class="form-field"><label for="state">State *</label><input type="text" id="state" name="state" maxlength="30" value="<?= $v('state') ?>" autocomplete="address-level1" required></div>
        <div class="form-field"><label for="zip">ZIP *</label><input type="text" id="zip" name="zip" maxlength="10" inputmode="numeric" value="<?= $v('zip') ?>" autocomplete="postal-code" required></div>
    </div>
    <div class="form-row">
        <div class="form-field"><label for="phone">Phone (if it&rsquo;s changing too)</label><input type="tel" id="phone" name="phone" maxlength="30" value="<?= $v('phone') ?>" autocomplete="tel"></div>
        <div class="form-field"><label for="effective">New address starting *</label><input type="date" id="effective" name="effective" value="<?= $v('effective') ?>" required></div>
    </div>
    <div class="form-field"><label for="note">Note</label><textarea id="note" name="note" maxlength="1000"><?= $v('note') ?></textarea></div>

    <?php else: /* direct_deposit */ ?>
    <div class="dash-help">Your bank details are encrypted, only HR can see them, and they&rsquo;re permanently
        erased once the change has been entered into payroll. They&rsquo;re never sent by email.
        You&rsquo;ll find both numbers at the bottom of a check or in your bank&rsquo;s app.</div>
    <div class="form-field"><label for="bank_name">Bank name *</label><input type="text" id="bank_name" name="bank_name" maxlength="100" value="<?= $v('bank_name') ?>" required></div>
    <div class="form-field"><label for="routing">Routing number (9 digits) *</label><input type="text" id="routing" name="routing" inputmode="numeric" maxlength="11" autocomplete="off" required></div>
    <div class="form-row">
        <div class="form-field"><label for="account">Account number *</label><input type="password" id="account" name="account" inputmode="numeric" maxlength="20" autocomplete="off" required></div>
        <div class="form-field"><label for="account_confirm">Account number again *</label><input type="password" id="account_confirm" name="account_confirm" inputmode="numeric" maxlength="20" autocomplete="off" required></div>
    </div>
    <div class="form-field">
        <label>Account type *</label>
        <div class="checkbox-group">
            <label class="checkbox-option"><input type="radio" name="account_type" value="checking"<?= ($values['account_type'] ?? '') === 'checking' ? ' checked' : '' ?> required> Checking</label>
            <label class="checkbox-option"><input type="radio" name="account_type" value="savings"<?= ($values['account_type'] ?? '') === 'savings' ? ' checked' : '' ?>> Savings</label>
        </div>
    </div>
    <div class="form-field"><label for="note">Note (e.g. if you want to split your pay between accounts)</label><textarea id="note" name="note" maxlength="1000"><?= $v('note') ?></textarea></div>
    <div class="form-field">
        <label class="checkbox-option"><input type="checkbox" name="authorize" value="1" required>
            I authorize RJ Tide Construction to deposit my pay into this account, replacing my current direct deposit details.</label>
    </div>
    <?php endif; ?>

    <button type="submit" class="btn">Send to HR</button>
</form>
<?php endif; ?>
<?php dashboard_page_end(); ?>
