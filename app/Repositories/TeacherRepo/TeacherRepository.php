<?php

namespace App\Repositories\TeacherRepo;

use PDO;
use App\Models\Teacher;

class TeacherRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Get teacher by id
     */
    public function findByEmployeeId(
        string $employeeId
    ): ?Teacher {

        $stmt = $this->pdo->prepare("
          SELECT *
          FROM teachers
          WHERE employee_id = ?
          LIMIT 1
      ");

        $stmt->execute([$employeeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Teacher::fromArray($row)
            : null;
    }


    /**
     * find teacher by ID
     */
    public function findById(
        int $teacherId
    ): ?Teacher {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM teachers
        WHERE teacher_id = ?
        LIMIT 1
    ");

        $stmt->execute([$teacherId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Teacher::fromArray($row)
            : null;

    }


    /**
     * Get active teaching loads.
     */
    public function findHandlesByTeacher(
        int $teacherId
    ): array {

        $stmt = $this->pdo->prepare("
      SELECT
        tl.load_id, 
        tl.year_level,
        p.program_name
      FROM teacher_load tl
      INNER JOIN programs p
        ON tl.program_id = p.program_id
      WHERE tl.teacher_id = ?
        AND tl.is_active = 1
      ORDER BY p.program_name ASC
    ");

        $stmt->execute([$teacherId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Find by employee id and department
     */
    public function findByIdAndDepartment(
        string $employeeId,
        string $department
    ): ?Teacher {

        $stmt = $this->pdo->prepare("
      SELECT *
      FROM teachers 
      WHERE employee_id = ?
        AND department = ?
      LIMIT 1
    ");

        $stmt->execute([
          $employeeId,
          $department
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Teacher::fromArray($row)
            : null;

    }


    /**
     * Find by ID and department b
     * but exclude the parameter of id
     */
    public function existsByEmployeeIdAndDepartment(
        string $employeeId,
        string $department,
        int $excludeTeacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      SELECT 1
      FROM teachers
      WHERE employee_id = ?
        AND department = ?
        AND teacher_id != ?
      LIMIT 1
    ");

        $stmt->execute([
            $employeeId,
            $department,
            $excludeTeacherId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    /**
     * Exist by email and department
     * but exclude by teacher id
     */
    public function existsByEmailAndDepartment(
        string $email,
        string $department,
        int $excludeTeacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      SELECT 1
      FROM teachers
      WHERE employee_id = ?
        AND department = ?
        AND teacher_id != ?
      LIMIT 1
    ");

        $stmt->execute([
          $email,
          $department,
          $excludeTeacherId
        ]);

        return (bool) $stmt->fetchColumn();

    }



    /**
     * Find teacher by email
     */
    public function findByEmailAndDepartment(
        string $email,
        string $department
    ): ?Teacher {

        $stmt = $this->pdo->prepare("
          SELECT *
          FROM teachers
          WHERE email = ?
            AND department = ?
          LIMIT 1
      ");

        $stmt->execute([
          $email,
          $department
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row
            ? Teacher::fromArray($row)
            : null;
    }


    /**
     * Find teacher by name and department
     */
    public function existsByNameAndDepartment(
        string $fullName,
        string $department,
        int $excludeTeacherId
    ): bool {

        $stmt = $this->pdo->prepare("
          SELECT 1
          FROM teachers
          WHERE full_name = ?
            AND department = ?
            AND teacher_id != ?
          LIMIT 1
      ");

        $stmt->execute([
            $fullName,
            $department,
            $excludeTeacherId
        ]);

        return (bool) $stmt->fetchColumn();
    }

    public function getAssignedTeachers(
        string $studentId,
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
          es.eval_id, 
          es.is_submitted,
          t.teacher_id,
          t.full_name,
          t.department,
          t.image_path
        
        FROM teachers t

        INNER JOIN evaluation_status es
          ON es.teacher_id = t.teacher_id

        WHERE es.period_id = ?
          AND es.student_id = ?

        ORDER BY 
          t.full_name ASC
      ");

        $stmt->execute([
          $periodId,
          $studentId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Check if teacher is part of an active evaluation
     */
    public function hasActiveEvaluation(
        int $teacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      SELECT 1
      FROM teacher_load tl

      INNER JOIN teachers t 
        ON t.teacher_id = tl.teacher_id
      
      INNER JOIN evaluation_periods ep
        ON ep.target_dept = t.department
        AND ep.is_active = 1

      WHERE tl.teacher_id = ?
        AND tl.is_active = 1

      LIMIT 1
    ");

        $stmt->execute([
          $teacherId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    /**
     * check deplicate load
     */
    public function existLoad(
        int $teacherId,
        int $programId,
        string $yearLevel
    ): bool {

        $stmt = $this->pdo->prepare("
      SELECT 1
      FROM teacher_load 
      WHERE teacher_id = ?
        AND program_id = ?
        AND year_level = ?
        AND is_active = 1
      LIMIT 1
    ");

        $stmt->execute([
          $teacherId,
          $programId,
          $yearLevel
        ]);

        return (bool) $stmt->fetchColumn();

    }


    /**
     * Check if teacher is active
     */
    public function isTeacherActive(
        int $teacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      SELECT 1
      FROM teachers
      WHERE teacher_id = ?
        and is_active = 1
      LIMIT 1
    ");

        $stmt->execute([
          $teacherId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    /**
     * Check duplicate inactive load
     */
    public function findInactiveLoadId(
        int $teacherId,
        int $programId,
        string $yearLevel
    ): ?int {

        $stmt = $this->pdo->prepare("
      SELECT load_id
      FROM teacher_load
      WHERE teacher_id = ?
        AND program_id = ?
        AND year_level = ?
        AND is_active = 0
      LIMIT 1
    ");

        $stmt->execute([
          $teacherId,
          $programId,
          $yearLevel
        ]);

        $id = $stmt->fetchColumn();

        return $id !== false ? (int)$id : null;

    }


    /**
     * Count evaluation history of a teacher
     */
    public function countEvaluationHistory(
        int $teacherId
    ): int {

        $stmt = $this->pdo->prepare("
          SELECT COUNT(*)
          FROM evaluation_status es

          WHERE es.teacher_id = ?
            AND es.is_submitted = 1
    ");

        $stmt->execute([$teacherId]);

        return (int) $stmt->fetchColumn();

    }


    /**
     * Count active teacher by
     * department
     */
    public function countActiveByDepartment(
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM teachers
      WHERE department = ?
        AND is_active = 1
    ");

        $stmt->execute([$department]);

        return (int) $stmt->fetchColumn();
    }


    /**
     * Get teacher count by department
     */
    public function getCountByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare('
      SELECT 
        COUNT(*) AS total,
      
        COALESCE(
          SUM(
            CASE 
              WHEN is_active = 1 THEN 1
              ELSE 0
            END
          ),
          0
        ) AS active,

        COALESCE(
          SUM(
            CASE 
              WHEN is_active = 0 THEN 1
              ELSE 0 
            END
          ),
          0
        ) AS inactive

      FROM teachers
      WHERE department = ?
    ');

        $stmt->execute([
          $department
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
          'total' => 0,
          'active' => 0,
          'inactive' => 0,
        ];

    }

    /**
     * Get all teachers by department
     */
    public function findAllByDepartment(
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
      SELECT *
      FROM teachers
      WHERE department = ?
      ORDER BY (is_active = 1) DESC, full_name ASC
    ");

        $stmt->execute([$department]);

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row) => Teacher::fromArray($row),
            $rows
        );

    }


    /**
     * Find Load BY ID
     */
    public function findLoadById(
        int $teacherId,
        int  $loadId
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT
            tl.load_id,
            tl.teacher_id,
            tl.program_id,
            tl.year_level,
            tl.is_active,
            p.program_name
        FROM teacher_load tl
        INNER JOIN programs p
            ON p.program_id = tl.program_id
        WHERE tl.load_id = ?
          AND tl.teacher_id = ?
        LIMIT 1
    ");

        $stmt->execute([
            $loadId,
            $teacherId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    }


    /**
     * Find teacher load by program and year
     */
    public function hasTeacherAssigned(
        int $yearLevel,
        int $programId
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT COUNT(*)
        FROM teacher_load
        WHERE year_level = ?
          AND program_id = ?
          AND is_active = 1
      ");

        $stmt->execute([
          $yearLevel,
          $programId
        ]);

        return (int) $stmt->fetchColumn() > 0;

    }



    /**
     * Create new teacher
     */
    public function create(
        Teacher $teacher
    ): int {

        $stmt = $this->pdo->prepare("
          INSERT INTO teachers (
              employee_id,
              full_name,
              email,
              department,
              image_path,
              is_active
          )
          VALUES (?, ?, ?, ?, ?, ?)
      ");

        $stmt->execute([
            $teacher->employeeId,
            $teacher->fullName,
            $teacher->email,
            $teacher->department,
            $teacher->imagePath,
            $teacher->isActive ? 1 : 0
        ]);

        return (int) $this->pdo->lastInsertId();
    }


    public function createLoad(
        int $teacherId,
        int $programId,
        string $yearLevel
    ): int {

        $stmt = $this->pdo->prepare("
      INSERT INTO teacher_load (
        teacher_id,
        program_id,
        year_level,
        is_active
      )
      VALUES (?, ?, ?, 1)
    ");

        $stmt->execute([
          $teacherId,
          $programId,
          $yearLevel
        ]);

        return (int) $this->pdo->lastInsertId();
    }


    /**
     * Reactivate an existing teacher.
     */
    public function reactivate(
        int $teacherId,
        string $employeeId,
        string $fullName,
        string $email,
        string $imagePath
    ): bool {

        $stmt = $this->pdo->prepare("
          UPDATE teachers
          SET
              employee_id = ?,
              full_name = ?,
              email = ?,
              image_path = ?,
              is_active = 1
          WHERE teacher_id = ?
      ");

        return $stmt->execute([
            $employeeId,
            $fullName,
            $email,
            $imagePath,
            $teacherId
        ]);
    }


    /**
     * Reactivate teacher load
     */
    public function reactivateLoad(
        int $loadId
    ): bool {

        $stmt = $this->pdo->prepare("
          UPDATE teacher_load
          SET is_active = 1
          WHERE load_id = ?
            AND is_active = 0
      ");

        $stmt->execute([
            $loadId
        ]);

        return $stmt->rowCount() > 0;
    }


    /**
     * Update teacher information
     */
    public function update(
        int $teacherId,
        string $employeeId,
        string $fullName,
        string $email,
        string $imagePath
    ): bool {

        $stmt = $this->pdo->prepare("
      UPDATE teachers
      SET
        employee_id = ?,
        full_name = ?,
        email = ?,
        image_path = ?
      WHERE teacher_id = ?
    ");

        return $stmt->execute([
          $employeeId,
          $fullName,
          $email,
          $imagePath,
          $teacherId
        ]);
    }


    /**
     * Deactivate teacher
     */
    public function deactivate(
        int $teacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      UPDATE teachers
      SET is_active = 0
      WHERE teacher_id = ?
    ");

        return $stmt->execute([$teacherId]);

    }


    /**
     * Permanently delete teacher.
     */
    public function delete(
        int $teacherId
    ): bool {

        $stmt = $this->pdo->prepare("
      DELETE FROM teachers
      WHERE teacher_id = ?
    ");

        return $stmt->execute([$teacherId]);

    }


    /**
     * Deactivate teacher load
     */
    public function deactivateLoad(
        int $loadId
    ): int {

        $stmt = $this->pdo->prepare("
          UPDATE teacher_load
          SET is_active = 0
          WHERE load_id = ?
      ");

        $stmt->execute([
            $loadId
        ]);

        return $stmt->rowCount();
    }


    /**
     * Permanently delete teacher load.
     */
    public function deleteLoad(
        int $loadId
    ): int {

        $stmt = $this->pdo->prepare("
          DELETE FROM teacher_load
          WHERE load_id = ?
      ");

        $stmt->execute([
            $loadId
        ]);

        return $stmt->rowCount();
    }


    /**
   * Get the teacher ranking
   * based on period Id and department
   *
   */
    public function getTeacherRanking(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT
            t.teacher_id,
            t.full_name AS teacher_name,

            COUNT(DISTINCT es.student_id) AS total_evaluated_students,

            ROUND(AVG(ea.score), 2) AS overall_mean_score

            FROM teachers t

            INNER JOIN evaluation_status es
                ON es.teacher_id = t.teacher_id
                AND es.period_id = ?
                AND es.is_submitted = 1

            INNER JOIN evaluation_answers ea
                ON ea.eval_id = es.eval_id

            INNER JOIN (
            SELECT
                student_id
            FROM evaluation_status
            WHERE period_id = ?
            GROUP BY student_id
            HAVING
                COUNT(*) > 0
                AND SUM(is_submitted = 1) = COUNT(*)
            ) completed_students
                ON completed_students.student_id = es.student_id

            WHERE t.department = ?
              AND t.is_active = 1

            GROUP BY
                t.teacher_id,
                t.full_name

            HAVING AVG(ea.score) IS NOT NULL

            ORDER BY overall_mean_score DESC,
              total_evaluated_students DESC

            LIMIT 5
    ");

        $stmt->execute([
          $periodId,
          $periodId,
          $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}
