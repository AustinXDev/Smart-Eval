<?php 

namespace APP\Services\Admin\PasswordResetServices;

use App\Repositories\AdminRepo\AdminRepository;
use App\Repositories\AdminRepo\AdminPasswordResetRepository;
use App\Services\Admin\PasswordResetServices\ForgotPasswordEmail;
use App\providers\EmailProvider;
use RuntimeException;


class AdminPasswordResetService 
{

  private const LIMIT = 3;
  private const BLOCK_HOURS = 1;

  public function __construct(
    private AdminRepository $adminRepo,
    private AdminPasswordResetRepository $resetRepo,
    private EmailProvider $mailer
  )
  {
  }

  public function requestReset(
    string $username,
    string $ip
  ): array {


    $username = trim($username);


    if($username === ''){
      throw new RuntimeException(
        "Username is required"
      );
    }


    $admin = $this->adminRepo->findByUsername($username);

    /**
     * Check and don't reveal if username exist for security
     */
    if(!$admin) {
      return [];
    }


    /**
     * Rate Limiting
     */
    $attemptCount = $this->resetRepo->countRecentAttempts(
      (int) $admin->adminId,
      $ip,
      self::BLOCK_HOURS
    );


    if($attemptCount > self::LIMIT) {
      throw new RuntimeException(
        'Too many request. Please try again.'
      );
    }

    $token = bin2hex(
      random_bytes(32)
    );

    $token_hash = hash('sha256', $token);

    $expiresAt = (new \DateTimeImmutable(
      'now',
      new \DateTimeZone('Asia/Manila')
    ))
      ->modify('+1 hours')
      ->format('Y-m-d H:i:s');

    /**
     * Store reset request
     */
    $this->resetRepo->create(
      (int) $admin->adminId,
      $ip,
      $token_hash,
      $expiresAt
    );

    $link = rtrim($_ENV['APP_URL'])
          . '/admin-reset-password?token='
          . urldecode($token);

    $sent = $this->mailer->send(
      $admin->email,
      'Password Reset Request',
      ForgotPasswordEmail::build(
          $username,
          $link
      )
    );

    if (!$sent) {
      throw new RuntimeException(
          'Failed to send password reset email.'
      );
    }

    return [
      'status'  => 'success',
      'message' => "If the email is registered, you'll receive a password reset link shortly."
    ];
  }

}
?>