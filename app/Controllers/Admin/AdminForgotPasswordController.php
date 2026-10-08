<?php 

namespace App\Controllers\Admin;

use App\Services\Admin\PasswordResetServices\AdminPasswordResetService;

use Throwable;

class AdminForgotPasswordController
{

public function __construct(
  private AdminPasswordResetService $passResetRepo
) {
}

public function handle(string $username): array 
{

  try {

    return $this->passResetRepo->requestReset(
      trim($username ?? ''),
      $_SERVER['REMOTE_ADDR']
    );

  } catch (\Throwable $e) {
    return [
      'status'  => 'error',
      'message' => $e->getMessage()
    ];
  }

}

}

?>