<?php 

namespace App\Services\ResetPasswordServices;

use App\Repositories\StudentRepository;
use App\Repositories\ResetPasswordRepository;

class ResetPasswordServices
{

  public function __construct(
    private StudentRepository $students,
    private ResetPasswordRepository $passwordResets
  )
  {
  }

  /** 
   * @throws ResetPasswordException
   */

  public function reset(
    string $token,
    string  $password
  ): array {

    //Validate input
    if($token === '' || $password === ''){
      throw new ResetPasswordException(
        'Token and password are required.'
      );
    }

    $tokenHash = hash('sha256', $token);

    //Find valid reset request
    $resetRequest = $this->passwordResets
      ->findValidToken($tokenHash);

    if(!$resetRequest){
      throw new ResetPasswordException(
        'Invalid or expired password reset token.'
      );
    }

    //Hash new password
    $passwordHash = password_hash(
      $password,
      PASSWORD_DEFAULT
    );

    if($passwordHash === false){
      throw new ResetPasswordException(
          'Unable to secure the new password.'
      );
    }

    //Update student password
    $success = $this->students->updatePassword(
      $resetRequest['student_id'],
      $passwordHash
    );

    if(!$success){
      throw new ResetPasswordException(
        'Unable to reset your password. Please try again.'
      );
    }

    $this->passwordResets->markAsUsed($tokenHash);

    return[
      'status'  => 'success',
      'message' => 'Password has been reset successfully.'
    ];
  }

}

?>