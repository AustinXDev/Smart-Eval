<?php 

namespace App\Controllers\Register;

use App\Services\RegistrationService\RegistrationVerificationService;

class RegistrationVerificationController
{

  public function __construct(
    private RegistrationVerificationService $verification
  )
  {
  }

  public function verify(
    string $studentId,
    string $code,
    string $ip
  ): array{

    return $this->verification->verify(
      $studentId,
      $code,
      $ip
    );

  }

}

?>