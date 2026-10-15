<?php

declare(strict_types=1);

namespace App\Services\ActivationServices;

use App\Repositories\StudentRepo\StudentRepository;
use App\Services\LoginServices\PasswordPolicy;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class ActivationService
{
    private const TIMEZONE = 'Asia/Manila';

    public function __construct(
        private StudentRepository $students
    ) {
    }

    /**
     * Activate a student account after password creation.
     *
     * OTP verification is handled separately during login.
     *
     * @throws ActivationException
     */
    public function activate(
        string $token,
        string $password
    ): array {

        // Validate input
        if ($token === '' || $password === '') {
            throw new ActivationException(
                'Token and password are required.'
            );
        }

        // Validate password requirements
        PasswordPolicy::validate($password);

        // Hash the activation token
        $hashedToken = hash('sha256', $token);

        // Find student with pending activation
        $student = $this->students
            ->findPendingByActivation($hashedToken);

        if (!$student) {
            throw new ActivationException(
                'Invalid activation token. Please request a new activation link.'
            );
        }

        // Validate token expiration
        $timezone = new DateTimeZone(self::TIMEZONE);

        $now = new DateTimeImmutable(
            'now',
            $timezone
        );

        if ($student->tokenExpires === null) {
            throw new ActivationException(
                'Activation token is invalid or expired.'
            );
        }

        try {
            $expiresAt = new DateTimeImmutable(
                $student->tokenExpires,
                $timezone
            );
        } catch (Throwable $e) {
            throw new ActivationException(
                'Invalid activation token expiration.'
            );
        }

        if ($now >= $expiresAt) {
            throw new ActivationException(
                'Activation token has expired. Please request a new activation link.'
            );
        }

        // Securely hash password
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        if ($passwordHash === false) {
            throw new ActivationException(
                'Unable to secure your password.'
            );
        }

        // Save password
        $saved = $this->students->savePendingPassword(
            $student->studentId,
            $passwordHash
        );

        if (!$saved) {
            throw new ActivationException(
                'Unable to save your password. Please try again.'
            );
        }

        // Immediately activate account
        $activated = $this->students->activateStudent(
            $student->studentId
        );

        if (!$activated) {
            throw new ActivationException(
                'Unable to activate your account. Please try again.'
            );
        }

        return [
            'status' => 'success',
            'message' => 'Your account has been successfully activated. You can now log in.',
            'student_id' => $student->studentId
        ];
    }
}
