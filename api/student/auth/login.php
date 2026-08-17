<?php

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Login\LoginController;
use App\Repositories\StudentRepository;
use App\Repositories\LoginAttemptRepository;
use App\Repositories\TwoFactorRepository;
use App\Repositories\EvaluationRepository;

use App\Services\LoginServices\AuthService;
use App\Services\LoginServices\LoginRateLimiter;
use App\Services\LoginServices\EvaluationRedirectResolver;

use App\Services\TwoFactorServices\TwoFactorService;

use App\providers\EmailProvider;
use App\Session\SessionManager;

header('Content-Type: application/json');

$input = json_decode(
    file_get_contents('php://input'),
    true
) ?? [];


$ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {

    /*
     * Database
     */
    require_once __DIR__ . '/../../../app/config/database.php';


    /*
     * Session
     */
    $session = new SessionManager();


    /*
     * Repositories
     */
    $students = new StudentRepository($pdo);

    $loginAttemptRepo = new LoginAttemptRepository($pdo);

    $twoFactorRepo = new TwoFactorRepository($pdo);

    $evaluationRepo = new EvaluationRepository($pdo);


    /*
     * Email
     */
    $mailer = new EmailProvider();


    /*
     * 2FA Service
     */
    $twoFactor = new TwoFactorService(
        $students,
        $twoFactorRepo,
        $mailer
    );


    /*
     * Login rate limiter
     */
    $rateLimiter = new LoginRateLimiter(
        $loginAttemptRepo
    );


    /*
     * Evaluation redirect resolver
     */
    $redirectResolver = new EvaluationRedirectResolver(
        $students,
        $evaluationRepo
    );


    /*
     * Authentication service
     */
    $auth = new AuthService(
        $students,
        $rateLimiter,
        $redirectResolver,
        $session,
        $twoFactor
    );


    /*
     * Controller
     */
    $controller = new LoginController(
        $auth
    );


    /*
     * Login
     */
    $response = $controller->handle(
        $input,
        $ip
    );

    error_log('LOGIN SESSION ID: ' . session_id());
    error_log('LOGIN SESSION: ' . print_r($_SESSION, true));


    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}