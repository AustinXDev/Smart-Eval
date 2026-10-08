<?php 

namespace App\Controllers\Admin;

use App\Services\Admin\AdminAuthService;

class AdminAuthController
{
  public function __construct(
    private AdminAuthService $auth
  )
  {
  }

  public function login(
    string $username,
    string $password,
    string $ip,
  ): array {

    return $this->auth->login(
      $username,
      $password,
      $ip
    );

  }

  public function verify(
    string $code,
    string $ip
  ): array {

    return $this->auth->verify(
        $code,
        $ip
    );

  }
}

?>