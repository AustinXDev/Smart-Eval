<?php


require_once dirname(__DIR__, 1) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../app/init.php';
echo json_encode(['status' => 'success', 'token' => \App\middleware\ApiSecurity::csrfToken()]);
