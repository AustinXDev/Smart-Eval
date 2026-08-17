<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Activation\ActivationController;
use App\Repositories\StudentRepository;
use App\Repositories\TwoFactorRepository;
use App\Services\ActivationServices\ActivationService;
use App\Services\TwoFactorServices\TwoFactorService;
use App\providers\EmailProvider;

header('Content-Type: application/json');

try {

    /*
     * Read JSON
     */
    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];

    /*
     * Database
     */
    require_once __DIR__ . '/../../app/config/database.php';

    /*
     * Repositories
     */
    $students = new StudentRepository($pdo);

    $twoFactorRepo = new TwoFactorRepository($pdo);

    /*
     * Email
     */
    $mailer = new EmailProvider();

    /*
     * Two-factor service
     */
    $twoFactor = new TwoFactorService(
        $students,
        $twoFactorRepo,
        $mailer
    );

    /*
     * Activation service
     */
    $activationService = new ActivationService(
        $students,
        $twoFactor
    );

    /*
     * Controller
     */
    $controller = new ActivationController(
        $activationService
    );

    /*
     * Activate / create password
     */
    $response = $controller->handle(
        $input
    );

    http_response_code(
        $response['status'] === '2fa_required'
            ? 200
            : 400
    );

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}