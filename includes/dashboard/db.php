<?php
// Database connection for the Employee Dashboard.
//
// By default everything is stored in one SQLite file,
// uploads/dashboard/dashboard.sqlite, which needs no setup. To use a MySQL
// database from the hosting control panel instead, set DASHBOARD_DB_DSN,
// DASHBOARD_DB_USER and DASHBOARD_DB_PASS in includes/secrets.php (see
// includes/secrets.example.php).
//
// Tables are created automatically. To add or change tables later, append a
// new numbered step to db_migrations() below, never edit a step that has
// already run on the live site.

function db(): PDO {
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    if (defined('DASHBOARD_DB_DSN') && DASHBOARD_DB_DSN !== '') {
        $pdo = new PDO(DASHBOARD_DB_DSN, DASHBOARD_DB_USER, DASHBOARD_DB_PASS, $options);
    } else {
        dashboard_ensure_dir(DASHBOARD_DATA_DIR);
        $pdo = new PDO('sqlite:' . DASHBOARD_DATA_DIR . '/dashboard.sqlite', null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }

    db_migrate($pdo);
    return $pdo;
}

// Current time in UTC, the format every dashboard timestamp is stored in.
function db_now(): string {
    return gmdate('Y-m-d H:i:s');
}

function db_migrations(bool $mysql): array {
    $id     = $mysql ? 'INT AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT';
    $engine = $mysql ? ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4' : '';

    return [
        1 => [
            "CREATE TABLE users (
                id            $id,
                ms_oid        VARCHAR(64)  NOT NULL UNIQUE,
                email         VARCHAR(255) NOT NULL,
                name          VARCHAR(255) NOT NULL,
                role          VARCHAR(20)  NOT NULL DEFAULT 'employee',
                active        SMALLINT     NOT NULL DEFAULT 1,
                created_at    VARCHAR(19)  NOT NULL,
                last_login_at VARCHAR(19)  NULL
            )$engine",
            // Who changed what, e.g. role changes and job openings.
            "CREATE TABLE audit_log (
                id         $id,
                user_id    INT          NULL,
                action     VARCHAR(64)  NOT NULL,
                details    TEXT         NOT NULL,
                created_at VARCHAR(19)  NOT NULL
            )$engine",
        ],
    ];
}

function db_migrate(PDO $pdo): void {
    $mysql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql';
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_migrations (version INT PRIMARY KEY, applied_at VARCHAR(19) NOT NULL)');
    $done = $pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN);

    foreach (db_migrations($mysql) as $version => $statements) {
        if (in_array($version, array_map('intval', $done), true)) {
            continue;
        }
        foreach ($statements as $sql) {
            $pdo->exec($sql);
        }
        $pdo->prepare('INSERT INTO schema_migrations (version, applied_at) VALUES (?, ?)')->execute([$version, db_now()]);
    }
}

function dashboard_ensure_dir(string $dir): void {
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
        throw new RuntimeException("Could not create $dir, check the folder's write permissions on the server");
    }
}

// Records who did what, shown to Admins on the Users page.
function audit(?int $userId, string $action, string $details): void {
    db()->prepare('INSERT INTO audit_log (user_id, action, details, created_at) VALUES (?, ?, ?, ?)')
        ->execute([$userId, $action, $details, db_now()]);
}
