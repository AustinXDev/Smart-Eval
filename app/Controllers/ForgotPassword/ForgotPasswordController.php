<?php 

namespace App\Controllers\ForgotPassword;

use App\Services\ForgotPasswordServices\ForgotPasswordException;
use APP\Services\ForgotPasswordServices\ForgotPasswordServices;
use Throwable;

class ForgotPasswordController
{

  public function __construct(
    private ForgotPasswordServices $service
  )
  {
  }

  public function handle(array $input): array
  {

    try{
      
      return $this->service->sendResetLink(
        trim($input['studentEmail'] ?? ''),
        $_SERVER['REMOTE_ADDR']
      );

    } catch (ForgotPasswordException $e){

      return [
        'status'  => 'error',
        'message' => $e->getMessage()
      ];

      /*
      return [
        'status' => 'error',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
      ];
      */

    } catch (\Throwable $e) {

      return [
        'status'  => 'error',
        'message' => $e->getMessage()
      ];

    }

  }

}

?>