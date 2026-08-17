<?php 
namespace App\Controllers\ResetPassword;

use App\Services\ResetPasswordServices\ResetPasswordException;
use App\Services\ResetPasswordServices\ResetPasswordServices;

class ResetPasswordController
{

  public function __construct(
    private ResetPasswordServices $service
  )
  {
  }

  public function handle(array $input): array
  {

    $token = trim($input['token'] ?? '');
    $password = $input['password'] ?? '';

    try{
      return $this->service->reset(
        $token,
        $password
      );
    } catch (ResetPasswordException $e) {

    return [
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ];

      return [
        'status'  => 'error',
        'message' => $e->getMessage()
      ];

    } catch (\Throwable $e){

      return [
        'status'  => 'error',
        'message' => $e->getMessage()
      ];

    }
    

  }

}

?>