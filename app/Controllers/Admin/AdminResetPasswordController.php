<?php 

namespace App\Controllers\Admin;

use App\Services\Admin\ResetPasswordServices\ResetPasswordService;

use RuntimeException;
use Throwable;

class AdminResetPasswordController
{

  public function __construct(
    private ResetPasswordService $service
  )
  {
  }

  public function handle(array $input): array
  {

    $token = trim($input['token'] ?? '');
    $password = trim($input['password'] ?? '');

    try{

      return $this->service->reset(
        $token,
        $password
      );

    } catch (\Throwable $e) {

      return[
        'status' => "error",
        'message' => $e->getMessage()
      ];

    }

  }

}

?>