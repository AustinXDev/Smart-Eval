<?php

date_default_timezone_set('Asia/Manila');

//avoid writing long paths
define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

if (($_ENV['APP_ENV'] ?? 'production') === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
}
if (PHP_SAPI !== 'cli') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: same-origin');
}

define('BASE_URL', rtrim($_ENV['APP_URL'], '/') . '/');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    new \App\Session\SessionManager();
}

require_once __DIR__ . '/config/database.php';
