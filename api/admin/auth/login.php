<?php



require_once dirname(__DIR__, 3) . '/app/api_bootstrap.php';

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Admin\AdminAuthController;
use App\Repositories\AdminRepo\AdminRepository;
use App\Repositories\AdminRepo\AdminTwoFactorRepository;
use App\Repositories\TrustedDeviceRepo\TrustedDeviceRepository;
use App\Services\TrustedDeviceServices\TrustedDeviceService;
use App\Services\Admin\AdminAuthService;
use App\providers\EmailProvider;

header('Content-type: application/json');

try {

    $input = json_decode(
        file_get_contents('php://input'),
        true
    ) ?? [];

    $username = trim(
        $input['admin_username'] ?? ''
    );

    $password =
        $input['admin_password'] ?? '';

    $ip =
        $_SERVER['REMOTE_ADDR'] ?? '';

    require_once __DIR__ .
        '/../../../app/config/database.php';

    /**
     * Repositories
     */
    $admin = new AdminRepository($pdo);

    $twoFactor = new AdminTwoFactorRepository($pdo);

    $trustedDeviceRepo = new TrustedDeviceRepository($pdo);

    /**
     * Provider
     */

    $mailer = new EmailProvider();


    /**
     * Service
     */

    $trustedDeviceService = new TrustedDeviceService($trustedDeviceRepo, $_ENV['APP_KEY']);

    $auth = new AdminAuthService(
        $admin,
        $twoFactor,
        $mailer,
        $trustedDeviceService
    );

    /**
     * Controller
     */
    $controller = new AdminAuthController(
        $auth
    );

    /**
     * Login
     */

    $response = $controller->login(
        $username,
        $password,
        $ip
    );

    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    error_log(
        '[ADMIN LOGIN ERROR] ' .
        get_class($e) . ': ' .
        $e->getMessage()
    );

    // Sign-in messages from AdminAuthService (wrong password, attempts left,
    // locked, deactivated) are shown as-is; database/engine errors stay generic.
    // (Previously every failure was a generic 500 that also exposed the file path.)
    $isServerError = $e instanceof \PDOException || $e instanceof \Error;

    http_response_code($isServerError ? 500 : 400);

    echo json_encode([
        'status' => 'error',
        'message' => $isServerError
            ? 'Unable to complete login. Please try again later.'
            : \App\middleware\ApiSecurity::publicMessage($e),
    ]);

}
