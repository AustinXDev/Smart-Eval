<?php

declare(strict_types=1);

namespace App\Repositories\DashboardRepo;

use PDO;

/**
 * Dashboard-only queries. Other dashboard figures reuse existing repositories
 * (students, teachers, evaluation status, participation monitoring, analytics).
 */
final class DashboardRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * Submitted teacher evaluations per day for a period, scoped exactly like
     * EvaluationStatusRepository::countTotalSubmitted() (active students in the
     * department), so the trend adds up to the Submitted Evaluations card.
     * date_taken is the first submission time (+08:00 DB session time zone).
     *
     * @return array<string, int> ['Y-m-d' => count]
     */
    public function getDailySubmissions(int $periodId, string $department): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DATE(es.date_taken) AS day, COUNT(*) AS total
            FROM evaluation_status es
            INNER JOIN students s
                ON s.student_id = es.student_id
                AND s.is_active = 1
            INNER JOIN programs p
                ON p.program_id = s.program_id
                AND p.department = :department
            WHERE es.period_id = :period_id
                AND es.is_submitted = 1
            GROUP BY DATE(es.date_taken)
            ORDER BY day
        ");
        $stmt->execute(['period_id' => $periodId, 'department' => $department]);

        $days = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $days[(string) $row['day']] = (int) $row['total'];
        }

        return $days;
    }

    /**
     * Latest activity in the department, built only from real record timestamps
     * (there is no activity-log table):
     *   - submission : a student's submitted evaluations, grouped per student per
     *                  day (no teacher names or answers, to keep evaluations anonymous)
     *   - student    : students.created_at (registration)
     *   - opened     : evaluation_periods.start_date that has passed
     *   - closed     : evaluation_periods.end_date of a closed period
     * Teachers have no created_at column and periods have no creation/status
     * history, so those events cannot be listed without inventing data.
     */
    public function getRecentActivities(string $department, int $limit = 8): array
    {
        $stmt = $this->pdo->prepare("
            SELECT type, title, detail, occurred_at, total
            FROM (
                SELECT
                    'submission' AS type,
                    s.full_name AS title,
                    p.program_code AS detail,
                    MAX(es.date_taken) AS occurred_at,
                    COUNT(*) AS total
                FROM evaluation_status es
                INNER JOIN students s
                    ON s.student_id = es.student_id
                INNER JOIN programs p
                    ON p.program_id = s.program_id
                    AND p.department = :dept_submission
                WHERE es.is_submitted = 1
                GROUP BY es.student_id, s.full_name, p.program_code, DATE(es.date_taken)

                UNION ALL

                SELECT
                    'student', s.full_name, p.program_code, s.created_at, 1
                FROM students s
                INNER JOIN programs p
                    ON p.program_id = s.program_id
                    AND p.department = :dept_student

                UNION ALL

                SELECT
                    'opened', ep.academic_year, ep.semester, ep.start_date, 1
                FROM evaluation_periods ep
                WHERE ep.target_dept = :dept_opened
                    AND ep.start_date <= NOW()

                UNION ALL

                SELECT
                    'closed', ep.academic_year, ep.semester, ep.end_date, 1
                FROM evaluation_periods ep
                WHERE ep.target_dept = :dept_closed
                    AND ep.is_closed = 1
            ) activity
            WHERE occurred_at IS NOT NULL
            ORDER BY occurred_at DESC
            LIMIT :limit
        ");
        foreach (['dept_submission', 'dept_student', 'dept_opened', 'dept_closed'] as $key) {
            $stmt->bindValue(':' . $key, $department, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(static fn (array $row): array => [
            'type' => (string) $row['type'],
            'title' => (string) $row['title'],
            'detail' => (string) $row['detail'],
            'occurred_at' => (string) $row['occurred_at'],
            'total' => (int) $row['total'],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
