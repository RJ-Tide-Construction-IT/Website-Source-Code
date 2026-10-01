<?php
// Sign-in sessions, roles, and form protection for the Employee Dashboard.

// Signed-in sessions end after this long, even if the browser stays open.
const DASHBOARD_SESSION_HOURS = 12;

function dashboard_start_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    // Keep session files in the site's own private folder rather than the
    // shared host's temp folder, where other sites on the server could see them.
    $dir = DASHBOARD_DATA_DIR . '/sessions';
    dashboard_ensure_dir($dir);
    session_save_path($dir);

    ini_set('session.use_strict_mode', '1');
    ini_set('session.gc_maxlifetime', (string) (DASHBOARD_SESSION_HOURS * 3600));
    session_name('rjt_dashboard');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => BASE_URL . '/dashboard/',
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// The signed-in user's row from the database, or null. Re-read on every page,
// so a role change or deactivation on the Users page takes effect immediately.
function current_user(): ?array {
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;

    $id = $_SESSION['user_id'] ?? null;
    if (!$id || time() - ($_SESSION['signed_in_at'] ?? 0) > DASHBOARD_SESSION_HOURS * 3600) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND active = 1');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: null;
    return $user;
}

// Sends the browser to another page and stops this one. Always use this rather
// than header('Location: ...') on its own: without the exit, the rest of the
// page would keep running (and could output things it shouldn't).
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

// A role's display name, e.g. 'office' => 'Office'.
function role_label(string $role): string {
    return $GLOBALS['DASHBOARD_ROLES'][$role]['label'] ?? ucfirst($role);
}

function user_can(?array $user, string $capability): bool {
    if (!$user) {
        return false;
    }
    return in_array($capability, $GLOBALS['DASHBOARD_ROLES'][$user['role']]['can'] ?? [], true);
}

function is_permanent_admin(string $email): bool {
    return in_array(strtolower($email), array_map('strtolower', $GLOBALS['DASHBOARD_ADMIN_EMAILS']), true);
}

// Called after Microsoft confirms who someone is. Creates their dashboard
// account on first sign-in (as an Employee, or Admin for the emails listed in
// config.php) and keeps their name/email in sync with Microsoft 365.
// Returns false if an Admin has deactivated the account.
function sign_in_user(string $oid, string $email, string $name): bool {
    $pdo  = db();
    $stmt = $pdo->prepare('SELECT * FROM users WHERE ms_oid = ?');
    $stmt->execute([$oid]);
    $user = $stmt->fetch();

    if (!$user) {
        $role = is_permanent_admin($email) ? 'admin' : 'employee';
        $pdo->prepare('INSERT INTO users (ms_oid, email, name, role, active, created_at, last_login_at) VALUES (?, ?, ?, ?, 1, ?, ?)')
            ->execute([$oid, $email, $name, $role, db_now(), db_now()]);
        $userId = (int) $pdo->lastInsertId();
        audit($userId, 'account_created', "$name ($email) signed in for the first time as " . role_label($role));
    } else {
        if (!$user['active']) {
            audit((int) $user['id'], 'sign_in_blocked', "$name ($email) tried to sign in but their account is deactivated");
            return false;
        }
        $userId = (int) $user['id'];
        $role = is_permanent_admin($email) ? 'admin' : $user['role'];
        $pdo->prepare('UPDATE users SET email = ?, name = ?, role = ?, last_login_at = ? WHERE id = ?')
            ->execute([$email, $name, $role, db_now(), $userId]);
    }

    session_regenerate_id(true); // new session ID at sign-in, so an old one can't be reused
    $_SESSION['user_id'] = $userId;
    $_SESSION['signed_in_at'] = time();
    return true;
}

function sign_out(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => $p['path'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']);
    }
    session_destroy();
}

// Only dashboard pages are allowed as the "go here after signing in" target,
// so a crafted link can't bounce someone off to another site after sign-in.
function safe_next(?string $next): string {
    $home = BASE_URL . '/dashboard/';
    if (!is_string($next) || !str_starts_with($next, $home) || str_contains($next, '//') || str_contains($next, '\\')) {
        return $home;
    }
    return $next;
}

// Top of every signed-in page. Sends visitors who aren't signed in to the
// sign-in page, then back here afterwards.
function require_login(): array {
    header('Cache-Control: no-store'); // never keep private pages in browser/proxy caches
    $user = current_user();
    if (!$user) {
        redirect(BASE_URL . '/dashboard/login.php?next=' . urlencode($_SERVER['REQUEST_URI'] ?? ''));
    }
    return $user;
}

// Like require_login(), but also needs a specific permission from the role list
// in config.php, e.g. require_capability('manage_jobs').
function require_capability(string $capability): array {
    $user = require_login();
    if (!user_can($user, $capability)) {
        http_response_code(403);
        dashboard_page_start('Not Allowed', $user);
        echo '<p>Your account doesn&rsquo;t have access to this page. If you need it, ask an Admin to change your role.</p>';
        echo '<p><a class="back-link" href="' . BASE_URL . '/dashboard/">&larr; Back to the dashboard</a></p>';
        dashboard_page_end();
        exit;
    }
    return $user;
}

// ---------- Form protection ----------
// Every dashboard form includes csrf_field(), and every POST handler calls
// verify_csrf(), so another website can't submit forms using an employee's
// signed-in session.

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verify_csrf(): void {
    if (!is_string($_POST['csrf'] ?? null) || !hash_equals(csrf_token(), $_POST['csrf'])) {
        http_response_code(400);
        exit('This form expired. Go back, refresh the page, and try again.');
    }
}

// ---------- One-time messages shown after a redirect ----------

function flash(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function take_flashes(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}
