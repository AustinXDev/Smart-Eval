<?php

namespace App\Repositories\ReportRepositories;

use PDO;

class TeacherReportRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Get individual  teacher evaluation report data
     *
     * Only students who completed all of their assigned
     * teacher evaluations within the evaluation period
     * are included in the report.
     */


    #Valid students who completed all of their evaluation_status records of this period

    public function getValidStudentIds(
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT es.student_id
        FROM evaluation_status es
        WHERE es.period_id = ?
        GROUP BY es.student_id
        HAVING
          COUNT(*) > 0
          AND SUM(es.is_submitted = 1) = COUNT(*)
      ");

        $stmt->execute([
          $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    }


    #Get teacher's overall evaluation summary.

    public function getTeacherSummary(
        int $periodId,
        int $teacherId
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT
            t.teacher_id,
            t.employee_id,
            t.full_name,

            ROUND(AVG(ea.score), 2) AS average_score,

            COUNT(DISTINCT es.student_id) AS total_evaluated

        FROM evaluation_status es

        INNER JOIN teachers t
            ON t.teacher_id = es.teacher_id

        INNER JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id

        WHERE es.period_id = ?
          AND es.teacher_id = ?
          AND es.is_submitted = 1

          AND EXISTS (
              SELECT 1
              FROM evaluation_status completed
              WHERE completed.student_id = es.student_id
                AND completed.period_id = es.period_id
              GROUP BY completed.student_id
              HAVING COUNT(*) > 0
                 AND SUM(completed.is_submitted = 1) = COUNT(*)
          )

        GROUP BY
            t.teacher_id,
            t.employee_id,
            t.full_name
    ");

        $stmt->execute([
            $periodId,
            $teacherId
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }


    # Categorical Performance

    /**
     * Get teacher performance by evaluation category.
     *
     * Only includes responses from students who completed
     * all assigned evaluations within the period.
     */
    public function getCategoryBreakdown(
        int $periodId,
        int $teacherId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            q.category,

            ROUND(
                AVG(ea.score),
                2
            ) AS average_score

        FROM evaluation_answers ea

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id

        INNER JOIN questions q
            ON q.question_id = ea.question_id

        WHERE es.period_id = ?
          AND es.teacher_id = ?
          AND es.is_submitted = 1

          AND EXISTS (
              SELECT 1
              FROM evaluation_status completed
              WHERE completed.student_id = es.student_id
                AND completed.period_id = es.period_id
              GROUP BY completed.student_id
              HAVING COUNT(*) > 0
                 AND SUM(completed.is_submitted = 1) = COUNT(*)
          )

        GROUP BY q.category

        ORDER BY average_score DESC
    ");

        $stmt->execute([
            $periodId,
            $teacherId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    #Question Performance

    /**
     * Get teacher performance for each evaluation question.
     *
     * Only includes responses from students who completed
     * all assigned evaluations within the period.
     */
    public function getQuestionPerformance(
        int $periodId,
        int $teacherId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            q.question_id,
            q.question_text,

            ROUND(
                AVG(ea.score),
                2
            ) AS average_score

        FROM evaluation_answers ea

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id

        INNER JOIN questions q
            ON q.question_id = ea.question_id

        WHERE es.period_id = ?
          AND es.teacher_id = ?
          AND es.is_submitted = 1

          AND EXISTS (
              SELECT 1
              FROM evaluation_status completed
              WHERE completed.student_id = es.student_id
                AND completed.period_id = es.period_id
              GROUP BY completed.student_id
              HAVING COUNT(*) > 0
                 AND SUM(completed.is_submitted = 1) = COUNT(*)
          )

        GROUP BY
            q.question_id,
            q.question_text

        ORDER BY average_score ASC
    ");

        $stmt->execute([
            $periodId,
            $teacherId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    # Get evaluation information belongs in this report.

    public function getPeriodById(
        int $periodId
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT 
          academic_year,
          semester
        FROM evaluation_periods
        WHERE period_id = ?
        LIMIT 1
      ");

        $stmt->execute([
          $periodId
        ]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;

    }

    public function getComment(
        int $periodId,
        int $teacherId
    ): ?array {

        $stmt = $this->pdo->prepare("
            SELECT
                comment
            FROM evaluation_status
            WHERE teacher_id = ?
                AND period_id = ?
                AND comment IS NOT NULL
                AND TRIM(comment) <> ''
            ORDER BY date_taken DESC
        ");

        $stmt->execute([$teacherId, $periodId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);

    }

}
