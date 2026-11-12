<?php

declare(strict_types=1);

namespace App\Repositories\MonitoringRepo;

use PDO;

/**
 * Student participation for one department and evaluation period.
 *
 * Every method reads from progressQuery(), the single source of truth for the
 * required/completed counts and the derived status, so KPI cards, the
 * participation chart, filters and table rows can never disagree. Search,
 * filters, counting and pagination all run in MySQL.
 */
final class ParticipationMonitoringRepository
{
    public const STATUSES = ['Completed', 'Incomplete', 'Unresponsive'];

    public function __construct(private PDO $pdo)
    {
    }

    /** Status counts and unique students who submitted today, in one aggregate query. */
    public function getSummary(int $periodId, string $department): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS eligible,
                COALESCE(SUM(status = 'Completed'), 0) AS completed,
                COALESCE(SUM(status = 'Incomplete'), 0) AS incomplete,
                COALESCE(SUM(status = 'Unresponsive'), 0) AS unresponsive,
                COALESCE(SUM(submitted_today), 0) AS submitted_today
            FROM ({$this->progressQuery()}) progress
        ");
        $stmt->execute(['period_id' => $periodId, 'department' => $department]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'eligible' => (int) ($row['eligible'] ?? 0),
            'completed' => (int) ($row['completed'] ?? 0),
            'incomplete' => (int) ($row['incomplete'] ?? 0),
            'unresponsive' => (int) ($row['unresponsive'] ?? 0),
            'submitted_today' => (int) ($row['submitted_today'] ?? 0),
        ];
    }

    /** Year levels present among this department's eligible students. */
    public function getYearLevels(int $periodId, string $department): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT year_level
            FROM ({$this->progressQuery()}) progress
            WHERE TRIM(year_level) <> ''
        ");
        $stmt->execute(['period_id' => $periodId, 'department' => $department]);

        $levels = array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
        natcasesort($levels);

        return array_values($levels);
    }

    /**
     * One page of students matching the search/filters.
     *
     * @return array{records_filtered: int, students: list<array>}
     */
    public function getStudentPage(
        int $periodId,
        string $department,
        int $limit,
        int $offset,
        string $search = '',
        string $yearLevel = '',
        string $status = ''
    ): array {
        [$where, $filterParams] = $this->filters($search, $yearLevel, $status);
        $params = ['period_id' => $periodId, 'department' => $department] + $filterParams;

        $count = $this->pdo->prepare("SELECT COUNT(*) FROM ({$this->progressQuery()}) progress {$where}");
        $count->execute($params);
        $recordsFiltered = (int) $count->fetchColumn();

        if ($recordsFiltered === 0 || $offset >= $recordsFiltered) {
            return ['records_filtered' => $recordsFiltered, 'students' => []];
        }

        $stmt = $this->pdo->prepare("
            SELECT
                student_id,
                full_name,
                year_level,
                program_name,
                completed_count,
                submitted_today,
                status
            FROM ({$this->progressQuery()}) progress
            {$where}
            ORDER BY full_name ASC, student_id ASC
            LIMIT :limit OFFSET :offset
        ");
        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $students = array_map(static fn (array $row): array => [
            'student_id' => (string) $row['student_id'],
            'full_name' => (string) $row['full_name'],
            'year_level' => (string) $row['year_level'],
            'program_name' => (string) $row['program_name'],
            'completed_count' => (int) $row['completed_count'],
            'status' => (string) $row['status'],
            'submitted_today' => (bool) $row['submitted_today'],
        ], $stmt->fetchAll(PDO::FETCH_ASSOC));

        return ['records_filtered' => $recordsFiltered, 'students' => $students];
    }

    /**
     * One row per eligible student: every active student in the department.
     * Placeholders :department and :period_id are each used once (native
     * prepares forbid repeated names).
     *
     * Status rule (kept in sync with filters()):
     *   - Unresponsive: no evaluation submitted, including students with no
     *     evaluation_status rows yet (never opened the evaluation)
     *   - Completed:    submitted every required teacher evaluation
     *   - Incomplete:   submitted some, but not all
     *
     * completed_count (shown in the table) is the number of submitted teacher
     * evaluations. required_count is only the student's own evaluation_status
     * rows, used internally to tell Completed from Incomplete; it is never
     * estimated, and is NULL for students with no rows (who are Unresponsive).
     */
    private function progressQuery(): string
    {
        return "
            SELECT
                s.student_id,
                s.full_name,
                s.year_level,
                p.program_name,
                progress.required_count,
                COALESCE(progress.completed_count, 0) AS completed_count,
                COALESCE(progress.submitted_today, 0) AS submitted_today,
                CASE
                    WHEN COALESCE(progress.completed_count, 0) = 0 THEN 'Unresponsive'
                    WHEN progress.completed_count >= progress.required_count THEN 'Completed'
                    ELSE 'Incomplete'
                END AS status
            FROM students s
            INNER JOIN programs p
                ON p.program_id = s.program_id
                AND p.department = :department
            LEFT JOIN (
                SELECT
                    student_id,
                    COUNT(DISTINCT teacher_id) AS required_count,
                    COUNT(DISTINCT CASE WHEN is_submitted = 1 THEN teacher_id END) AS completed_count,
                    -- date_taken holds the first submission time (+08:00 DB session zone).
                    MAX(
                        is_submitted = 1
                        AND date_taken >= CURRENT_DATE()
                        AND date_taken < CURRENT_DATE() + INTERVAL 1 DAY
                    ) AS submitted_today
                FROM evaluation_status
                WHERE period_id = :period_id
                GROUP BY student_id
                HAVING COUNT(DISTINCT teacher_id) > 0
            ) progress
                ON progress.student_id = s.student_id
            WHERE s.is_active = 1
        ";
    }

    /** @return array{0: string, 1: array<string, string>} */
    private function filters(string $search, string $yearLevel, string $status): array
    {
        $conditions = [];
        $params = [];

        if ($search !== '') {
            $like = '%' . strtr($search, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $conditions[] = "(student_id LIKE :search_id ESCAPE '!' OR full_name LIKE :search_name ESCAPE '!')";
            $params['search_id'] = $like;
            $params['search_name'] = $like;
        }
        if ($yearLevel !== '') {
            $conditions[] = 'year_level = :year_level';
            $params['year_level'] = $yearLevel;
        }
        // Filter on the counts behind the status CASE (same rule, see progressQuery()),
        // not on its string result: comparing a computed string to a bound parameter
        // can raise "Illegal mix of collations" on MySQL 8 servers whose connection
        // collation differs from the tables' utf8mb4_general_ci.
        $statusConditions = [
            'Completed' => '(completed_count > 0 AND completed_count >= required_count)',
            'Incomplete' => '(completed_count > 0 AND completed_count < required_count)',
            'Unresponsive' => 'completed_count = 0',
        ];
        if ($status !== '') {
            if (!isset($statusConditions[$status])) {
                throw new \InvalidArgumentException('Invalid participation status.');
            }
            $conditions[] = $statusConditions[$status];
        }

        return [$conditions ? 'WHERE ' . implode(' AND ', $conditions) : '', $params];
    }
}
