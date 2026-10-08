<?php

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\TwoFactor\TwoFactorController;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\TwoFactorRepository;
use App\Repositories\StudentRepo\LoginAttemptRepository;
use App\Services\TwoFactorServices\TwoFactorService;
use App\Services\LoginServices\AuthService;
use App\Services\LoginServices\LoginRateLimiter;
use App\Services\LoginServices\EvaluationRedirectResolver;
use App\providers\EmailProvider;
use App\Session\SessionManager;
use App\Repositories\StudentRepo\EvaluationRepository;

header('Content-Type: application/json');

$input = json_decode(
    file_get_contents('php://input'),
    true
) ?? [];

$code = trim($input['code'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {

    /*
     * Session
     */
    $session = new SessionManager();

    error_log('VERIFY SESSION ID: ' . session_id());
    error_log('VERIFY SESSION: ' . print_r($_SESSION, true));
    error_log('===== 2FA VERIFY REQUEST =====');
    error_log('TIME: ' . date('Y-m-d H:i:s'));
    error_log('SESSION ID: ' . session_id());
    error_log('CODE: ' . $code);

    $pending = $session->get('2fa_pending');
    $studentId = $session->get('2fa_student_id');

    /*
     * Get the temporary 2FA login state.
     */
    $pending = $session->get('2fa_pending');
    $studentId = $session->get('2fa_student_id');

    if (!$pending || !$studentId) {
        throw new Exception(
            'No pending two-factor authentication request.'
        );
    }

    /*
     * Database
     */
    require_once __DIR__ . '/../../../app/config/database.php';

    /*
     * Repositories
     */
    $students = new StudentRepository($pdo);

    $twoFactorRepo = new TwoFactorRepository($pdo);

    $loginAttemptRepo = new LoginAttemptRepository($pdo);

    /*
     * Email
     */
    $mailer = new EmailProvider();

    /*
     * 2FA service
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
     *
     * constructor defines
     * EvaluationRedirectResolver.
     */
    $evaluationRepo = new EvaluationRepository($pdo);

    $redirectResolver = new EvaluationRedirectResolver(
        $students,
        $evaluationRepo
    );

    /*
     * Auth service
     */
    $auth = new AuthService(
        $students,
        $rateLimiter,
        $redirectResolver,
        $session,
        $twoFactor
    );

    /*
     * 2FA controller
     */
    $controller = new TwoFactorController(
        $students,
        $auth,
        $twoFactor,
        $session
    );

    /*
     * Purpose is controlled by the server.
     *
     */
    $response = $controller->verify(
        'login',
        $code,
        $ip
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