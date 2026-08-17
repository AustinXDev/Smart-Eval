<?php 

namespace App\Services\RegistrationService;

use App\Repositories\StudentRepository;
use App\Services\TwoFactorServices\TwoFactorService;

class RegistrationVerificationService
{

  public function __construct(
    private StudentRepository $students,
    private TwoFactorService $twoFactor
  )
  {
  }

  public function verify(
    string $studentId,
    string $code,
    string $ip
  ): array {

     /*
    * Validate input
    */
    if ($studentId === '' || $code === '') {
        throw new \Exception(
            'Student ID and verification code are required.'
        );
    }

    /*
    * Find student
    */
    $student = $this->students->findById(
        $studentId
    );

    if (!$student) {
        throw new \Exception(
            'Student account not found.'
        );
    }

  /*
  * Make sure the account is still pending.
  */
  if ($student->accountStatus !== 'pending') {
      throw new \Exception(
          'This account has already been activated.'
      );
  }

  /*
  * Verify registration OTP.
  */
  $verified = $this->twoFactor->verify(
      $studentId,
      'registration',
      $code,
      $ip
  );

  if (!$verified) {
      throw new \Exception(
          'Invalid or expired verification code.'
      );
  }


    $activated = $this->students->activateStudent($studentId);

    if(!$activated){
      throw new \Exception(
        'Unable to activate your account. Please try again.'
      );
    }

    return [
      'status'  =>  'success',
      'message' =>  'Your account has been verified and activated.'
    ];

  }

}

?>