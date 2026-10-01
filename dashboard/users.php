<?php
// Users: everyone who has signed in to the dashboard. Admins can change a
// person's role (what they can see) or turn off their access. People appear
// here automatically the first time they sign in with Microsoft.
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_capability('manage_users');
$roles = $GLOBALS['DASHBOARD_ROLES'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([(int) ($_POST['user_id'] ?? 0)]);
    $target = $stmt->fetch();
    $role   = posted_text($_POST, 'role');
    $active = !empty($_POST['active']) ? 1 : 0;

    if (!$target) {
        flash('error', 'That person wasn\'t found.');
    } elseif ((int) $target['id'] === (int) $user['id']) {
        flash('error', 'You can\'t change your own role or access, ask another Admin.');
    } elseif (is_permanent_admin($target['email'])) {
        flash('error', $target['name'] . ' is always an Admin (set in includes/config.php).');
    } elseif (!isset($roles[$role])) {
        flash('error', 'Unknown role.');
    } else {
        db()->prepare('UPDATE users SET role = ?, active = ? WHERE id = ?')->execute([$role, $active, $target['id']]);

        $changes = [];
        if ($role !== $target['role']) {
            $changes[] = 'role ' . role_label($target['role']) . ' to ' . role_label($role);
        }
        if ($active !== (int) $target['active']) {
            $changes[] = $active ? 'turned access back on' : 'turned access off';
        }
        if ($changes) {
            audit((int) $user['id'], 'user_updated', $user['name'] . ' changed ' . $target['name'] . ': ' . implode(', ', $changes));
            flash('success', 'Saved changes for ' . $target['name'] . '.');
        } else {
            flash('success', 'No changes for ' . $target['name'] . '.');
        }
    }
    redirect(BASE_URL . '/dashboard/users.php');
}

$people   = db()->query('SELECT * FROM users ORDER BY active DESC, name')->fetchAll();
$activity = db()->query('SELECT created_at, details FROM audit_log ORDER BY id DESC LIMIT 25')->fetchAll();

dashboard_page_start('Users', $user);
?>
<p>Everyone appears here the first time they sign in. New people start as <strong>Employees</strong>.
   Change someone&rsquo;s role to give them more access, or uncheck <strong>Access</strong> for anyone
   who has left the company (disabling their Microsoft 365 account also blocks them).</p>

<ul class="dash-role-list">
    <?php foreach ($roles as $key => $info): ?>
    <li><strong><?= htmlspecialchars($info['label']) ?></strong>: <?= $key === 'employee' ? 'employee features only' : htmlspecialchars(implode(', ', array_map(fn($c) => str_replace('_', ' ', $c), $info['can']))) ?></li>
    <?php endforeach; ?>
</ul>

<div class="dash-table-wrap">
    <table class="dash-table">
        <thead><tr><th>Name</th><th>Role</th><th>Access</th><th>Last sign-in</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($people as $person):
            $formId = 'user' . $person['id'];
            $locked = (int) $person['id'] === (int) $user['id'] || is_permanent_admin($person['email']); ?>
            <tr class="<?= $person['active'] ? '' : 'dash-row--inactive' ?>">
                <td><?= htmlspecialchars($person['name']) ?><br><span class="dash-meta"><?= htmlspecialchars($person['email']) ?></span></td>
                <?php if ($locked): ?>
                <td><?= htmlspecialchars(role_label($person['role'])) ?></td>
                <td><?= $person['active'] ? 'On' : 'Off' ?></td>
                <td><?= format_time($person['last_login_at']) ?></td>
                <td class="dash-meta"><?= (int) $person['id'] === (int) $user['id'] ? 'You' : 'Always Admin' ?></td>
                <?php else: ?>
                <td>
                    <select name="role" form="<?= $formId ?>" aria-label="Role for <?= htmlspecialchars($person['name']) ?>">
                        <?php foreach ($roles as $key => $info): ?>
                        <option value="<?= $key ?>"<?= $person['role'] === $key ? ' selected' : '' ?>><?= htmlspecialchars($info['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td><label><input type="checkbox" name="active" value="1" form="<?= $formId ?>"<?= $person['active'] ? ' checked' : '' ?>> On</label></td>
                <td><?= format_time($person['last_login_at']) ?></td>
                <td>
                    <form method="post" id="<?= $formId ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $person['id'] ?>">
                        <button type="submit" class="btn btn--sm">Save</button>
                    </form>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h2 class="dash-subheading">Recent activity</h2>
<?php if ($activity): ?>
<ul class="dash-activity">
    <?php foreach ($activity as $entry): ?>
    <li><span class="dash-meta"><?= format_time($entry['created_at']) ?></span> <?= htmlspecialchars($entry['details']) ?></li>
    <?php endforeach; ?>
</ul>
<?php else: ?>
<p class="dash-meta">Nothing yet.</p>
<?php endif; ?>
<?php dashboard_page_end(); ?>
