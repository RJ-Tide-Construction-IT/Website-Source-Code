<?php
require_once dirname(__DIR__) . '/includes/dashboard/bootstrap.php';
$user = require_login();

// Tiles come from the page list in includes/dashboard/layout.php (dashboard_pages()),
// filtered to what this person's role can see.
$tiles = array_filter(dashboard_pages_for($user), fn($page) => isset($page['text']));

dashboard_page_start('Employee Dashboard', $user);
?>
<p class="dash-welcome">Welcome, <?= htmlspecialchars(strtok($user['name'], ' ') ?: $user['name']) ?>.</p>

<?php if (user_can($user, 'manage_users')):
    $waitingCount = (int) db()->query('SELECT COUNT(*) FROM users WHERE awaiting_approval = 1')->fetchColumn();
    if ($waitingCount): ?>
<div class="form-note dash-notice">
    <?= $waitingCount === 1 ? '1 person is' : $waitingCount . ' people are' ?> waiting for approval.
    <a href="<?= BASE_URL ?>/dashboard/users.php">Review on the Users page</a>
</div>
<?php endif; endif; ?>

<div class="dash-tiles">
    <?php foreach ($tiles as $tile): ?>
    <?php if ($tile['href'] !== null): ?>
    <a class="dash-tile" href="<?= BASE_URL . $tile['href'] ?>">
        <h3><?= htmlspecialchars($tile['label']) ?></h3>
        <p><?= htmlspecialchars($tile['text']) ?></p>
    </a>
    <?php else: ?>
    <div class="dash-tile dash-tile--soon">
        <h3><?= htmlspecialchars($tile['label']) ?> <span class="badge">Coming soon</span></h3>
        <p><?= htmlspecialchars($tile['text']) ?></p>
    </div>
    <?php endif; ?>
    <?php endforeach; ?>
</div>
<?php dashboard_page_end(); ?>
