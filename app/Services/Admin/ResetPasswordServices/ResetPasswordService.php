<?php 

namespace App\Services\Admin\ResetPasswordServices;

use App\Repositories\AdminRepo\AdminRepository;
use App\Repositories\AdminRepo\AdminResetPasswordRepository;

use RuntimeException;

class ResetPasswordService
{

  public function __construct(
    private AdminRepository $adminRepo,
    private AdminResetPasswordRepository $adminResetRepo
  )
  {
  }

  public function reset(
    string $token,
    string $password
  ): array {

    /**
     * Validate inputs
     */
    if($token === '' || $password === '') {

      throw new RuntimeException("
        Token and password are required.
      ");

    }

    $tokenHash = hash('sha256', $token);

    $resetRequest = $this->adminResetRepo->findValidToken($tokenHash);

    if(!$resetRequest) {
      throw new RuntimeException("
        Invalid or expired password reset token.
      ");
    }

    //Hash new password
    $passwordHash = password_hash(
      $password,
      PASSWORD_DEFAULT
    );

    if($passwordHash === false){
      throw new RuntimeException("
        Unable to secure the new password.
      ");
    }

    /**
     * Update admin password
     */
    $success = $this->adminRepo->updatePassword(
      $resetRequest['admin_id'],
      $passwordHash
    );

    if(!$success) {
      throw new RuntimeException("
        Unable to reset your password. Please try again.
      ");
    }

    $this->adminResetRepo->markAsUsed($tokenHash);

    return[
      'status'  => 'success',
      'message' =>  'Password has been reset successfully.'
    ];
    
  }

}

?>