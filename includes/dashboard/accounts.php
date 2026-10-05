<?php
// Dashboard accounts: creating someone's account the first time they sign in,
// the approval rule for Google sign-ins, who is always an Admin, and the email
// to Admins when someone is waiting. Sessions, roles and page protection are in
// auth.php.

// How each sign-in method is shown on the Users page.
function sign_in_method_label(string $method): string {
    return ['microsoft' => 'Microsoft', 'google' => 'Google', 'test' => 'Local test'][$method] ?? ucfirst($method);
}

// Whether this account is always an Admin (DASHBOARD_ADMIN_EMAILS in config.php).
// Only company (Microsoft 365) accounts qualify: anyone can make a Google
// account that uses an @rjtide.com address, so a Google sign-in never gets
// Admin this way. (The local test sign-in counts too, so you can test as Admin.)
function is_permanent_admin(string $method, string $email): bool {
    return in_array($method, ['microsoft', 'test'], true)
        && in_array(strtolower($email), array_map('strtolower', $GLOBALS['DASHBOARD_ADMIN_EMAILS']), true);
}

// New accounts from these sign-in methods need an Admin's approval before they
// can do anything. Microsoft accounts are already RJ Tide employees by
// definition; a Google account could be anyone's.
function needs_approval(string $method): bool {
    return $method === 'google';
}

/**
 * Called after the sign-in service confirms who someone is. Creates their
 * dashboard account the first time (as an Employee, or Admin for the emails
 * in config.php) and keeps their name/email up to date.
 * @return string 'signed_in'; 'off' if an Admin turned their access off;
 *                'awaiting_approval' if a Google sign-in hasn't been approved
 *                yet ('new_awaiting_approval' the very first time).
 */
function sign_in_user(string $method, string $accountId, string $email, string $name): string {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE account_id = ?');
    $stmt->execute([$accountId]);
    $user = $stmt->fetch();
    $via  = sign_in_method_label($method);

    if (!$user) {
        $role    = is_permanent_admin($method, $email) ? 'admin' : 'employee';
        $waiting = needs_approval($method) ? 1 : 0;
        $pdo->prepare('INSERT INTO users (account_id, sign_in_method, email, name, role, active, awaiting_approval, created_at, last_login_at) VALUES (?, ?, ?, ?, ?, 1, ?, ?, ?)')
            ->execute([$accountId, $method, $email, $name, $role, $waiting, db_now(), db_now()]);
        $userId = (int) $pdo->lastInsertId();
        if ($waiting) {
            audit($userId, 'awaiting_approval', "$name ($email) signed in with $via for the first time and is waiting for approval");
            return 'new_awaiting_approval';
        }
        audit($userId, 'account_created', "$name ($email) signed in with $via for the first time as " . role_label($role));
    } else {
        $userId = (int) $user['id'];
        if (!$user['active']) {
            audit($userId, 'sign_in_blocked', "$name ($email) tried to sign in but their access is turned off");
            return 'off';
        }
        $role = is_permanent_admin($method, $email) ? 'admin' : $user['role'];
        $pdo->prepare('UPDATE users SET email = ?, name = ?, role = ?, last_login_at = ? WHERE id = ?')
            ->execute([$email, $name, $role, db_now(), $userId]);
        if ($user['awaiting_approval']) {
            return 'awaiting_approval';
        }
    }

    session_regenerate_id(true); // new session ID at sign-in, so an old one can't be reused
    $_SESSION['user_id'] = $userId;
    $_SESSION['signed_in_at'] = time();
    return 'signed_in';
}

// Emails the Admins that someone new is waiting for approval. Best effort:
// if email isn't set up or fails, the person still shows on the Users page.
function notify_admins_of_new_sign_in(string $name, string $email): void {
    if (dashboard_is_local()) {
        // Never send real email while testing on your own computer.
        error_log("Employee Dashboard (local): would email Admins that $name ($email) is waiting for approval");
        return;
    }
    if (!is_file(dirname(__DIR__) . '/secrets.php')) {
        return;
    }
    require_once dirname(__DIR__) . '/mailer.php';
    $admins = db()->query("SELECT email FROM users WHERE role = 'admin' AND active = 1 AND awaiting_approval = 0 AND sign_in_method = 'microsoft'")->fetchAll(PDO::FETCH_COLUMN);
    $admins = array_unique(array_merge($admins, $GLOBALS['DASHBOARD_ADMIN_EMAILS']));
    $body = "$name ($email) signed in to the Employee Dashboard with Google for the first time.\n\n"
          . "They can't see anything until an Admin approves them. To approve or decline, go to:\n"
          . SITE_URL . BASE_URL . "/dashboard/users.php\n";
    foreach ($admins as $admin) {
        send_email($admin, 'Employee Dashboard: ' . header_safe($name) . ' is waiting for approval', $body);
    }
}
