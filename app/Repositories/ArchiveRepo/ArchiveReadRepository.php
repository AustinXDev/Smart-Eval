<?php

declare(strict_types=1);

namespace App\Repositories\ArchiveRepo;

use PDO;

final class ArchiveReadRepository
{
    public function __construct(
        private PDO $archivePdo
    ) {
    }

    // Retrieve archived evaluation periods
    public function getArchivedPeriods(): array
    {

        $stmt = $this->archivePdo->prepare("
            SELECT
                ep.period_id,
                ep.academic_year,
                ep.semester,
                ep.target_dept,
                ep.start_date,
                ep.end_date,
                ep.final_average,
                ep.total_responses,
                ep.participation_rate,
                ep.archived_at
            FROM evaluation_periods ep
            ORDER BY ep.archived_at DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    public function getLatestArchivedPeriod(
        string $department
    ): ?array {

        $sql = "
        SELECT ep.*
        FROM evaluation_periods ep
        WHERE ep.target_dept = :department
          AND ep.is_closed = 1
        ORDER BY ep.end_date DESC, ep.period_id DESC
        LIMIT 1
    ";

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'department' => $department
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }


    /**
     * Check whether an archived evaluation period exists.
     */
    public function isPeriodArchived(int $periodId): bool
    {
        $sql = "
        SELECT 1
        FROM evaluation_periods
        WHERE period_id = :period_id
          AND is_closed = 1
        LIMIT 1
    ";

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return (bool) $stmt->fetchColumn();
    }


    //Retrieve a specific archived evaluation period
    public function getArchivedPeriod(
        int $periodId
    ): array|false {

        $stmt = $this->archivePdo->prepare("
            SELECT *
            FROM evaluation_periods
            WHERE period_id = :period_id
                AND is_closed = 1
            LIMIT 1
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    // Retrieve archived teacher evaluations
    public function getTeacherEvaluations(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT
                ts.teacher_id,
                ts.employee_id,
                ts.full_name,
                ts.department,
                COUNT(DISTINCT es.eval_id) AS total_evaluations,
                ROUND(AVG(ea.score), 2) AS average_score
            FROM teacher_snapshots ts

            LEFT JOIN evaluation_status es
                ON es.teacher_id = ts.teacher_id
               AND es.period_id = ts.period_id
               AND es.is_submitted = 1

            LEFT JOIN evaluation_answers ea
                ON ea.eval_id = es.eval_id
               AND ea.period_id = ts.period_id

            WHERE ts.period_id = ?

            GROUP BY
                ts.teacher_id,
                ts.employee_id,
                ts.full_name,
                ts.department

            ORDER BY ts.full_name ASC
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve archived student participation summary
    public function getStudentParticipation(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT
                ss.student_pk,
                ss.student_id,
                ss.full_name,
                ss.year_level,
                ps.program_name,
                COUNT(DISTINCT es.eval_id) AS completed_evaluations
            FROM student_snapshots ss

            LEFT JOIN program_snapshots ps
                ON ps.program_id = ss.program_id
               AND ps.period_id = ss.period_id

            LEFT JOIN evaluation_status es
                ON es.student_pk = ss.student_pk
               AND es.period_id = ss.period_id
               AND es.is_submitted = 1

            WHERE ss.period_id = ?

            GROUP BY
                ss.student_pk,
                ss.student_id,
                ss.full_name,
                ss.year_level,
                ps.program_name

            ORDER BY ss.full_name ASC
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /** Management reads use the archive connection only; no cross-database joins. */
    public function managementOverview(): array
    {
        $stats = $this->archivePdo->query("
            SELECT
                (SELECT COUNT(*) FROM evaluation_periods) AS archived_periods,
                (SELECT COUNT(*) FROM archive_jobs WHERE status = 'verified') AS verified_backups,
                (SELECT COUNT(*) FROM evaluation_periods ep
                 LEFT JOIN archive_jobs aj ON aj.period_id = ep.period_id
                 WHERE aj.archive_id IS NULL OR aj.status = 'failed'
                    OR aj.cleanup_status = 'needs_reconciliation') AS archive_issues
        ")->fetch(PDO::FETCH_ASSOC);

        return array_map('intval', $stats) + ['recovery_operations' => (int) $this->archivePdo->query('SELECT COUNT(*) FROM archive_restore_operations')->fetchColumn()];
    }


    public function managementOptions(): array
    {

        return [
            'years' => $this->archivePdo->query('SELECT DISTINCT academic_year FROM evaluation_periods ORDER BY academic_year DESC')->fetchAll(PDO::FETCH_COLUMN),
            'semesters' => $this->archivePdo->query('SELECT DISTINCT semester FROM evaluation_periods ORDER BY semester')->fetchAll(PDO::FETCH_COLUMN),
            'periods' => $this->archivePdo->query('SELECT period_id, academic_year, semester, target_dept FROM evaluation_periods ORDER BY period_id DESC')->fetchAll(PDO::FETCH_ASSOC),
        ];
    }


    public function managementList(
        array $filters,
        int $start,
        int $length
    ): array {
        [$where, $params] = $this->managementWhere($filters);

        $from = ' FROM evaluation_periods ep LEFT JOIN archive_jobs aj ON aj.period_id = ep.period_id ';

        $stmt = $this->archivePdo->prepare('SELECT COUNT(*)' . $from . $where);

        $stmt->execute($params);

        $filtered = (int) $stmt->fetchColumn();

        $stmt = $this->archivePdo->prepare(
            "
            SELECT 
                ep.period_id, 
                ep.academic_year, 
                ep.semester, 
                ep.target_dept, 
                ep.archived_at,
                aj.status AS backup_status, 
                aj.cleanup_status,
                   (SELECT COUNT(*) 
                    FROM student_snapshots s 
                    WHERE s.period_id = ep.period_id) AS students,
                   (SELECT COUNT(*) 
                   FROM teacher_snapshots t 
                   WHERE t.period_id = ep.period_id) AS teachers,
                   (SELECT COUNT(*) 
                   FROM evaluation_status e WHERE e.period_id = ep.period_id AND e.is_submitted = 1) AS submissions
            " . $from . $where . " ORDER BY ep.archived_at DESC, ep.period_id DESC LIMIT ? OFFSET ?"
        );

        $i = 1;

        foreach ($params as $value) {
            $stmt->bindValue($i++, $value);
        }

        $stmt->bindValue($i++, $length, PDO::PARAM_INT);

        $stmt->bindValue($i, $start, PDO::PARAM_INT);

        $stmt->execute();

        return [
            'recordsTotal' => (int) $this->archivePdo->query('SELECT COUNT(*) FROM evaluation_periods')->fetchColumn(),
            'recordsFiltered' => $filtered,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];

    }

    private function managementWhere(
        array $filters
    ): array {

        $conditions = [];
        $params = [];

        foreach (['department' => 'ep.target_dept', 'year' => 'ep.academic_year', 'semester' => 'ep.semester'] as $key => $column) {

            if (($filters[$key] ?? '') !== '') {
                $conditions[] = "$column = ?";
                $params[] = $filters[$key];
            }

        }

        if (($filters['backup_status'] ?? '') === 'missing') {

            $conditions[] = 'aj.archive_id IS NULL';

        } elseif (($filters['backup_status'] ?? '') !== '') {

            $conditions[] = 'aj.status = ?';
            $params[] = $filters['backup_status'];

        }

        if (($filters['search'] ?? '') !== '') {

            $conditions[] = '(ep.academic_year LIKE ? OR CAST(ep.period_id AS CHAR) LIKE ?)';
            $params[] = '%' . $filters['search'] . '%';
            $params[] = '%' . $filters['search'] . '%';

        }

        return [$conditions ? ' WHERE ' . implode(' AND ', $conditions) : '', $params];
    }

    public function managementDetails(int $periodId): ?array
    {
        $stmt = $this->archivePdo->prepare(
            "
            SELECT 
                ep.period_id, 
                ep.academic_year, 
                ep.semester, 
                ep.target_dept,
                ep.start_date, 
                ep.end_date, 
                ep.archived_at, 
                ep.is_closed,
                aj.status AS backup_status, 
                aj.cleanup_status, 
                aj.cleaned_at,
                aj.archived_at AS backup_verified_at
            FROM evaluation_periods ep 

            LEFT JOIN archive_jobs aj 
                ON aj.period_id = ep.period_id
            WHERE ep.period_id = ?"
        );

        $stmt->execute([$periodId]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$period) {
            return null;
        }

        $counts = [];

        foreach (['student_snapshots', 'teacher_snapshots', 'evaluation_status', 'evaluation_answers', 'participation_history'] as $table) {
            $stmt = $this->archivePdo->prepare("SELECT COUNT(*) FROM `$table` WHERE period_id = ?");
            $stmt->execute([$periodId]);
            $counts[$table] = (int) $stmt->fetchColumn();
        }

        $stmt = $this->archivePdo->prepare('SELECT COUNT(*) FROM evaluation_status WHERE period_id = ? AND is_submitted = 1');

        $stmt->execute([$periodId]);

        $counts['submissions'] = (int) $stmt->fetchColumn();

        return $period + ['counts' => $counts, 'backup_filename' => null, 'backup_size' => null];

    }

    public function recoveryLogs(
        array $filters,
        int $start,
        int $length
    ): array {
        $conditions = [];
        $params = [];

        foreach (['period_id' => 'r.period_id', 'department' => 'ep.target_dept'] as $key => $column) {
            if (($filters[$key] ?? '') !== '') {
                $conditions[] = "$column = ?";
                $params[] = $filters[$key];
            }
        }

        if (($filters['search'] ?? '') !== '') {
            $conditions[] = '(r.status LIKE ? OR r.source LIKE ? OR CAST(r.period_id AS CHAR) LIKE ?)';
            array_push($params, ...array_fill(0, 3, '%' . $filters['search'] . '%'));
        }

        $from = ' FROM archive_restore_operations r LEFT JOIN evaluation_periods ep ON ep.period_id = r.period_id';

        $where = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

        $stmt = $this->archivePdo->prepare('SELECT COUNT(*)' . $from . $where);

        $stmt->execute($params);

        $filtered = (int) $stmt->fetchColumn();

        $stmt = $this->archivePdo->prepare('SELECT r.*, ep.academic_year, ep.semester, ep.target_dept' . $from . $where
            . ' ORDER BY r.operation_id DESC LIMIT ? OFFSET ?');
        $i = 1;

        foreach ($params as $value) {
            $stmt->bindValue($i++, $value);
        }

        $stmt->bindValue($i++, $length, PDO::PARAM_INT);

        $stmt->bindValue($i, $start, PDO::PARAM_INT);

        $stmt->execute();

        return [
            'recordsTotal' => (int) $this->archivePdo->query('SELECT COUNT(*) FROM archive_restore_operations')->fetchColumn(),
            'recordsFiltered' => $filtered,
            'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
        ];

    }

    public function recoveryLogDetails(int $id): ?array
    {
        $stmt = $this->archivePdo->prepare('
            SELECT 
                r.*, 
                ep.academic_year, 
                ep.semester, 
                ep.target_dept
            FROM archive_restore_operations r 
            LEFT JOIN evaluation_periods ep 
                ON ep.period_id = r.period_id
            WHERE r.operation_id = ?');

        $stmt->execute([$id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function backupSchema(): array
    {
        $schema = [];
        foreach (\App\Services\BackupServices\DatabaseExportService::TABLES as $table) {
            $schema[$table] = [
                'columns' => $this->archivePdo->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC),
                'indexes' => $this->archivePdo->query("SHOW INDEX FROM `$table`")->fetchAll(PDO::FETCH_ASSOC),
            ];
        }
        return $schema;
    }

    public function periodExists(int $periodId): bool
    {
        $stmt = $this->archivePdo->prepare('SELECT 1 FROM evaluation_periods WHERE period_id = ?');
        $stmt->execute([$periodId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getRecoveryEligibility(
        int $periodId,
        string $checksum
    ): array {
        $stmt = $this->archivePdo->prepare("
        SELECT
            status,
            archive_data_status,
            checksum_sha256
        FROM archive_jobs
        WHERE period_id = :period_id
        LIMIT 1
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($job === false) {
            return [
                'eligible' => false,
                'reason' => 'No registered archive backup was found.'
            ];
        }

        if ($job['status'] !== 'verified') {
            return [
                'eligible' => false,
                'reason' => 'The registered backup is not verified.'
            ];
        }

        if ($job['archive_data_status'] !== 'deleted') {
            return [
                'eligible' => false,
                'reason' => 'Archive records have not been marked as deleted.'
            ];
        }

        $storedChecksum = $job['checksum_sha256'];

        if (
            !is_string($storedChecksum) ||
            !preg_match('/^[a-f0-9]{64}$/i', $storedChecksum) ||
            !hash_equals(
                strtolower($storedChecksum),
                strtolower($checksum)
            )
        ) {
            return [
                'eligible' => false,
                'reason' => 'Uploaded backup does not match the registered backup.'
            ];
        }

        return [
            'eligible' => true,
            'reason' => 'Backup verified and eligible for restoration.'
        ];
    }

}
