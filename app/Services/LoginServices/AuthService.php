<?php 

namespace App\Services\LoginServices;

use App\Models\Student;
use App\Repositories\StudentRepository;
use App\Services\TwoFactorServices\TwoFactorService;
use App\Session\SessionManager;

class AuthService
{
  public function __construct(
    private StudentRepository $students,
    private LoginRateLimiter $rateLimiter,
    private  EvaluationRedirectResolver $redirectResolver,
    private SessionManager $session,
    private TwoFactorService $twoFactor
  )
  {
  }

  /**
   * @throws AuthException on any expected login failure
   * @return array{student: Student, redirect: string}
   */
  public function login(string $studentId, string $password, string $ip): array
  {
    if($studentId === '' || $password === '') {
      throw new AuthException('Student ID and password are required.');
    }

    $student = $this->students->findById($studentId);

    $rateLimitStudentId = $student?->studentId ?? $studentId;

    if(
        $this->rateLimiter->isLocked(
          $ip, 
          $rateLimitStudentId
        )
      ) {

      $minutes = $this->rateLimiter->lockMinutes();

      throw new AuthException("Too  many failed login attempts. Try again after {$minutes} minute(s)");

    }


    if ($student && !$student->isActive) {
      throw new AuthException('This account is not available.');
    }

    if(
      !$student || 
      !$student->passwordHash || 
      !password_verify($password, $student->passwordHash)
    ) {

      $this->rateLimiter->recordFailure(
        $studentId, 
        $ip
      );

      $remaining = $this->rateLimiter->remainingAttempts(
        $ip, 
        $studentId
      );

      throw new AuthException("Incorrect student ID or password. {$remaining} attempt(s) remaining.");
      
    }

    //Generate and send 2fa code
    $this->twoFactor->sendCode(
      $student->studentId, 'login'
    );

    //Store temporary authentication state
    $this->session->set('2fa_pending', true);
    $this->session->set(
      '2fa_student_id',
      $student->studentId
    );

    //Store the IP that started authentication
    $this->session->set(
      '2fa_ip',
      $ip
    );

    // Clear login failures because the password was correct
    $this->rateLimiter->clear($student->studentId);

    $this->rateLimiter->recordSuccess(
      $student->studentId,
      $ip
    );

    return [
      'status' => '2fa_required',
      'message' => 'A verification code has been sent to your registered email.'
    ];
  }

  public function completeLogin(
    Student $student,
    string $ip
  ): array {

    $this->session->regenerate();

    $this->session->set(
      'student', 
      $student->toSessionArray()
    );

    $redirect = $this->redirectResolver->resolve(
      $student
    );

    $this->session->remove('2fa_pending');
    $this->session->remove('2fa_student_id');
    $this->session->remove('2fa_ip');

    $this->rateLimiter->clear(
        $student->studentId
    );

    return [
      'status' => 'success',
      'student' => $student,
      'redirect' => $redirect
    ];

  }
}

?>