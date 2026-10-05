<?php
// Sign-in sessions, roles, page protection, and form protection for the
// Employee Dashboard. Creating accounts and approvals are in accounts.php.

// Signed-in sessions end after this long, even if the browser stays open.
const DASHBOARD_SESSION_HOURS = 12;

// True when running on PHP's built-in preview server (php -S) on your own
// computer, false on the live site.
function dashboard_is_local(): bool {
    return PHP_SAPI === 'cli-server';
}

// The local-only test sign-in (dashboard/dev-login.php) and the pretend sign-in pages work only when ALL of
// these are true: DASHBOARD_DEV_LOGIN is set in secrets.php, the site is running
// on PHP's built-in preview server (php -S), and the request comes from this
// same computer. The file is also never uploaded by the deploy.
function dashboard_dev_login_allowed(): bool {
    return defined('DASHBOARD_DEV_LOGIN') && DASHBOARD_DEV_LOGIN === true
        && PHP_SAPI === 'cli-server'
        && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}

// On the live site, the dashboard only runs at SITE_URL's address, so sign-in
// sessions and the sign-in services' return address always match. Visitors who arrive
// at another address (e.g. www.rjtide.com) are sent there first.
function dashboard_require_main_address(): void {
    if (dashboard_is_local()) {
        return;
    }
    $mainHost = parse_url(SITE_URL, PHP_URL_HOST);
    if (strcasecmp($_SERVER['HTTP_HOST'] ?? '', $mainHost) !== 0) {
        redirect(SITE_URL . ($_SERVER['REQUEST_URI'] ?? BASE_URL . '/dashboard/'));
    }
}

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
        // Always HTTPS-only on the live site; locally php -S is plain http.
        'secure'   => !dashboard_is_local() || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
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
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND active = 1 AND awaiting_approval = 0');
    $stmt->execute([$id]);
    $user = $stmt->fetch() ?: null;
    return $user;
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
// Every dashboard form includes csrf_field(), and every page checks for a
// submission with form_submitted() (which checks the token), so another
// website can't submit forms using an employee's signed-in session.

// True if this request is a form submission, after checking its form token
// (a missing or wrong token stops the page). Always use this, rather than
// checking $_SERVER['REQUEST_METHOD'] directly, so the token check can't be
// forgotten:   if (form_submitted()) { ...handle the form... }
function form_submitted(): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return false;
    }
    verify_csrf();
    return true;
}

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
