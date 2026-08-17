<?php 

namespace App\Services\ActivationServices;

use App\Repositories\StudentRepository;
use App\Services\ActivationServices\ActivationException;
use App\Services\TwoFactorServices\TwoFactorService;
use DateTimeImmutable;
use DateTimeZone;

class ActivationService
{
  
  public function __construct(
    private StudentRepository $students,
    private TwoFactorService $twoFactor
  )
  {
  }

  /**
   * @throws ActivationException
   */

  public function activate(string $token, string $password): array
  {

    //Validate Input
    if($token === '' || $password === ''){
      throw new ActivationException('
        Token and password are required.
      ');
    }

    //Hash the token
    $hashedToken = hash('sha256', $token);

    $student = $this->students->findPendingByActivation($hashedToken);

    if(!$student){
      throw new ActivationException('
        Invalid activation token.Please request a new activation link.
      ');
    }

    /**
     * Check if token expires
     */

    $now = new DateTimeImmutable(
      'now',
      new DateTimeZone('Asia/Manila')
    );

    if($student->tokenExpires === null ||
      $now > new DateTimeImmutable($student->tokenExpires))
    {
      throw new ActivationException(
        'Activation token has expired. Please request a new activation link.'
      );
    }

    //Hash password
    $passwordHash = password_hash(
      $password,
      PASSWORD_DEFAULT
    );

    if ($passwordHash === false) {
        throw new ActivationException(
            'Unable to secure your password.'
        );
    }

     /*
      * Save password.
      *
      */
    $saved = $this->students->savePendingPassword(
        $student->studentId,
        $passwordHash
    );

    if (!$saved) {
        throw new ActivationException(
            'Unable to save your password. Please try again.'
        );
    }

     /*
      * Generate and send registration OTP.
      */
    $this->twoFactor->sendCode(
        $student->studentId,
        'registration'
    );

    /*
    * Password is created, but account is NOT
    * active until OTP verification succeeds.
    */
    return [
        'status' => '2fa_required',
        'message' =>
            'Your password has been created. A verification code has been sent to your registered email.',
        'student_id' => $student->studentId
    ];

  }

}

?>