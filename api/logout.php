<?php

require_once dirname(__DIR__, 1) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../app/init.php';

use App\Controllers\Logout\LogoutController;
use App\Services\LogoutServices\LogoutService;

header('Content-Type: application/json; charset=utf-8');

try {

    $service = new LogoutService();

    $controller = new LogoutController($service);

    $logout = $controller->handle();

    echo json_encode($logout);

} catch (\Throwable $e) {

    http_response_code(500);

    $e->getLine();
    $e->getFile();

    echo json_encode([
      'status' => 'error',
      'error' => $e,
      'message' => \App\middleware\ApiSecurity::publicMessage($e),
    ]);
}
