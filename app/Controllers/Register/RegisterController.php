<?php 

namespace App\Controllers\Register;

use App\Services\RegistrationService\RegistrationService;
use App\Services\RegistrationService\RegistrationException;

class RegisterController
{

  public function __construct(
    private RegistrationService $service
  )
  {
  }

  public function handle(array $input)
  {
    try{

      $studentId = trim($input['studentId'] ?? '');
      $email     = trim($input['studentEmail'] ?? '');
      
      return $this->service->register(
        $studentId,
        $email
      );

    } catch(RegistrationException $e){

      return [
        'status' => 'error',
        'message' => $e->getMessage()
      ];
    }
  }

}

?>