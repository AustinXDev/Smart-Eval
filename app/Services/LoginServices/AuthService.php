<?php

namespace App\Services\LoginServices;

use App\Models\Student;
use App\Repositories\StudentRepo\StudentRepository;
use App\Services\TwoFactorServices\TwoFactorService;
use App\Services\TrustedDeviceServices\TrustedDeviceService;
use App\Session\SessionManager;

class AuthService
{
    public function __construct(
        private StudentRepository $students,
        private LoginRateLimiter $rateLimiter,
        private EvaluationRedirectResolver $redirectResolver,
        private SessionManager $session,
        private TwoFactorService $twoFactor,
        private TrustedDeviceService $trustedService
    ) {
    }

    /**
     * @throws AuthException on any expected login failure
     * @return array{student: Student, redirect: string}
     */
    public function login(string $studentId, string $password, string $ip): array
    {
        if ($studentId === '' || $password === '') {
            throw new AuthException('Student ID and password are required.');
        }

        $student = $this->students->findById($studentId);

        $rateLimitStudentId = $student?->studentId ?? $studentId;

        if (
            $this->rateLimiter->isLocked(
                $ip,
                $rateLimitStudentId
            )
        ) {

            $minutes = $this->rateLimiter->lockMinutes();

            throw new AuthException("Too  many failed login attempts. Try again after {$minutes} minute(s)");

        }

        if (
            !$student ||
            !$student->verifyPassword($password)
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

        if (
            !$student->isActive ||
            $student->accountStatus !== 'active'
        ) {
            throw new AuthException(
                'This account is not available.'
            );
        }

        // Use the immutable student primary key.
        if ($student->student_pk <= 0) {
            throw new \RuntimeException(
                'Invalid student primary key.'
            );
        }

        // check whether this browser is trusted.
        $isTrusted = $this->trustedService->isTrusted(
            'student',
            (string) $student->student_pk
        );

        if ($isTrusted) {

            // skip email OTP and complete authentication
            return $this->completeLogin(
                $student,
                $ip
            );

        }

        // Browser is not trusted.
        // Continue with the existing OTP authentication.
        $this->twoFactor->resendCode(
            $student->studentId,
            'login'
        );

        //Store temporary authentication state
        $this->session->set('2fa_pending', true);
        $this->session->set('2fa_expires_at', time() + 300);
        $this->session->set(
            '2fa_student_id',
            $student->studentId
        );

        //Store the IP that started authentication
        $this->session->set(
            '2fa_ip',
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

        if (!$student->isActive || $student->accountStatus !== 'active') {
            throw new AuthException('This account is not available.');
        }
        $this->session->regenerate();
        $this->session->set('authenticated_at', time());
        $this->session->set('last_activity', time());

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
        $this->session->remove('2fa_expires_at');

        $this->rateLimiter->recordSuccess(
            $student->studentId,
            $ip
        );


        $this->rateLimiter->clear(
            $student->studentId
        );

        return [
          'status' => 'success',
          'student' => $student,
          'redirect' => $redirect
        ];

    }


    /**
     * Register a trusted browser after successful
     * student login OTP verification.
     */
    public function rememberTrustedDevice(
        Student $student,
        string $deviceName = 'Student Browser'
    ): void {

        // Ensure the account is still active.
        if (
            !$student->isActive ||
            $student->accountStatus !== 'active'
        ) {
            throw new AuthException(
                'This account is not available.'
            );
        }

        // Ensure the permanent primary key is valid.
        if ($student->student_pk <= 0) {
            throw new \RuntimeException(
                'Invalid student primary key.'
            );
        }

        // Register the trusted browser using students.id.
        $this->trustedService->remember(
            'student',
            (string) $student->student_pk,
            $deviceName
        );
    }

}
