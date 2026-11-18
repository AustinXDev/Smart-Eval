<?php

declare(strict_types=1);

namespace App\Repositories\LogRepo;

use PDO;

/**
 * Storage for activity_logs (scripts/sql/2026_11_18_create_activity_logs.sql)
 * plus the small lookups the logger uses to turn IDs into readable labels.
 */
class ActivityLogRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    // ------------------------------------------------------------------
    // Write / read
    // ------------------------------------------------------------------

    /**
     * @param array{
     *   actor_type:string, actor_id:?string, actor_name:?string, actor_role:?string,
     *   module:string, action:string, description:string,
     *   target_type:?string, target_id:?string, department:?string,
     *   metadata:?string, ip_address:?string, user_agent:?string
     * } $entry
     */
    public function create(array $entry): void
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO activity_logs (
                actor_type, actor_id, actor_name, actor_role,
                module, action, description,
                target_type, target_id, department,
                metadata, ip_address, user_agent
            ) VALUES (
                :actor_type, :actor_id, :actor_name, :actor_role,
                :module, :action, :description,
                :target_type, :target_id, :department,
                :metadata, :ip_address, :user_agent
            )
        ');

        $stmt->execute([
            'actor_type'  => $entry['actor_type'],
            'actor_id'    => $entry['actor_id'],
            'actor_name'  => $entry['actor_name'],
            'actor_role'  => $entry['actor_role'],
            'module'      => $entry['module'],
            'action'      => $entry['action'],
            'description' => $entry['description'],
            'target_type' => $entry['target_type'],
            'target_id'   => $entry['target_id'],
            'department'  => $entry['department'],
            'metadata'    => $entry['metadata'],
            'ip_address'  => $entry['ip_address'],
            'user_agent'  => $entry['user_agent'],
        ]);
    }

    /**
     * Newest-first page of log entries, for an activity log view.
     *
     * @param array{module?:string, action?:string, actor_type?:string, department?:string,
     *              search?:string, from?:string, to?:string} $filters
     * @return array{recordsTotal:int, recordsFiltered:int, data:array<int,array<string,mixed>>}
     */
    public function findPage(int $offset, int $limit, array $filters = []): array
    {
        $where = ' WHERE 1=1';
        $params = [];

        foreach (['module', 'action', 'actor_type', 'department'] as $key) {
            if (!empty($filters[$key])) {
                $where .= " AND {$key} = :{$key}";
                $params[$key] = (string) $filters[$key];
            }
        }

        if (!empty($filters['from'])) {
            $where .= ' AND created_at >= :from';
            $params['from'] = (string) $filters['from'];
        }

        if (!empty($filters['to'])) {
            $where .= ' AND created_at < DATE_ADD(:to, INTERVAL 1 DAY)';
            $params['to'] = (string) $filters['to'];
        }

        if (!empty($filters['search'])) {
            $pattern = '%' . strtr((string) $filters['search'], ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $where .= " AND (description LIKE :s1 ESCAPE '!' OR actor_name LIKE :s2 ESCAPE '!' OR actor_id LIKE :s3 ESCAPE '!' OR target_id LIKE :s4 ESCAPE '!')";
            $params['s1'] = $params['s2'] = $params['s3'] = $params['s4'] = $pattern;
        }

        $total = (int) $this->pdo->query('SELECT COUNT(*) FROM activity_logs')->fetchColumn();

        $count = $this->pdo->prepare('SELECT COUNT(*) FROM activity_logs' . $where);
        $count->execute($params);

        $stmt = $this->pdo->prepare(
            'SELECT log_id, actor_type, actor_id, actor_name, actor_role, module, action, description,
                    target_type, target_id, department, metadata, ip_address, created_at
             FROM activity_logs' . $where . '
             ORDER BY created_at DESC, log_id DESC
             LIMIT :limit OFFSET :offset'
        );

        foreach ($params as $key => $value) {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'recordsTotal' => $total,
            'recordsFiltered' => (int) $count->fetchColumn(),
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];
    }

    /**
     * Entries per actor type (admin / student / system) for the same filters,
     * ignoring the actor_type filter itself — used for the page's tab counts.
     *
     * @return array{admin:int, student:int, system:int}
     */
    public function countByActorType(array $filters = []): array
    {
        unset($filters['actor_type']);

        $counts = ['admin' => 0, 'student' => 0, 'system' => 0];

        foreach (array_keys($counts) as $type) {
            $page = $this->findPage(0, 1, $filters + ['actor_type' => $type]);
            $counts[$type] = $page['recordsFiltered'];
        }

        return $counts;
    }

    /**
     * Modules that have entries for an actor type (for the module filter).
     *
     * @return list<string>
     */
    public function modulesFor(string $actorType): array
    {
        $stmt = $this->pdo->prepare('SELECT DISTINCT module FROM activity_logs WHERE actor_type = ? ORDER BY module');
        $stmt->execute([$actorType]);

        return array_map('strval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    // ------------------------------------------------------------------
    // Label lookups (read-only). Each returns [] when nothing matches.
    // ------------------------------------------------------------------

    public function findAdmin(int $adminId): array
    {
        return $this->row('SELECT admin_id, username, full_name, role FROM admins WHERE admin_id = ?', [$adminId]);
    }

    public function findAdminByUsername(string $username): array
    {
        return $this->row('SELECT admin_id, username, full_name, role FROM admins WHERE username = ? LIMIT 1', [$username]);
    }

    public function findStudent(string $studentId): array
    {
        return $this->row('
            SELECT s.student_id, s.full_name, p.department
            FROM students s
            LEFT JOIN programs p ON p.program_id = s.program_id
            WHERE s.student_id = ?
            LIMIT 1', [$studentId]);
    }

    public function findStudentByEmail(string $email): array
    {
        return $this->row('
            SELECT s.student_id, s.full_name, p.department
            FROM students s
            LEFT JOIN programs p ON p.program_id = s.program_id
            WHERE s.email = ?
            LIMIT 1', [$email]);
    }

    public function findStudentByActivationToken(string $tokenHash): array
    {
        return $this->row('
            SELECT s.student_id, s.full_name, p.department
            FROM students s
            LEFT JOIN programs p ON p.program_id = s.program_id
            WHERE s.activation_token = ?
            LIMIT 1', [$tokenHash]);
    }

    public function findStudentByResetToken(string $tokenHash): array
    {
        return $this->row('
            SELECT s.student_id, s.full_name, p.department
            FROM password_resets r
            INNER JOIN students s ON s.student_id = r.student_id
            LEFT JOIN programs p ON p.program_id = s.program_id
            WHERE r.token = ?
            ORDER BY r.id DESC
            LIMIT 1', [$tokenHash]);
    }

    public function findAdminByResetToken(string $tokenHash): array
    {
        return $this->row('
            SELECT a.admin_id, a.username, a.full_name, a.role
            FROM admin_password_resets r
            INNER JOIN admins a ON a.admin_id = r.admin_id
            WHERE r.token = ?
            ORDER BY r.id DESC
            LIMIT 1', [$tokenHash]);
    }

    public function findTeacher(int $teacherId): array
    {
        return $this->row('SELECT teacher_id, full_name, employee_id, department FROM teachers WHERE teacher_id = ?', [$teacherId]);
    }

    public function findProgram(int $programId): array
    {
        return $this->row('SELECT program_id, program_code, program_name, department FROM programs WHERE program_id = ?', [$programId]);
    }

    public function findTeacherLoad(int $loadId): array
    {
        return $this->row('
            SELECT tl.load_id, tl.year_level, p.program_code, p.program_name, p.department
            FROM teacher_load tl
            INNER JOIN programs p ON p.program_id = tl.program_id
            WHERE tl.load_id = ?', [$loadId]);
    }

    public function findQuestionSet(int $setId): array
    {
        return $this->row('SELECT set_id, set_name FROM question_sets WHERE set_id = ?', [$setId]);
    }

    public function findQuestion(int $questionId): array
    {
        return $this->row('
            SELECT q.question_id, q.question_text, q.set_id, qs.set_name
            FROM questions q
            LEFT JOIN question_sets qs ON qs.set_id = q.set_id
            WHERE q.question_id = ?', [$questionId]);
    }

    public function findPeriod(int $periodId): array
    {
        return $this->row('
            SELECT period_id, academic_year, semester, target_dept AS department
            FROM evaluation_periods
            WHERE period_id = ?', [$periodId]);
    }

    /**
     * Evaluation progress for a student's most recent period: totals only.
     * Deliberately returns no teacher, load, score or comment data.
     *
     * @return array{period_id:?int, required:int, submitted:int}
     */
    public function studentSubmissionProgress(string $studentId): array
    {
        $row = $this->row('
            SELECT es.period_id,
                   COUNT(*) AS required,
                   COALESCE(SUM(CASE WHEN es.is_submitted = 1 THEN 1 ELSE 0 END), 0) AS submitted
            FROM evaluation_status es
            WHERE es.student_id = ?
              AND es.period_id = (
                  SELECT MAX(period_id) FROM evaluation_status WHERE student_id = ?
              )
            GROUP BY es.period_id', [$studentId, $studentId]);

        return [
            'period_id' => isset($row['period_id']) ? (int) $row['period_id'] : null,
            'required' => (int) ($row['required'] ?? 0),
            'submitted' => (int) ($row['submitted'] ?? 0),
        ];
    }

    private function row(string $sql, array $params): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    }
}
