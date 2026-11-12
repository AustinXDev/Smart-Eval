<?php

require_once __DIR__ . '/../init.php';

// database.php
$host = trim($_ENV['DB_HOST']) ?? '';
$port = trim($_ENV['DB_PORT'] ?? '3306');
$db   = trim($_ENV['DB_NAME']) ?? '';
$user = trim($_ENV['DB_USER']) ?? '';
$pass = trim($_ENV['DB_PASS']) ?? '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // throw exceptions
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Match the schema's table collation (utf8mb4_general_ci unless DB_COLLATION says
    // otherwise). Without this the connection uses the server default (production's is
    // utf8mb4_unicode_ci) and comparisons between bound parameters and query strings
    // fail with MySQL error 1267 "Illegal mix of collations".
    $collation = trim((string) ($_ENV['DB_COLLATION'] ?? '')) ?: 'utf8mb4_general_ci';
    if (!preg_match('/^utf8mb4_[a-z0-9_]+$/', $collation)) {
        throw new \PDOException("Invalid DB_COLLATION: {$collation}");
    }
    $pdo->exec("SET NAMES utf8mb4 COLLATE {$collation}");
    $pdo->exec("SET time_zone = '+08:00'");
} catch (\PDOException $e) {
    // Return JSON instead of HTML error
    error_log('[Database] ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status' => 'error','message' => 'Database connection unavailable.']);
    exit;
}
