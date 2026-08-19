<?php 

namespace APP\Services\ForgotPasswordServices;

use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\PasswordResetRepository;
use App\providers\EmailProvider;
use App\Services\ForgotPasswordServices\ForgotPasswordEmail;
use App\Services\ForgotPasswordServices\ForgotPasswordException;

class ForgotPasswordServices
{

  private const LIMIT = 3;
  private const BLOCK_HOURS = 5;

  public function __construct(
    private StudentRepository $students,
    private PasswordResetRepository $resets,
    private EmailProvider $mailer
  )
  {
  }


  public function sendResetLink(
    string $email,
    string $ip
  ): array {

    $this->resets->cleanupExpired();

    $student = $this->students->findByEmail($email);

    if(!$student){
      return[
        'status' => 'success',
        'message' => "If the email is registered, you'll receive a password reset link shortly."
      ];
    }

    // Check rate limit
    $attempts = $this->resets->countRecentAttempts(
        $student->studentId,
        $ip,
        self::BLOCK_HOURS
    );


    if ($attempts >= self::LIMIT) {
        throw new ForgotPasswordException(
            'Too many requests. Please try again later.'
        );
    }


    $token = bin2hex(random_bytes(32));

    $tokenHash = hash('sha256', $token);

    $now = new \DateTimeImmutable(
    'now',
    new \DateTimeZone('Asia/Manila')
    );

    $expires = $now
        ->modify('+1 hour')
        ->format('Y-m-d H:i:s');

    $this->resets->create(
      $student->studentId,
      $ip,
      $tokenHash,
      $expires
    );

    $link = rtrim($_ENV['APP_URL'])
          . '/reset-password?token='
          . urlencode($token);

    $this->mailer->send(
      $student->email,
      'Password Reset Request',
      ForgotPasswordEmail::build(
        $student->fullName,
        $link
      )
    );

    return [
      'status'  => 'success',
      'message' => "If the email is registered, you'll receive a password reset link shortly."
    ];

  }
}

?>