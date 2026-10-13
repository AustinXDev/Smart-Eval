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
} catch (\PDOException $e) {
    // Return JSON instead of HTML error
    error_log('[Database] ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['status'=>'error','message'=>'Database connection unavailable.']);
    exit;
}
?>