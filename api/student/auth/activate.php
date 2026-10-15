<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';
require_once dirname(__DIR__, 3) . '/app/init.php';

use App\Controllers\Activation\ActivationController;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\ActivationServices\ActivationService;
use App\Middleware\ApiSecurity;

header('Content-Type: application/json; charset=utf-8');

try {

    // Read JSON request
    $input = json_decode(
        file_get_contents('php://input'),
        true,
        512,
        JSON_THROW_ON_ERROR
    );

    if (!is_array($input)) {
        throw new InvalidArgumentException(
            'Invalid request payload.'
        );
    }

    // Database connection
    require_once dirname(__DIR__, 3) . '/app/config/database.php';

    // Repository
    $students = new StudentRepository($pdo);

    // Activation service
    $activationService = new ActivationService(
        $students
    );

    // Controller
    $controller = new ActivationController(
        $activationService
    );

    // Process activation
    $response = $controller->handle($input);

    // Preserve a valid controller response
    if (
        !is_array($response) ||
        ($response['status'] ?? null) !== 'success'
    ) {
        http_response_code(400);

        echo json_encode([
            'status' => 'error',
            'message' => is_array($response)
                ? ($response['message'] ?? 'Unable to activate account.')
                : 'Unable to activate account.'
        ]);

        exit;
    }

    // Successful activation
    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => ApiSecurity::publicMessage($e)
    ]);
}
