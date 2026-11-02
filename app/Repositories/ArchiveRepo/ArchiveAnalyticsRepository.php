<?php

declare(strict_types=1);

namespace App\Repositories\ArchiveRepo;

use PDO;

final class ArchiveAnalyticsRepository
{
    public function __construct(
        private PDO $archivePdo
    ) {
    }

    /**
   * Return finalized historical participation statistics
   * from the archived evaluation period.
   */
    public function getArchivedFunnel(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
        SELECT
            total_responses,
            total_incomplete_students,
            total_unresponsive_students
        FROM evaluation_periods
        WHERE period_id = ?
          AND is_closed = 1
        LIMIT 1
    ");

        $stmt->execute([$periodId]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return [
                'total_enrolled' => 0,
                'total_completed' => 0,
                'total_incomplete' => 0,
                'total_unresponsive' => 0
            ];
        }

        $completed = (int) $data['total_responses'];
        $incomplete = (int) $data['total_incomplete_students'];
        $unresponsive = (int) $data['total_unresponsive_students'];

        return [
            'total_enrolled' => $completed + $incomplete + $unresponsive,
            'total_completed' => $completed,
            'total_incomplete' => $incomplete,
            'total_unresponsive' => $unresponsive
        ];
    }


    /**
   * Return the previous 5 archived evaluation periods
   * belonging to the same department.
   */
    public function getArchivedPreviousPeriodTrend(
        string $department,
        int $periodId
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            ep.period_id,

            CONCAT(
                ep.academic_year,
                ' (',
                ep.semester,
                ')'
            ) AS label,

            COALESCE(ep.final_average, 0.00) AS score,
            ep.end_date

        FROM evaluation_periods ep

        WHERE ep.target_dept = ?
          AND ep.is_closed = 1

          AND ep.end_date < (
              SELECT current_period.end_date
              FROM evaluation_periods current_period
              WHERE current_period.period_id = ?
                AND current_period.target_dept = ?
          )

        ORDER BY ep.end_date DESC
        LIMIT 5
    ");

        $stmt->execute([
            $department,
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return archived year-level analytics.
     *
     * Uses finalized participation records captured
     * when the evaluation period was closed.
     */
    public function getArchivedYearLevelAnalytics(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            ph.year_level_at_time AS year_level,

            COUNT(*) AS total_enrolled,

            SUM(
                CASE
                    WHEN ph.status = 'Completed'
                    THEN 1 ELSE 0
                END
            ) AS total_finished,

            SUM(
                CASE
                    WHEN ph.status <> 'Completed'
                    THEN 1 ELSE 0
                END
            ) AS total_not_finished

        FROM participation_history ph

        INNER JOIN evaluation_periods ep
            ON ep.period_id = ph.period_id

        WHERE ph.period_id = ?
          AND ph.dept_at_time = ?
          AND ep.target_dept = ?
          AND ep.is_closed = 1

        GROUP BY ph.year_level_at_time

        ORDER BY ph.year_level_at_time ASC
    ");

        $stmt->execute([
            $periodId,
            $department,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return archived category performance.
     *
     * Only includes evaluations from students
     * who completed all assigned evaluations.
     */
    public function getArchivedDepartmentCategoryPerformance(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            qs.category,
            ROUND(AVG(ea.score), 2) AS average_score

        FROM evaluation_answers ea

        INNER JOIN question_snapshots qs
            ON qs.question_id = ea.question_id
           AND qs.period_id = ea.period_id

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id
           AND es.period_id = ea.period_id
           AND es.is_submitted = 1

        INNER JOIN participation_history ph
            ON ph.student_id = es.student_id
           AND ph.period_id = es.period_id
           AND ph.dept_at_time = ?
           AND ph.status = 'Completed'

        INNER JOIN evaluation_periods ep
            ON ep.period_id = es.period_id
           AND ep.target_dept = ?
           AND ep.is_closed = 1

        WHERE ea.period_id = ?

        GROUP BY qs.category

        ORDER BY qs.category ASC
    ");

        $stmt->execute([
            $department,
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return archived question breakdown.
     *
     * Calculates the average score per question
     * using finalized evaluation records.
     */
    public function getArchivedQuestionBreakdown(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            qs.question_id,
            qs.question_text,

            ROUND(AVG(ea.score), 2) AS average_score

        FROM evaluation_answers ea

        INNER JOIN question_snapshots qs
            ON qs.question_id = ea.question_id
           AND qs.period_id = ea.period_id

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id
           AND es.period_id = ea.period_id
           AND es.is_submitted = 1

        INNER JOIN participation_history ph
            ON ph.student_id = es.student_id
           AND ph.period_id = es.period_id
           AND ph.dept_at_time = ?
           AND ph.status = 'Completed'

        INNER JOIN evaluation_periods ep
            ON ep.period_id = es.period_id
           AND ep.target_dept = ?
           AND ep.is_closed = 1

        WHERE ea.period_id = ?

        GROUP BY
            qs.question_id,
            qs.question_text

        ORDER BY average_score DESC
    ");

        $stmt->execute([
            $department,
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return archived teacher rankings.
     *
     * Uses historical teacher information and
     * finalized student participation records.
     */
    public function getArchivedTeacherRanking(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            ts.teacher_id,
            ts.employee_id,
            ts.full_name,

            ROUND(AVG(ea.score), 2) AS mean_score,

            COUNT(DISTINCT es.student_id) AS total_evaluated

        FROM evaluation_status es

        INNER JOIN teacher_snapshots ts
            ON ts.teacher_id = es.teacher_id
           AND ts.period_id = es.period_id
           AND ts.department = ?

        INNER JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id
           AND ea.period_id = es.period_id

        INNER JOIN participation_history ph
            ON ph.student_id = es.student_id
           AND ph.period_id = es.period_id
           AND ph.dept_at_time = ?
           AND ph.status = 'Completed'

        INNER JOIN evaluation_periods ep
            ON ep.period_id = es.period_id
           AND ep.target_dept = ?
           AND ep.is_closed = 1

        WHERE es.period_id = ?
          AND es.is_submitted = 1

        GROUP BY
            ts.teacher_id,
            ts.employee_id,
            ts.full_name

        ORDER BY
            mean_score DESC,
            total_evaluated DESC
    ");

        $stmt->execute([
            $department,
            $department,
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return students who never started
     * an archived evaluation period.
     */
    public function getArchivedNotEvaluatedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            ph.student_id,
            ph.full_name_at_time AS full_name,
            ph.program_name_at_time AS program_name

        FROM participation_history ph

        INNER JOIN evaluation_periods ep
            ON ep.period_id = ph.period_id
           AND ep.target_dept = ?
           AND ep.is_closed = 1

        WHERE ph.period_id = ?
          AND ph.status = 'Never Started'
          AND ph.dept_at_time = ?

        ORDER BY ph.full_name_at_time ASC
    ");

        $stmt->execute([
            $department,
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return students who started but did not
     * complete an archived evaluation period.
     */
    public function getArchivedAbandonedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->archivePdo->prepare("
        SELECT
            ph.student_id,
            ph.full_name_at_time AS full_name,
            ph.program_name_at_time AS program_name

        FROM participation_history ph

        INNER JOIN evaluation_periods ep
            ON ep.period_id = ph.period_id
           AND ep.target_dept = ?
           AND ep.is_closed = 1

        WHERE ph.period_id = ?
          AND ph.status = 'Abandoned'
          AND ph.dept_at_time = ?

        ORDER BY ph.full_name_at_time ASC
    ");

        $stmt->execute([
            $department,
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
