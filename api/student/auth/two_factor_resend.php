<?php

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\TwoFactor\TwoFactorSendController;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\TwoFactorRepository;
use App\Services\TwoFactorServices\TwoFactorService;
use App\providers\EmailProvider;
use App\Session\SessionManager;

header('Content-Type: application/json');

try {

    $session = new SessionManager();

    $pending = $session->get('2fa_pending');
    $studentId = $session->get('2fa_student_id');

    if (!$pending || !$studentId) {
        throw new Exception(
            'No pending two-factor authentication request.'
        );
    }

    require_once __DIR__ . '/../../../app/config/database.php';

    $students = new StudentRepository($pdo);

    $twoFactorRepo = new TwoFactorRepository($pdo);

    $mailer = new EmailProvider();

    $twoFactor = new TwoFactorService(
        $students,
        $twoFactorRepo,
        $mailer
    );


    $controller = new TwoFactorSendController(
        $twoFactor,
    );

    /*
     * The purpose is controlled by the server.
     * Don't accept it from JavaScript.
     */
    $response = $controller->resend(
        $studentId,
        'login'
    );

    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}