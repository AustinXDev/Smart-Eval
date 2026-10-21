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
                p.program_code,
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


    public function hasEvaluationStatus(
        string $studentId,
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare(
            "
            SELECT 1
            FROM evaluation_status
            WHERE student_id = ?
                AND period_id = ?
            LIMIT 1
        "
        );

        $stmt->execute([
            $studentId,
            $periodId
        ]);

        return (bool) $stmt->fetchColumn();

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
                s.student_id, s.full_name, s.email, s.program_id,
                s.year_level, s.enrollment_type, s.account_status,
                s.is_active, s.is_finished_all,
                p.program_name,
                p.department
            FROM students s
            INNER JOIN programs p
                ON s.program_id = p.program_id
            WHERE p.department = ?
            AND s.is_active = 1
            ORDER BY (s.is_active = 1) DESC, s.full_name ASC
        ");

        $stmt->execute([
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get students counts by department
     */
    public function findPageByDepartment(
        string $department,
        int $offset,
        int $limit,
        string $search,
        string $status,
        string $program
    ): array {
        // Preserve the existing active-student list scope.
        $where = 'p.department = :department AND s.is_active = 1';

        $params = ['department' => $department];

        if ($search !== '') {

            $where .= ' AND (s.student_id LIKE :idSearch OR s.full_name LIKE :nameSearch OR p.program_name LIKE :programSearch OR p.department LIKE :deptSearch)';

            foreach (['idSearch', 'nameSearch', 'programSearch', 'deptSearch'] as $key) {
                $params[$key] = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            }

            $where = str_replace([':idSearch', ':nameSearch', ':programSearch', ':deptSearch'], [":idSearch ESCAPE '!'", ":nameSearch ESCAPE '!'", ":programSearch ESCAPE '!'", ":deptSearch ESCAPE '!'"], $where);

        }

        if ($status === 'Inactive') {
            $where .= ' AND s.is_active = 0';
        }

        if ($program !== 'All' && $program !== '') {
            $where .= ' AND p.program_name = :program';
            $params['program'] = $program;
        }

        $from = ' FROM students s INNER JOIN programs p ON s.program_id = p.program_id WHERE ' . $where;
        $count = $this->pdo->prepare('SELECT COUNT(*)' . $from);

        $count->execute($params);

        $filtered = (int) $count->fetchColumn();

        $stmt = $this->pdo->prepare('SELECT s.student_id, s.full_name, s.is_active, p.program_name, p.department' . $from . ' ORDER BY s.full_name ASC, s.student_id ASC LIMIT :limit OFFSET :offset');

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }

        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);

        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return ['students' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'filtered' => $filtered];

    }

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
     * Save activation token for a pending student.
     */
    public function saveActivationToken(
        string $studentId,
        string $email,
        string $tokenHash,
        string $expires,
        string $now
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET activation_token = :token,
                token_expires = :expires
            WHERE student_id = :student_id
            AND email = :email
            AND account_status = 'pending'
            AND (
                activation_token IS NULL
                OR token_expires IS NULL
                OR token_expires <= :now
            )
        ");

        $stmt->execute([
            'token' => $tokenHash,
            'expires' => $expires,
            'student_id' => $studentId,
            'email' => $email,
            'now' => $now,
        ]);

        return $stmt->rowCount() === 1;
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


    public function activateWithToken(
        string $studentId,
        string $tokenHash,
        string $passwordHash,
        string $now
    ): bool {
        $stmt = $this->pdo->prepare("
            UPDATE students
            SET password_hash = :password,
                pending_password_hash = NULL,
                activation_token = NULL,
                token_expires = NULL,
                account_status = 'active'
            WHERE student_id = :student_id
            AND activation_token = :token
            AND token_expires > :now
            AND account_status = 'pending'
        ");

        $stmt->execute([
            'password' => $passwordHash,
            'student_id' => $studentId,
            'token' => $tokenHash,
            'now' => $now,
        ]);

        return $stmt->rowCount() === 1;
    }


    /**
     * Cleare the failed activation token
     */
    public function clearFailedActivationToken(
        string $studentId,
        string $tokenHash
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET activation_token = NULL,
                token_expires = NULL
            WHERE student_id = ?
                AND activation_token = ?
                AND account_status = 'pending'
        ");

        $stmt->execute([$studentId, $tokenHash]);

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

        if (!$confirmed) {

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


    /**
     * Update selected teacher Ids
     */
    public function updateSelectedTeacherIds(
        string $studentId,
        array $teacherIds
    ): void {

        $stmt = $this->pdo->prepare("
            UPDATE students
            SET selected_load_ids = ?
            WHERE student_id = ?
        ");

        $stmt->execute([
            json_encode($teacherIds),
            $studentId
        ]);

    }
}
