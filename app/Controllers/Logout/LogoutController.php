<?php 

namespace App\Controllers\Logout;

use App\Services\LogoutServices\LogoutService;

class LogoutController
{

  public function __construct(
    private LogoutService $logoutService
  )
  {
  }

  
  public function handle(): array
  {
    return $this->logoutService->logout();
  }

}

?>