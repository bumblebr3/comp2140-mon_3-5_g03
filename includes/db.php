<?php
/* Loads local application settings and creates a reusable PDO connection to the MySQL database. */

// Read private settings from the local configuration file, not from public source code.
function app_config(): array
{
    $path = __DIR__ . '/../config/local.php';
    if (!file_exists($path)) {
        throw new RuntimeException('Copy config/local.example.php to config/local.php and update the database settings.');
    }
    return require $path;
}

// Reuse one PDO connection during a request to avoid opening duplicate connections.
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $config = app_config();
    $dsn = 'mysql:host=' . $config['db_host'] . ';port=' . $config['db_port'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4';
    $pdo = new PDO($dsn, $config['db_user'], $config['db_password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}
