<?php 

require_once __DIR__ . '/../../../app/init.php';

use App\Controllers\Logout\LogoutController;
use App\Services\LogoutServices\LogoutService;

header('Content-type: application/json');

try{

  $service = new LogoutService();

  $controller = new LogoutController($service);

  $response = $controller->handle();

  echo json_encode($response);

} catch (Throwable $e) {

  http_response_code(500);

  echo json_encode([
    'status'  => 'error',
    'message' => 'Unable to logout. Please try again.'
  ]);

}

?>