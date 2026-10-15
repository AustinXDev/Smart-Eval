<?php

// Read-only session verification using the existing authentication authority.
require_once dirname(__DIR__) . '/app/api_bootstrap.php';

use App\middleware\ApiSecurity;

try {
    $lastActivity = $_SESSION['last_activity'] ?? null;
    if (($_GET['role'] ?? '') === 'admin') {
        ApiSecurity::admin($pdo);
    } elseif (($_GET['role'] ?? '') === 'student') {
        ApiSecurity::student($pdo);
    } else {
        ApiSecurity::fail(400, 'Invalid session role.');
    }
    // Monitoring must not extend the existing server-side inactivity limit.
    $_SESSION['last_activity'] = $lastActivity;
    $expiresAt = min((int) $lastActivity + 1800, (int) $_SESSION['authenticated_at'] + 28800);
    echo json_encode(['status' => 'success', 'expiresIn' => max(0, $expiresAt - time())]);
} catch (Throwable $error) {
    ApiSecurity::fail(500, 'Unable to verify your session.');
}
