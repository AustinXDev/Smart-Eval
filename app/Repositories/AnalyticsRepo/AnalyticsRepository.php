<?php

namespace App\Repositories\AnalyticsRepo;

use PDO;

class AnalyticsRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Return historical funnel
     * completed, incomplete, and unresponsive students
     */
    public function getHistoricalFunnel(
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT 
            total_responses,
            total_incomplete_students,
            total_unresponsive_students
          FROM evaluation_periods
          WHERE period_id = ?
            AND is_closed = 1
          LIMIT 1
        ");

        $stmt->execute([
          $periodId
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        if (!$data) {
            return [
                'total_enrolled' => 0,
                'completed' => 0,
                'total_incomplete' => 0,
                'never_started' => 0
            ];
        }

        $completed = (int) ($data['total_responses'] ?? 0);
        $incomplete = (int) ($data['total_incomplete_students'] ?? 0);
        $neverStarted = (int) ($data['total_unresponsive_students'] ?? 0);

        return [
            'total_enrolled' => $completed + $incomplete + $neverStarted,
            'total_completed' => $completed,
            'total_incomplete' => $incomplete,
            'total_unresponsive' => $neverStarted
        ];

    }


    /**
     * Return live funnel
     */
    public function getLiveFunnel(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT 
          COUNT(s.student_id) AS total_enrolled,

          COUNT(CASE 
            WHEN es.total_evals IS NULL THEN 1 
          END) AS total_unresponsive,

          COUNT(CASE 
            WHEN es.total_evals IS NOT NULL 
            AND es.completed_evals < es.total_evals THEN 1 
          END) AS total_incomplete,

          COUNT(CASE 
            WHEN es.total_evals IS NOT NULL 
            AND es.completed_evals = es.total_evals THEN 1 
          END) AS total_completed

        FROM students s
        INNER JOIN programs p 
          ON p.program_id = s.program_id

        LEFT JOIN (
          SELECT 
            student_id,
            COUNT(*) AS total_evals,
            SUM(
              CASE WHEN is_submitted = 1 THEN 1 ELSE 0 END
            ) AS completed_evals

          FROM evaluation_status
          WHERE period_id = ?
            GROUP BY student_id
        ) es 
          ON es.student_id = s.student_id

        WHERE p.department = ?
          AND s.is_active = 1;
      ");

        $stmt->execute([
          $periodId,
          $department
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

    }


    /**
     * Return four previous evaluation period mean score
     */
    public function getPreviousPeriodTrend(
        string $department,
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            period_id,
            CONCAT(
                academic_year,
                ' (',
                semester,
                ')'
            ) AS label,
            COALESCE(final_average, 0.00) AS score,
            end_date
        FROM evaluation_periods
        WHERE target_dept = ?
          AND is_closed = 1
          AND end_date < (
              SELECT end_date
              FROM evaluation_periods
              WHERE period_id = ?
          )
        ORDER BY end_date DESC
        LIMIT 5
    ");

        $stmt->execute([
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Return current evaluation perido score
     */
    public function getLiveMeanScore(
        int $periodId
    ): array {
        $stmt = $this->pdo->prepare("
        SELECT
            p.period_id,
            CONCAT(
                p.academic_year,
                ' (', p.semester, ')',
                IF(p.is_closed = 0, ' (Live)', '')
            ) AS label,
            COALESCE(ROUND(AVG(ea.score), 2), 0.00) AS score
        FROM evaluation_periods p

        -- Left join to retain the period even if no evaluations exist
        LEFT JOIN evaluation_status es
            ON es.period_id = p.period_id
          AND es.is_submitted = 1

          -- Only match answers from students who submitted ALL assigned evaluations
          AND NOT EXISTS (
              SELECT 1
              FROM evaluation_status incomplete
              WHERE incomplete.period_id = es.period_id
                AND incomplete.student_id = es.student_id
                AND incomplete.is_submitted = 0
          )

        -- Left join to retain the period even if no scores exist yet
        LEFT JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id

        WHERE p.period_id = ?

        GROUP BY
            p.period_id,
            p.academic_year,
            p.semester,
            p.is_closed
      ");

        $stmt->execute([
          $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }


    /**
     * Get the selected closed evaluation period mean score.
     */
    public function getHistoricalMeanScore(
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            period_id,
            CONCAT(
                academic_year,
                ' (',
                semester,
                ')'
            ) AS label,
            COALESCE(final_average, 0.00) AS score,
            end_date
        FROM evaluation_periods
        WHERE period_id = ?
          AND is_closed = 1
        LIMIT 1
    ");

        $stmt->execute([
            $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }


    /**
     * Return live year level analytics
     * e.g, 1st year, 2nd year, 3rd year, etc...
     */
    public function getLiveYearLevelAnalytics(
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            year_level,
            COUNT(*) AS total_enrolled,
            SUM(status = 'total_finished') AS total_finished,
            SUM(status = 'total_not_finished') AS total_not_finished

        FROM (
            SELECT
                s.student_id,
                s.year_level,

                CASE
                    WHEN COUNT(es.eval_id) > 0
                        AND SUM(es.is_submitted = 1) = COUNT(es.eval_id)
                        THEN 'total_finished'

                    ELSE 'total_not_finished'
                END AS status

            FROM students s

            INNER JOIN programs p
              ON p.program_id = s.program_id

            INNER JOIN evaluation_periods ep
              ON ep.period_id = ?
              AND ep.target_dept = p.department

            LEFT JOIN evaluation_status es
                ON es.student_id = s.student_id
                AND es.period_id = ep.period_id

            WHERE s.is_active = 1

            GROUP BY
                s.student_id,
                s.year_level
        ) AS student_status

        GROUP BY year_level
        ORDER BY year_level ASC
      ");

        $stmt->execute([
          $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
    * Return historical year level analytics
    */
    public function getHistoricalYearLevelAnalytics(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT 
            year_level_at_time AS year_level,
            COUNT(*) AS total_enrolled,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS total_finished,
            SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS total_finished,
            SUM(CASE WHEN status != 'Completed' THEN 1 ELSE 0 END) AS total_not_finished
          FROM participation_history
          WHERE period_id = ?
            AND dept_at_time = ?
          GROUP BY year_level_at_time
          ORDER BY year_level_at_time ASC
      ");

        $stmt->execute([$periodId, $department]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Return live category performance
     */
    public function getLiveDepartmentCategoryPerformance(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            q.category,
            ROUND(AVG(ea.score), 2) AS average_score

        FROM evaluation_answers ea

        INNER JOIN questions q
            ON q.question_id = ea.question_id

        INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id
            AND es.period_id = ?
            AND es.is_submitted = 1

        INNER JOIN students s
            ON s.student_id = es.student_id

        INNER JOIN programs p
            ON p.program_id = s.program_id
            AND p.department = ?

        INNER JOIN (
            SELECT
                student_id

            FROM evaluation_status

            WHERE period_id = ?

            GROUP BY student_id

            HAVING COUNT(*) > 0
              AND SUM(is_submitted = 1) = COUNT(*)

        ) completed_students
            ON completed_students.student_id = es.student_id

        GROUP BY q.category

        ORDER BY q.category ASC
      ");

        $stmt->execute([
            $periodId,
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Return historical category performance
     */
    public function getHistoricalDepartmentCategoryPerformance(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT 
          q.category,
          ROUND(AVG(ea.score), 2) AS average_score

        FROM evaluation_answers ea

        INNER JOIN questions q
          ON q.question_id = ea.question_id

        INNER JOIN evaluation_status es
          ON es.eval_id = ea.eval_id
          AND es.period_id = ?
          AND es.is_submitted = 1

        INNER JOIN participation_history ph
          ON ph.student_id = es.student_id
          AND ph.period_id = ?
          AND ph.dept_at_time = ?
          AND ph.status = 'Completed'

        GROUP BY q.category

        ORDER BY q.category ASC

      ");

        $stmt->execute([
          $periodId,
          $periodId,
          $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Return live question breakdown
     */
    public function getLiveQuestionBreakdown(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT 
          q.question_id,
          q.question_text,
          ROUND(AVG(ea.score), 2) AS average_score

        FROM evaluation_answers ea

        INNER JOIN questions q
          ON q.question_id = ea.question_id

        INNER JOIN evaluation_status es
          ON es.eval_id = ea.eval_id
          AND es.period_id = ?
          AND es.is_submitted = 1

        INNER JOIN students s
          ON s.student_id = es.student_id

        INNER JOIN programs p
          ON p.program_id = s.program_id
          AND p.department = ?

        INNER JOIN(
          SELECT 
            student_id

          FROM evaluation_status

          WHERE period_id = ?

          GROUP BY student_id

          HAVING COUNT(*) > 0
            AND SUM(is_submitted = 1) = COUNT(*)
        ) completed_students
          ON completed_students.student_id = es.student_id

        GROUP BY
          q.question_id,
          q.question_text

        ORDER BY average_score DESC
      ");

        $stmt->execute([
          $periodId,
          $department,
          $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Return historical question breakdown
     */
    public function getHistoricalQuestionBreakdown(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT 
            q.question_id,
            q.question_text,
            ROUND(AVG(ea.score), 2) AS average_score

          FROM evaluation_answers ea

          INNER JOIN questions q
            ON q.question_id = ea.question_id

          INNER JOIN evaluation_status es
            ON es.eval_id = ea.eval_id
            AND es.period_id = ?
            AND es.is_submitted = 1

          INNER JOIN participation_history ph
            ON ph.student_id = es.student_id
            AND ph.period_id = ?
            AND ph.dept_at_time = ?
            AND ph.status = 'Completed'

          GROUP BY
            q.question_id,
            q.question_text

          ORDER BY average_score DESC
        ");

        $stmt->execute([
          $periodId,
          $periodId,
          $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get teacher ranking for an active evaluation period.
     */
    public function getLiveTeacherRanking(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            t.teacher_id,
            t.employee_id,
            t.full_name,

            ROUND(AVG(ea.score), 2) AS mean_score,

            COUNT(DISTINCT es.student_id) AS total_evaluated

        FROM evaluation_status es

        INNER JOIN teachers t
            ON t.teacher_id = es.teacher_id
            AND t.department = ?
            AND t.is_active = 1

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

        WHERE es.period_id = ?
          AND es.is_submitted = 1

        GROUP BY
            t.teacher_id,
            t.employee_id,
            t.full_name

        ORDER BY
            mean_score DESC,
            total_evaluated DESC
    ");

        $stmt->execute([
            $department,
            $periodId,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getHistoricalTeacherRanking(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            t.teacher_id,
            t.employee_id,
            t.full_name,

            ROUND(AVG(ea.score), 2) AS mean_score,

            COUNT(DISTINCT es.student_id) AS total_evaluated

        FROM evaluation_status es

        INNER JOIN teachers t
            ON t.teacher_id = es.teacher_id
            AND t.is_active = 1
            AND t.department = ?

        INNER JOIN evaluation_answers ea
            ON ea.eval_id = es.eval_id

        INNER JOIN participation_history ph
            ON ph.student_id = es.student_id
            AND ph.period_id = es.period_id
            AND ph.dept_at_time = ?
            AND ph.status = 'Completed'

        WHERE es.period_id = ?
          AND es.is_submitted = 1

        GROUP BY
            t.teacher_id,
            t.employee_id,
            t.full_name

        ORDER BY
            mean_score DESC,
            total_evaluated DESC
    ");

        $stmt->execute([
            $department,
            $department,
            $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get students who have not started the active evaluation.
     */
    public function getLiveNotEvaluatedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
          SELECT 
            s.student_id,
            s.full_name,
            s.email,
            p.program_name
          
          FROM students s

          INNER JOIN programs p
            ON p.program_id = s.program_id
          
          WHERE p.department = ?
            AND s.is_active = 1

           AND NOT EXISTS (
              SELECT 1
              FROM evaluation_status es
              WHERE es.student_id = s.student_id
                AND es.period_id = ?
          )

          ORDER BY s.full_name ASC
      ");

        $stmt->execute([
          $department,
          $periodId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Get students who never started a closed evaluation period.
     */
    public function getHistoricalNotEvaluatedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            ph.student_id,
            ph.full_name_at_time AS full_name,
            ph.program_name_at_time AS program_name

        FROM participation_history ph

        WHERE ph.period_id = ?
          AND ph.status = 'Never Started'
          AND ph.dept_at_time = ?

        ORDER BY ph.full_name_at_time ASC
    ");

        $stmt->execute([
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get abandoned students from an active evaluation period.
     */
    public function getLiveAbandonedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            s.student_id,
            s.full_name,
            s.email,
            p.program_name

        FROM students s

        INNER JOIN programs p
            ON p.program_id = s.program_id

        INNER JOIN evaluation_status es
            ON es.student_id = s.student_id
            AND es.period_id = ?

        WHERE p.department = ?
          AND s.is_active = 1

        GROUP BY
            s.student_id,
            s.full_name,
            s.email,
            p.program_name

        HAVING
            SUM(es.is_submitted = 1) < COUNT(es.eval_id)

        ORDER BY
            s.full_name ASC
        ");

        $stmt->execute([
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Get abandoned students from a closed evaluation period.
     */
    public function getHistoricalAbandonedList(
        int $periodId,
        string $department
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT
            ph.student_id,
            ph.full_name_at_time AS full_name,
            ph.program_name_at_time AS program_name

        FROM participation_history ph

        WHERE ph.period_id = ?
          AND ph.status = 'Abandoned'
          AND ph.dept_at_time = ?

        ORDER BY ph.full_name_at_time ASC
    ");

        $stmt->execute([
            $periodId,
            $department
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}
