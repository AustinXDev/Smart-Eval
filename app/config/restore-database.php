<?php

declare(strict_types=1);

require_once __DIR__ . '/../init.php';

// Archive database connection
$host = trim($_ENV['DB_HOST'] ?? '');
$port = trim($_ENV['DB_PORT'] ?? '3306');
$db   = trim($_ENV['RESTORE_DB_NAME'] ?? '');
$user = trim($_ENV['DB_USER'] ?? '');
$pass = $_ENV['DB_PASS'] ?? '';

$charset = 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {

    $restorePdo = new PDO(
        $dsn,
        $user,
        $pass,
        $options
    );

} catch (PDOException $e) {

    error_log('[Archive Database] ' . $e->getMessage());

    http_response_code(503);

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'status' => 'error',
        'message' => 'Archive database connection unavailable.'
    ]);

    exit;
}
