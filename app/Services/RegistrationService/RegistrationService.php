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
    ) {
    }

    public function register(string $studentId, string $email): array
    {

        //Check if input is not empty
        if ($studentId === '' || $email === '') {
            throw new RegistrationException(
                'Student ID and email are required'
            );
        }


        // Find pending student ID and email, then check if it is exist.
        $student = $this->students->findPending($studentId, $email);

        if (!$student) {
            throw new RegistrationException(
                'Unable to register with these details. Check your student ID and email. If you already activated your account, please log in or reset your password.',
            );
        }

        $now = new DateTimeImmutable(
            'now',
            new DateTimeZone('Asia/Manila')
        );


        if ($this->students->hasActiveToken(
            $studentId,
            $email,
            $now->format('Y-m-d H:i:s'),
        )) {
            throw new RegistrationException(
                'If your details are correct, you will receive an activation email shortly.'
            );
        }

        $token = bin2hex(random_bytes(32));

        $tokenHash = hash('sha256', $token);

        $expires = $now->modify('+1 hour')
                      ->format('Y-m-d H:i:s');

        $saved = $this->students->saveActivationToken(
            $studentId,
            $email,
            $tokenHash,
            $expires,
            $now->format('Y-m-d H:i:s')
        );


        if (!$saved) {
            throw new RegistrationException(
                'An activation link may already have been issued. Please check your inbox and spam folder.'
            );
        }


        $link = rtrim($_ENV['APP_URL'], '/')
                . '/activate-account?token='
                . urlencode($token);

        $sent = $this->mailer->send(
            $email,
            'Activate Your SMART-EVAL Account',
            RegistrationEmail::build($student['full_name'], $link)
        );

        if (!$sent) {
            $this->students->clearFailedActivationToken(
                $studentId,
                $tokenHash
            );

            throw new RegistrationException(
                'Unable to send the activation email. Please try again.'
            );
        }

        return [
            'status' => 'success',
            'message' =>
                'Activation email sent successfully. Please check your inbox.'
        ];
    }
}
