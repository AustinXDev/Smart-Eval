<?php

declare(strict_types=1);

namespace App\Repositories\ArchiveRepo;

use PDO;

final class ArchiveTeacherReportRepository
{
    public function __construct(
        private PDO $archivePdo
    ) {
    }

    /**
     * Get students who completed all their evaluations.
     */
    public function getValidStudentIds(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT es.student_id
            FROM evaluation_status es
            WHERE es.period_id = ?
            GROUP BY es.student_id
            HAVING COUNT(*) > 0
               AND SUM(es.is_submitted = 1) = COUNT(*)
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get archived teacher's overall evaluation summary.
     */
    public function getTeacherSummary(
        int $periodId,
        int $teacherId
    ): ?array {

        $stmt = $this->archivePdo->prepare("
            SELECT
                t.teacher_id,
                t.employee_id,
                t.full_name,

                ROUND(AVG(ea.score), 2) AS average_score,

                COUNT(DISTINCT es.student_id) AS total_evaluated

            FROM evaluation_status es

            INNER JOIN teacher_snapshots t
                ON t.teacher_id = es.teacher_id
                AND t.period_id = es.period_id

            INNER JOIN evaluation_answers ea
                ON ea.eval_id = es.eval_id
                AND ea.period_id = es.period_id

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

        $stmt->execute([$periodId, $teacherId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get archived category performance.
     */
    public function getCategoryBreakdown(
        int $periodId,
        int $teacherId
    ): array {

        $stmt = $this->archivePdo->prepare("
            SELECT
                q.category,
                ROUND(AVG(ea.score), 2) AS average_score

            FROM evaluation_answers ea

            INNER JOIN evaluation_status es
                ON es.eval_id = ea.eval_id
                AND es.period_id = ea.period_id

            INNER JOIN question_snapshots q
                ON q.question_id = ea.question_id
                AND q.period_id = ea.period_id

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

        $stmt->execute([$periodId, $teacherId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get archived question performance.
     */
    public function getQuestionPerformance(
        int $periodId,
        int $teacherId
    ): array {

        $stmt = $this->archivePdo->prepare("
            SELECT
                q.question_id,
                q.question_text,
                ROUND(AVG(ea.score), 2) AS average_score

            FROM evaluation_answers ea

            INNER JOIN evaluation_status es
                ON es.eval_id = ea.eval_id
                AND es.period_id = ea.period_id

            INNER JOIN question_snapshots q
                ON q.question_id = ea.question_id
                AND q.period_id = ea.period_id

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

        $stmt->execute([$periodId, $teacherId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get archived evaluation period information.
     */
    public function getPeriodById(int $periodId): ?array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT
                academic_year,
                semester
            FROM evaluation_periods
            WHERE period_id = ?
            LIMIT 1
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Get archived teacher comments.
     */
    public function getComment(
        int $periodId,
        int $teacherId
    ): array {

        $stmt = $this->archivePdo->prepare("
              SELECT es.comment
              FROM evaluation_status es

              WHERE es.teacher_id = ?
                AND es.period_id = ?
                AND es.is_submitted = 1
                AND es.comment IS NOT NULL
                AND TRIM(es.comment) <> ''

                -- Only include fully submitted students.
                AND EXISTS (
                    SELECT 1
                    FROM evaluation_status completed

                    WHERE completed.student_id = es.student_id
                      AND completed.period_id = es.period_id

                    GROUP BY completed.student_id

                    HAVING COUNT(*) > 0
                      AND SUM(completed.is_submitted = 1) = COUNT(*)
                )

              ORDER BY es.date_taken DESC
        ");

        $stmt->execute([$teacherId, $periodId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
