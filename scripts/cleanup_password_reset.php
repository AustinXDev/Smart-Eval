<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found');
}

require_once __DIR__ . '/../app/config/database.php';

// Delete expired password reset requests older than 1 day
$deleted = $pdo->exec("DELETE FROM password_resets WHERE created_at < NOW() - INTERVAL 1 DAY");
?>

