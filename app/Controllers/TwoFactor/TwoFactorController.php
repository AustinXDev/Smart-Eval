<?php

namespace App\Controllers\TwoFactor;

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\LoginServices\AuthService;
use App\Services\TwoFactorServices\TwoFactorService;
use App\Session\SessionManager;
use Exception;

class TwoFactorController
{
    public function __construct(
        private StudentRepository $students,
        private AuthService $auth,
        private TwoFactorService $twoFactor,
        private SessionManager $session
    ) {
    }

    /**
     * Verify the OTP submitted during login.
     *
     * @throws Exception
     */
    public function verify(
        string $purpose,
        string $code,
        string $ip
    ): array {

        /*
         * Get the temporary authentication state.
         */
        $pending = $this->session->get('2fa_pending');

        $studentId = $this->session->get('2fa_student_id');

        if (!$pending || !$studentId) {
            throw new Exception(
                'No pending two-factor authentication request.'
            );
        }

        /*
         * Verify the OTP.
         *
         * This checks:
         * - code format
         * - expiration
         * - previous attempts
         * - code hash
         * - whether the code was already used
         */
        $this->twoFactor->verify(
            $studentId,
            $purpose,
            $code,
            $ip
        );

        /*
         * OTP is valid.
         *
         * Get the student account.
         */
        $student = $this->students->findById(
            $studentId
        );

        if (!$student) {
            throw new Exception(
                'Student account could not be found.'
            );
        }

        /*
         * Password + OTP are both valid.
         *
         * Complete the actual login.
         */
        return $this->auth->completeLogin(
            $student,
            $ip
        );
    }

    public function verifyRegistration(
      string $code,
      string $ip
    ) {

      $pending = $this->session->get(
        'registration_pending'
      );

      $studentId = $this->session->get(
        'registration_pending'
      );

      if(!$pending || !$studentId){
        throw new \Exception(
          "No pending registration verification."
        );
      }

      //Verify OTP
      $this->twoFactor->verify(
        $studentId,
        'registration',
        $code,
        $ip
      );

      //Get student
      $student = $this->students->findById(
        $studentId
      );

      if(!$student){
        throw new \Exception(
          "Student account could not be found."
        );
      }

      /**
       * 
       * OTP is valid.
       * Activate the account.
       * 
       */
      $this->students->activateStudent(
        $studentId
      );

    }
}