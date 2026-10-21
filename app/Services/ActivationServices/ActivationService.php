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

        try {
            PasswordPolicy::validate($password);
        } catch (\RuntimeException $e) {
            throw new ActivationException($e->getMessage());
        }

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

        $activationTime = new DateTimeImmutable(
            'now',
            new DateTimeZone(self::TIMEZONE)
        );

        $activated = $this->students->activateWithToken(
            $student->studentId,
            $hashedToken,
            $passwordHash,
            $activationTime->format('Y-m-d H:i:s')
        );

        if (!$activated) {
            throw new ActivationException(
                'This activation link has expired, been replaced, or already been used. Please log in or request a new link.'
            );
        }

        return [
            'status' => 'success',
            'message' => 'Your account has been successfully activated. You can now log in.',
            'student_id' => $student->studentId
        ];
    }
}
