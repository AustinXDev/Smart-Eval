<?php 

namespace App\Controllers\Activation;

use App\Services\ActivationServices\ActivationException;
use App\Services\ActivationServices\ActivationService;

class ActivationController
{

  public function __construct(private ActivationService $service)
  {
  }

  public function handle(array $input): array
  {

    try{

      $token = trim($input['token'] ?? '');
      $password = trim($input['password'] ?? '');
      
      return $this->service->activate(
        $token,
        $password
      );

    } catch(ActivationException $e) {

      return [
        'status'  => 'error',
        'message' => $e->getMessage()
      ];

    } catch(\Throwable $e) {

      error_log('[ActivationController]' . $e->getMessage());

      return[
        'status' => 'error',
        'message' => 'An unexpected error occured. Please try again.'
      ];

    }

  }

}

?>