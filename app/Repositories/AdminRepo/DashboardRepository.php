<?php

namespace App\Repositories\AdminRepo;

use PDO;

class DashboardRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Get current active period
     * by department
     */
    public function getActivePeriod(
        string $department
    ): ?array {

        $stmt = $this->pdo->prepare("
          SELECT
              period_id
          FROM evaluation_periods
          WHERE target_dept = ?
            AND is_active = 1
          LIMIT 1
      ");

        $stmt->execute([
            $department
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get evaluation period data
     * by active period Id
     */
    public function getEvaluationPeriod(
        int $periodId
    ): ?array {

        $stmt = $this->pdo->prepare("
          SELECT
              academic_year,
              semester,
              start_date,
              end_date
          FROM evaluation_periods
          WHERE period_id = ?
      ");

        $stmt->execute([
            $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    /**
     * Total Active Student
     * by department
     */
    public function countActiveStudents(
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM students s
      INNER JOIN programs p
        ON s.program_id = p.program_id
      WHERE p.department = ?
        AND is_active = 1
    ");

        $stmt->execute([$department]);

        return (int) $stmt->fetchColumn();

    }


    /**
     * Total Active Teachers
     * by department
     */
    public function countActiveTeachers(
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM teachers 
      WHERE department = ?
      AND is_active = 1
    ");

        $stmt->execute([
          $department
        ]);

        return (int) $stmt->fetchColumn();

    }


    /**
     * Total Evaluation submitted
     * by department and period id
     */
    public function countSubmittedEvaluation(
        int $periodId,
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
      SELECT COUNT(*)
      FROM evaluation_status es

      INNER JOIN students s
        ON s.student_id = es.student_id

      INNER JOIN programs p
        ON p.program_id = s.program_id
      
      WHERE es.period_id = ?
        AND es.is_submitted = 1
        AND p.department = ?
    ");

        $stmt->execute([
          $periodId,
          $department,
        ]);

        return (int) $stmt->fetchColumn();

    }



}
