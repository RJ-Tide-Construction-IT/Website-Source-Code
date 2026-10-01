<?php
// Shared page frame for the Employee Dashboard: the normal site header/footer
// (marked private, see $privatePage in includes/header.php), a title banner,
// the dashboard tabs, and any one-time messages.

// Every dashboard page, in one list that drives both the tabs and the home page
// tiles, so the two always agree on who can see what.
//   'can'  permission needed (from DASHBOARD_ROLES in config.php), null = everyone signed in
//   'href' leave null for a page that isn't built yet: it shows as a
//          "Coming soon" tile on the home page and gets no tab
//   'text' the home page tile's description (no 'text' = no tile, e.g. Home itself)
//   'also' (optional) other pages that belong to this tab, so it stays highlighted there
function dashboard_pages(): array {
    return [
        ['label' => 'Home',         'href' => '/dashboard/',          'can' => null],
        ['label' => 'Documents',    'href' => null,                   'can' => null,
         'text'  => 'Upload certifications, safety forms, receipts, and job-site photos.'],
        ['label' => 'Job Openings', 'href' => '/dashboard/jobs.php',  'can' => 'manage_jobs',
         'text'  => 'Choose which positions are listed on the Careers page.'],
        ['label' => 'Job Postings', 'href' => '/dashboard/postings.php', 'can' => 'edit_postings',
         'text'  => 'Add new job postings, or edit the ones added here.',
         'also'  => ['/dashboard/posting-edit.php']],
        ['label' => 'Users',        'href' => '/dashboard/users.php', 'can' => 'manage_users',
         'text'  => 'Give employees more access, or turn off access for people who have left.'],
    ];
}

// The pages this person's role lets them see.
function dashboard_pages_for(array $user): array {
    return array_filter(dashboard_pages(), fn($page) => $page['can'] === null || user_can($user, $page['can']));
}

function dashboard_page_start(string $title, ?array $user): void {
    $pageTitle   = "$title, Employee Dashboard";
    $privatePage = true;
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/header.php';
    page_hero($title);

    echo '<section class="dashboard"><div class="container">';

    if ($user) {
        $current = strtok($_SERVER['REQUEST_URI'] ?? '', '?');
        echo '<div class="dash-bar"><nav class="dash-tabs" aria-label="Dashboard">';
        foreach (dashboard_pages_for($user) as $page) {
            if ($page['href'] === null) {
                continue; // not built yet
            }
            $url = BASE_URL . $page['href'];
            $active = $current === $url
                || ($page['href'] === '/dashboard/' && $current === BASE_URL . '/dashboard/index.php')
                || in_array($current, array_map(fn($p) => BASE_URL . $p, $page['also'] ?? []), true);
            echo '<a href="' . htmlspecialchars($url) . '"' . ($active ? ' class="is-active" aria-current="page"' : '') . '>' . htmlspecialchars($page['label']) . '</a>';
        }
        echo '</nav><div class="dash-user">'
           . '<span>' . htmlspecialchars($user['name']) . ' &middot; ' . htmlspecialchars(role_label($user['role'])) . '</span>'
           . '<form method="post" action="' . BASE_URL . '/dashboard/logout.php">' . csrf_field()
           . '<button type="submit" class="btn btn--sm btn--outline-dark">Sign out</button></form>'
           . '</div></div>';
    }

    foreach (take_flashes() as $flash) {
        $class = $flash['type'] === 'error' ? 'form-note--error' : 'form-note--success';
        echo '<div class="form-note ' . $class . '">' . htmlspecialchars($flash['message']) . '</div>';
    }
}

function dashboard_page_end(): void {
    echo '</div></section>';
    require $_SERVER['DOCUMENT_ROOT'] . '/includes/footer.php';
}

// Stored timestamps are UTC, shown in the office's time zone.
function format_time(?string $utc): string {
    if (!$utc) {
        return 'Never';
    }
    $time = new DateTime($utc, new DateTimeZone('UTC'));
    return $time->setTimezone(new DateTimeZone('America/Chicago'))->format('M j, Y g:i A');
}

// The local-only test sign-in (dashboard/dev-login.php) works only when ALL of
// these are true: DASHBOARD_DEV_LOGIN is set in secrets.php, the site is running
// on PHP's built-in preview server (php -S), and the request comes from this
// same computer. The file is also never uploaded by the deploy.
function dashboard_dev_login_allowed(): bool {
    return defined('DASHBOARD_DEV_LOGIN') && DASHBOARD_DEV_LOGIN === true
        && PHP_SAPI === 'cli-server'
        && in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
}
