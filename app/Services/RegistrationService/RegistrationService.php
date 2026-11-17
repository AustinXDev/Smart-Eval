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


        // A link was already sent and has not expired yet: tell the student
        // where to look and when a new link can be requested. Not an error.
        $activeExpiry = $this->students->findActiveTokenExpiry(
            $studentId,
            $email,
            $now->format('Y-m-d H:i:s'),
        );

        if ($activeExpiry !== null) {
            return $this->alreadySentResponse($activeExpiry, $now);
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

    /**
     * Response for a student whose activation link is still valid.
     * e.g. "… You can request a new link after 9:15 PM."
     * The date is added when the link expires on another day.
     */
    private function alreadySentResponse(
        string $expiresAt,
        DateTimeImmutable $now
    ): array {
        $timezone = new DateTimeZone('Asia/Manila');
        $expires = new DateTimeImmutable($expiresAt, $timezone);

        $when = $expires->format('Y-m-d') === $now->format('Y-m-d')
            ? $expires->format('g:i A')
            : $expires->format('M j, g:i A');

        return [
            'status' => 'pending',
            'message' => "An activation link was already sent to this email. "
                . "Check your inbox and spam folder. "
                . "You can request a new link after {$when}.",
            'retry_after' => $expires->format(DATE_ATOM),
        ];
    }
}
