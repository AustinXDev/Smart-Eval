<?php 

namespace App\Services\RegistrationService;

use App\Repositories\StudentRepo\StudentRepository;
use App\providers\EmailProvider;
use DateTimeImmutable;
use DateTimeZone;

class RegistrationService
{
  public function __construct(
    private StudentRepository $students,
    private EmailProvider $mailer
  )
  {
  }

  public function register(string $studentId, string $email): array{

    if($studentId === '' || $email === ''){
      throw new RegistrationException(
        'Student ID and email are required'
      );
    }


    $student = $this->students->findPending($studentId, $email);

    if(!$student){
      throw new RegistrationException(
        'Invalid student ID or email address.',
      );
    }

    $now = new DateTimeImmutable(
      'now',
      new DateTimeZone('Asia/Manila')
    );

    if($this->students->hasActiveToken(
      $studentId,
      $email,
      $now->format('Y-m-d H:i:s'),
    )){
      throw new RegistrationException(
        'If your details are correct, you will receive an activation email shortly.'
    );
    }

    $token = bin2hex(random_bytes(32));

    $tokenHash = hash('sha256', $token);

    $expires = $now->modify('+1 hour')
                  ->format('Y-m-d H:i:s');

    $this->students->saveActivationToken($studentId, $email, $tokenHash, $expires);

    $link = rtrim($_ENV['APP_URL'], '/')
            . '/activate-account?token='
            . urlencode($token);

    $sent = $this->mailer->send(
      $email, 
      'Activate Your SMART-EVAL Account',
      RegistrationEmail::build($student['full_name'], $link)
      );
    
      if(!$sent){
        throw new RegistrationException(
           'Failed to send activation email.'
        );
      }

      return [
          'status' => 'success',
          'message' =>
              'Activation email sent successfully. Please check your inbox.'
      ];
  }
}

?>