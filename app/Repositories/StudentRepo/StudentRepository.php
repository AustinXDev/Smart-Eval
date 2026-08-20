<?php

namespace App\Repositories\StudentRepo;

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
            SELECT 
                s.*,
                p.program_name,
                p.department
            FROM students s
            INNER JOIN programs p
                ON s.program_id = p.program_id
            WHERE s.student_id = ?
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
     * Find student by student id and email
     */
    public function findByStudentIdOrEmail(
        string $studentId,
        string $email
    ): ?array {

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM students
            WHERE student_id = ?
                OR email = ?
            LIMIT 1
        ");

        $stmt->execute([
            $studentId,
            $email
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    }


    /**
     * Check if student id or email already exist
     */
    public function existsByStudentIdOrEmail(
        string $studentId,
        string $email
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM students
            WHERE student_id = ?
                OR email = ?
            LIMIT 1
        ");   
        
        $stmt->execute([
            $studentId,
            $email
        ]);

        return (bool) $stmt->fetchColumn();

    }


    /**
     * Get all students by department. 
     */
    public function findAllByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
            SELECT
                s.*,
                p.program_name,
                p.department
            FROM students s
            INNER JOIN programs p
                ON s.program_id = p.program_id
            WHERE p.department = ?
            AND s.is_active = 1
            ORDER BY s.full_name ASC
        ");

        $stmt->execute([
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get students counts by department
     */
    public function getCountByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total,
                COALESCE(
                    SUM(CASE WHEN s.is_active = 1 THEN 1 ELSE 0 END),
                    0
                ) AS active,
                COALESCE(
                    SUM(CASE WHEN s.is_active = 0 THEN 1 ELSE 0 END),
                    0
                ) AS inactive
            FROM students s
            INNER JOIN programs p
                ON s.program_id = p.program_id
            WHERE p.department = ?
        ");

        $stmt->execute([
            $department
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0,
            'active' => 0,
            'inactive' => 0
        ];

    }


    /**
     * Count student evaluatuon responses
     */
    public function countEvaluationResponse(
        string  $studentId
    ): int {

        $stmt = $this->pdo->prepare("
           SELECT COUNT(*) 
           FROM evaluation_status 
            WHERE student_id = ? 
        ");

        $stmt->execute([
            $studentId
        ]);

        return (int) $stmt->fetchColumn();

    }

    
    /**
     * Check if student programs exist
     */
    public function programExist(
        int $programId,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM programs
            WHERE program_id = ?
                AND department = ?
            LIMIT 1
        ");

        $stmt->execute([
            $programId,
            $department
        ]);

        return (bool) $stmt->fetchColumn();

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


    /**
     * Count active student by 
     * department
     * 
     */
    public function countActiveByDepartment(
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM students s
            INNER JOIN programs p
                ON s.program_id = p.program_id
            WHERE p.department = ?
              AND s.is_active = 1
        ");

        $stmt->execute([$department]);

        return (int) $stmt->fetchColumn();
    }


    public function countNotEvaluated(
        string $department,
        int $periodId
    ): int {

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM students s

            INNER JOIN programs p
                ON s.program_id = p.program_id

            WHERE p.department = ?
            AND s.is_active = 1

            AND NOT EXISTS (
                SELECT 1
                FROM evaluation_status es
                WHERE es.student_id = s.student_id
                    AND es.period_id = ?
            )
        ");

        $stmt->execute([
            $department,
            $periodId
        ]);

        return (int) $stmt->fetchColumn();
    }


    /**
    * create new student
    */
    public function create(
        string $studentId,
        string $fullName,
        string $email,
        int $programId,
        int $yearLevel
    ): bool {

        $stmt = $this->pdo->prepare("
            INSERT INTO students (
                student_id,
                full_name,
                email, 
                program_id,
                year_level
            )
            VALUES(?, ?, ?, ?, ?)
        ");

        return $stmt->execute([
            $studentId,
            $fullName,
            $email,
            $programId,
            $yearLevel
        ]);

    }


    /**
     * Reactive student
     */
    public function reactive(
        string $studentId
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET is_active = 1
                WHERE student_id = ? 
        ");

        return $stmt->execute([
            $studentId
        ]);

    }


    /**
     * Edit student
     */
    public function edit(
        string $newID,
        string $oldID,
        string $fullName,
        string $email,
        int $yearLevel,
        int $programId
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET student_id = ?,
                full_name =?,
                email = ?,
                year_level = ?,
                program_id = ?
            WHERE student_id = ?
        ");

        return $stmt->execute([
            $newID,
            $fullName,
            $email,
            $yearLevel,
            $programId,
            $oldID
        ]);

    }


    /**
     * Reset student password
     */
    public function resetPassword(
        string $studentId,
        string $passwordHash
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET password_hash = ?
                WHERE student_id = ?
        ");

        return $stmt->execute([
            $passwordHash,
            $studentId
        ]);

    }


    /**
     * Delete Student
     */
    public function delete(
        string $studentId,
        bool $confirmed
    ): bool {
        
        if(!$confirmed){

            $stmt = $this->pdo->prepare("
                UPDATE students 
                    SET is_active = 0 
                WHERE student_id = ?
            ");

            return $stmt->execute([
                $studentId
            ]);

        }

        $stmt = $this->pdo->prepare("
            DELETE FROM students 
                WHERE student_id = ?
        ");

        return $stmt->execute([
            $studentId
        ]);
    }
}
