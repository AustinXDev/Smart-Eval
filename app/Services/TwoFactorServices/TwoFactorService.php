<?php 
namespace App\Services\TwoFactorServices;

use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\TwoFactorRepository;
use App\providers\EmailProvider;

class TwoFactorService
{

    private const CODE_EXPIRATION_MINUTES = 5;
    private const  MAX_ATTEMPTS = 5;
    private const ATTEMPT_WINDOW_MINUTES = 5;

  public function __construct(
    private StudentRepository $students,
    private TwoFactorRepository $twoFactor,
    private EmailProvider $mailer
  )
  {
  }

  /**
   * Generate and send 2fa code
   */
  public function sendCode(
    string $studentId,
    string $purpose
  ): array {

    $student = $this->students->findById($studentId);

    if(!$student){
      throw new TwoFactorException(
        'Unable to process the verification request.'
      );
    }

    //Invalid previous codes
    $this->twoFactor->deletePreviousCodes(
      $studentId,
      $purpose
    );

    $code = (string) random_int(
      100000,
      999999
    );

    //Hash code before storing
    $codeHash = hash(
      'sha256', 
      $code
    );

    $timezone = new \DateTimeZone(
      'Asia/Manila'
    );

    $expiresAt = (new \DateTimeImmutable(
      'now',
      $timezone
    ))
      ->modify(
        '+' .  self::CODE_EXPIRATION_MINUTES . ' minutes'
      )
      ->format(
        'Y-m-d H:i:s'
      );

    $created = $this->twoFactor->createCode(
      $studentId,
      $codeHash,
      $purpose,
      $expiresAt
    );

    if(!$created){
      throw new TwoFactorException(
        "Unable to generate a verification code."
      );
    }
    
    $this->mailer->send(
      $student->email,
      'Smart-Eval Verification Code',
      TwoFactorEmail::build(
        $student->fullName,
        $code,
        $purpose,
        self::CODE_EXPIRATION_MINUTES
      )
    );

    return [
      'status'  =>  'success',
      'message' =>  'A verification code has been sent to your registered email.'
    ];

  }

  /**
   * Verify submitted 2fa code.
   */
  public function verify(
    string $studentId,
    string $purpose,
    string $code,
    string $ip
  ): array
  {

    if($studentId === '' || $code === '') {
      throw new TwoFactorException(
        'Verification code is required'
      );
    }

    if(!preg_match('/^\d{6}$/', $code)) {

      $this->twoFactor->recordAttempt(
        $studentId,
        $purpose,
        $ip,
        false
      );

      throw new TwoFactorException(
        'Invalid verification code.'
      );

    }

    //Check failed attempts
    $failures = $this->twoFactor->countRecentFailures(
      $studentId,
      $purpose,
      self::ATTEMPT_WINDOW_MINUTES
    );

    if($failures >= self::MAX_ATTEMPTS) {

      throw new TwoFactorException(
        "Too many verification attempts. Please request a new code later."
      );

    }

    $record = $this->twoFactor->findValidCode(
      $studentId
    );

    if(!$record) {

      $this->twoFactor->recordAttempt(
        $studentId,
        $purpose,
        $ip,
        false
      );

      throw new TwoFactorException(
          'The verification code is invalid or expired.'
      );

    }

    $submittedHash = hash(
        'sha256',
        $code
    );

    if (!hash_equals(
        $record['code_hash'],
        $submittedHash
    )) {
    $this->twoFactor->recordAttempt(
          $studentId,
          $purpose,
          $ip,
          false
      );

      throw new TwoFactorException(
          'Incorrect verification code.'
      );
    }

    $this->twoFactor->markAsUsed(
      (int) $record['id']
    );

    // Record successful attempt
    $this->twoFactor->recordAttempt(
        $studentId,
        $purpose,
        $ip,
        true
    );

    return [
        'status' => 'success',
        'message' => 'Verification successful.'
    ];
  
  }

  public function resendCode(
    string $studentId,
    string $purpose
  ): array {

    if($studentId === ''){
      throw new TwoFactorException(
        "Unable to process the verification request."
      );
    }

    /**
     * Prevent OTP spam
     */

    if($this->twoFactor->hasRecentCode(
      $studentId,
      $purpose,
      60
    )) {
      throw new TwoFactorException(
        "Please wait 60 seconds before requesting another verification code."
      );
    }

    /**
     * Generate and send a new OTP.
     */
    return $this->sendCode(
      $studentId,
      $purpose
    );

  }

}

?>