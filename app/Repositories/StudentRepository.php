<?php

namespace App\Repositories;

use App\Models\Student;
use PDO;

class StudentRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }


    /**
     * Find student by student ID.
     */
    public function findById(string $studentId): ?Student
    {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM students
            WHERE student_id = ?
            LIMIT 1
        ");

        $stmt->execute([$studentId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Student::fromArray($row)
            : null;
    }


    /**
     * Mark the student's enrollment type.
     */
    public function markEnrollmentType(
        string $studentId,
        string $type
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET enrollment_type = ?
            WHERE student_id = ?
        ");

        $stmt->execute([
            $type,
            $studentId
        ]);
    }


    /**
     * Get department by program ID.
     */
    public function getDepartment(
        int $programId
    ): ?string {
        $stmt = $this->pdo->prepare("
            SELECT department
            FROM programs
            WHERE program_id = ?
        ");

        $stmt->execute([$programId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row['department'] ?? null;
    }


    /**
     * Find a pending student during registration.
     */
    public function findPending(
        string $studentId,
        string $email
    ): ?array {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM students
            WHERE student_id = ?
              AND email = ?
              AND account_status = 'pending'
            LIMIT 1
        ");

        $stmt->execute([
            $studentId,
            $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    /**
     * Check if the student already has
     * a valid activation token.
     */
    public function hasActiveToken(
        string $studentId,
        string $email,
        string $now
    ): bool {
        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM students
            WHERE student_id = ?
              AND email = ?
              AND activation_token IS NOT NULL
              AND token_expires > ?
              AND account_status = 'pending'
            LIMIT 1
        ");

        $stmt->execute([
            $studentId,
            $email,
            $now
        ]);

        return (bool) $stmt->fetch();
    }


    /**
     * Save activation token.
     */
    public function saveActivationToken(
        string $studentId,
        string $email,
        string $tokenHash,
        string $expires
    ): void {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET activation_token = ?,
                token_expires = ?
            WHERE student_id = ?
              AND email = ?
        ");

        $stmt->execute([
            $tokenHash,
            $expires,
            $studentId,
            $email
        ]);
    }


    /**
     * Find pending student using activation token.
     */
    public function findPendingByActivation(
        string $tokenHash
    ): ?Student {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM students
            WHERE activation_token = ?
              AND account_status = 'pending'
            LIMIT 1
        ");

        $stmt->execute([$tokenHash]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return Student::fromArray($row);
    }


    /**
     * Save the password temporarily.
     *
     * The account remains pending until
     * the registration OTP is verified.
     */
    public function savePendingPassword(
        string $studentId,
        string $passwordHash
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET pending_password_hash = ?
            WHERE student_id = ?
              AND account_status = 'pending'
        ");

        $stmt->execute([
            $passwordHash,
            $studentId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * Complete account activation after
     * successful OTP verification.
     *
     * Moves the pending password into
     * password_hash and activates the account.
     */
    public function activateStudent(
        string $studentId
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET password_hash = pending_password_hash,
                pending_password_hash = NULL,
                activation_token = NULL,
                token_expires = NULL,
                account_status = 'active'
            WHERE student_id = ?
              AND account_status = 'pending'
              AND pending_password_hash IS NOT NULL
        ");

        $stmt->execute([
            $studentId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * Find student by email.
     */
    public function findByEmail(
        string $email
    ): ?Student {
        $stmt = $this->pdo->prepare("
            SELECT *
            FROM students
            WHERE email = ?
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Student::fromArray($row)
            : null;
    }


    /**
     * Update password.
     *
     * Used for password reset/change after
     * the account is already active.
     */
    public function updatePassword(
        string $studentId,
        string $passwordHash
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET password_hash = ?
            WHERE student_id = ?
        ");

        $stmt->execute([
            $passwordHash,
            $studentId
        ]);

        return $stmt->rowCount() > 0;
    }
}
