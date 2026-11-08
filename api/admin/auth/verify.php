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

    $code = trim($input['code'] ?? '');

    $trustDevice = ($input['trust_device'] ?? false) === true;

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    require_once __DIR__ . '/../../../app/config/database.php';


    /**
     * Repositories
     */
    $adminRepo = new AdminRepository($pdo);

    $adminTwoFactorRepo = new AdminTwoFactorRepository($pdo);

    $trustedDeviceRepo = new TrustedDeviceRepository($pdo);


    /**
     * Mailer
     */
    $mailer = new EmailProvider();


    /**
     * Services
     */

    $trustedDeviceService = new TrustedDeviceService($trustedDeviceRepo, $_ENV['APP_KEY']);

    $authService = new AdminAuthService(
        $adminRepo,
        $adminTwoFactorRepo,
        $mailer,
        $trustedDeviceService
    );


    /**
     * Controller
     */
    $controller = new AdminAuthController(
        $authService
    );

    /**
     * Verify OTP
     */
    $response = $controller->verify(
        $code,
        $ip,
        $trustDevice
    );

    /**
     * Success
     */
    http_response_code(200);

    echo json_encode($response);

} catch (Throwable $e) {

    http_response_code(400);

    echo json_encode([
      'status'  => 'error',
      'message' =>  \App\middleware\ApiSecurity::publicMessage($e)
    ]);

}
