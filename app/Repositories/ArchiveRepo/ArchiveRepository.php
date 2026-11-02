<?php

declare(strict_types=1);

namespace App\Repositories\ArchiveRepo;

use PDO;
use RuntimeException;

final class ArchiveRepository
{
    public function __construct(
        private PDO $livePdo,
        private PDO $archivePdo,
        private string $liveDatabase = 'dbsmarteval',
        private string $archiveDatabase = 'dbsmarteval_archive'
    ) {

        foreach ([$this->liveDatabase, $this->archiveDatabase] as $database) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $database)) {
                throw new RuntimeException('Invalid database name.');
            }
        }

    }

    public function getLiveDatabase(): string
    {
        return $this->liveDatabase;
    }

    public function getArchiveDatabase(): string
    {
        return $this->archiveDatabase;
    }


    // find close period to archive
    public function findEligiblePeriods(): array
    {

        $sql = "
            SELECT
            ep.period_id,
            ep.end_date
            FROM evaluation_periods ep
            WHERE ep.is_closed = 1
            AND ep.end_date <= NOW()
            AND NOT EXISTS (
                SELECT 1
                FROM `{$this->archiveDatabase}`.archive_jobs aj
                WHERE aj.period_id = ep.period_id
                    AND aj.status = 'verified'
                    AND aj.cleanup_status = 'completed'
                    AND aj.cleaned_at IS NOT NULL
            )
            ORDER BY ep.end_date ASC
        ";

        $stmt = $this->livePdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    // Get a specific evaluation period
    public function findPeriodById(
        int $periodId
    ): ?array {

        $stmt = $this->livePdo->prepare("
            SELECT period_id, end_date, is_closed
            FROM evaluation_periods
            WHERE period_id = :period_id
            LIMIT 1
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

    }


    public function findArchiveJob(int $periodId): ?array
    {
        $stmt = $this->archivePdo->prepare("
        SELECT *
        FROM archive_jobs
        WHERE period_id = :period_id
        LIMIT 1
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }


    // Retrieve teacher notification job statistics.
    public function getTeacherNotificationStats(
        int $periodId
    ): array {

        $stmt = $this->livePdo->prepare("
            SELECT
                COUNT(*) AS total_jobs,

                COALESCE(SUM(status = 'pending'), 0)
                    AS pending_jobs,

                COALESCE(SUM(status = 'processing'), 0)
                    AS processing_jobs,

                COALESCE(SUM(
                    status = 'failed' AND attempts < 3
                ), 0) AS retryable_failed_jobs,

                COALESCE(SUM(
                    status = 'failed' AND attempts >= 3
                ), 0) AS exhausted_failed_jobs,

                COALESCE(SUM(status = 'sent'), 0)
                    AS sent_jobs,

                COALESCE(SUM(status = 'skipped'), 0)
                    AS skipped_jobs

            FROM teacher_notification_queue
            WHERE period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function archiveExists(int $periodId): bool
    {
        $stmt = $this->livePdo->prepare("
            SELECT 1
            FROM `{$this->archiveDatabase}`.evaluation_periods
            WHERE period_id = ?
            LIMIT 1
        ");

        $stmt->execute([$periodId]);

        return (bool) $stmt->fetchColumn();
    }

    public function copyPeriod(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
            INSERT INTO `{$this->archiveDatabase}`.evaluation_periods (
                period_id,
                academic_year,
                semester,
                target_dept,
                set_id,
                start_date,
                end_date,
                is_active,
                is_forced,
                is_closed,
                final_average,
                total_responses,
                total_unresponsive_students,
                total_incomplete_students,
                participation_rate,
                last_notified_at
            )
            SELECT
                period_id,
                academic_year,
                semester,
                target_dept,
                set_id,
                start_date,
                end_date,
                is_active,
                is_forced,
                is_closed,
                final_average,
                total_responses,
                total_unresponsive_students,
                total_incomplete_students,
                participation_rate,
                last_notified_at
            FROM `{$this->liveDatabase}`.evaluation_periods
            WHERE period_id = ?
              AND is_closed = 1
        ");

        $stmt->execute([$periodId]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                "Unable to archive evaluation period."
            );
        }
    }

    public function copyQuestionSet(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
            INSERT INTO `{$this->archiveDatabase}`.question_set_snapshots (
                period_id,
                set_id,
                set_name
            )
            SELECT
                ep.period_id,
                qs.set_id,
                qs.set_name
            FROM `{$this->liveDatabase}`.evaluation_periods ep
            INNER JOIN `{$this->liveDatabase}`.question_sets qs
                ON qs.set_id = ep.set_id
            WHERE ep.period_id = ?
        ");

        $stmt->execute([$periodId]);
    }


    public function copyQuestions(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
            INSERT INTO `{$this->archiveDatabase}`.question_snapshots (
                period_id,
                question_id,
                set_id,
                question_text,
                category,
                is_active
            )
            SELECT
                ep.period_id,
                q.question_id,
                q.set_id,
                q.question_text,
                q.category,
                q.is_active
            FROM `{$this->liveDatabase}`.evaluation_periods ep
            INNER JOIN `{$this->liveDatabase}`.questions q
                ON q.set_id = ep.set_id
            WHERE ep.period_id = ?
        ");

        $stmt->execute([$periodId]);
    }


    public function copyTeachers(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.teacher_snapshots (
            period_id,
            teacher_id,
            employee_id,
            full_name,
            department
        )
        SELECT
            ep.period_id,
            t.teacher_id,
            t.employee_id,
            t.full_name,
            t.department
        FROM `{$this->liveDatabase}`.teachers t
        INNER JOIN `{$this->liveDatabase}`.evaluation_periods ep
            ON ep.period_id = ?
        WHERE
            t.department = ep.target_dept

            OR EXISTS (
                SELECT 1
                FROM `{$this->liveDatabase}`.evaluation_status es
                WHERE es.teacher_id = t.teacher_id
                  AND es.period_id = ep.period_id
            )

            OR EXISTS (
                SELECT 1
                FROM `{$this->liveDatabase}`.teacher_notification_queue tn
                WHERE tn.teacher_id = t.teacher_id
                  AND tn.period_id = ep.period_id
            )
    ");

        $stmt->execute([$periodId]);
    }


    public function copyStudents(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.student_snapshots (
            period_id,
            student_pk,
            student_id,
            full_name,
            email,
            program_id,
            year_level,
            enrollment_type
        )
        SELECT
            ep.period_id,
            s.id,
            s.student_id,
            s.full_name,
            s.email,
            s.program_id,
            s.year_level,
            s.enrollment_type
        FROM `{$this->liveDatabase}`.students s

        INNER JOIN `{$this->liveDatabase}`.evaluation_periods ep
            ON ep.period_id = ?

        LEFT JOIN `{$this->liveDatabase}`.programs p
            ON p.program_id = s.program_id

        WHERE
            p.department = ep.target_dept

            OR EXISTS (
                SELECT 1
                FROM `{$this->liveDatabase}`.evaluation_status es
                WHERE es.student_id = s.student_id
                  AND es.period_id = ep.period_id
            )

            OR EXISTS (
                SELECT 1
                FROM `{$this->liveDatabase}`.participation_history ph
                WHERE ph.student_id = s.student_id
                  AND ph.period_id = ep.period_id
            )
    ");

        $stmt->execute([$periodId]);
    }


    public function copyPrograms(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.program_snapshots (
            period_id,
            program_id,
            program_code,
            program_name,
            department
        )
        SELECT
            ep.period_id,
            p.program_id,
            p.program_code,
            p.program_name,
            p.department
        FROM `{$this->liveDatabase}`.programs p

        INNER JOIN `{$this->liveDatabase}`.evaluation_periods ep
            ON ep.period_id = ?

        WHERE
            p.department = ep.target_dept

            OR EXISTS (
                SELECT 1
                FROM `{$this->archiveDatabase}`.student_snapshots ss
                WHERE ss.program_id = p.program_id
                  AND ss.period_id = ep.period_id
            )
    ");

        $stmt->execute([$periodId]);
    }


    public function copyEvaluations(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
            INSERT INTO `{$this->archiveDatabase}`.evaluation_status (
                eval_id,
                student_id,
                student_pk,
                teacher_id,
                period_id,
                is_submitted,
                date_taken,
                comment
            )
            SELECT
                es.eval_id,
                es.student_id,
                s.id,
                es.teacher_id,
                es.period_id,
                es.is_submitted,
                es.date_taken,
                es.comment
            FROM `{$this->liveDatabase}`.evaluation_status es
            LEFT JOIN `{$this->liveDatabase}`.students s
                ON s.student_id = es.student_id
            WHERE es.period_id = ?
        ");

        $stmt->execute([$periodId]);
    }

    public function copyAnswers(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
            INSERT INTO `{$this->archiveDatabase}`.evaluation_answers (
                answer_id,
                eval_id,
                question_id,
                score,
                period_id
            )
            SELECT
                ea.answer_id,
                ea.eval_id,
                ea.question_id,
                ea.score,
                es.period_id
            FROM `{$this->liveDatabase}`.evaluation_answers ea
            INNER JOIN `{$this->liveDatabase}`.evaluation_status es
                ON es.eval_id = ea.eval_id
            WHERE es.period_id = ?
        ");

        $stmt->execute([$periodId]);
    }


    // Copy participation history
    public function copyParticipationHistory(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.participation_history (
            history_id,
            period_id,
            student_id,
            full_name_at_time,
            year_level_at_time,
            dept_at_time,
            program_name_at_time,
            status,
            archived_at
        )
        SELECT
            history_id,
            period_id,
            student_id,
            full_name_at_time,
            year_level_at_time,
            dept_at_time,
            program_name_at_time,
            status,
            archived_at
        FROM `{$this->liveDatabase}`.participation_history
        WHERE period_id = ?
    ");

        $stmt->execute([$periodId]);
    }


    // Copy student notification history
    public function copyNotifications(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.notification_queue (
            notification_id,
            period_id,
            student_id,
            type,
            channel,
            status,
            attempt_count,
            sent_at,
            failed_at,
            error_message,
            created_at,
            updated_at,
            available_at,
            processing_started_at,
            claim_token
        )
        SELECT
            notification_id,
            period_id,
            student_id,
            type,
            channel,
            status,
            attempt_count,
            sent_at,
            failed_at,
            error_message,
            created_at,
            updated_at,
            available_at,
            processing_started_at,
            claim_token
        FROM `{$this->liveDatabase}`.notification_queue
        WHERE period_id = ?
    ");

        $stmt->execute([$periodId]);
    }


    // Copy teacher notification history
    public function copyTeacherNotifications(int $periodId): void
    {
        $stmt = $this->livePdo->prepare("
        INSERT INTO `{$this->archiveDatabase}`.teacher_notification_queue (
            queue_id,
            teacher_id,
            period_id,
            status,
            last_error,
            created_at,
            processed_at,
            attempts,
            next_attempt_at,
            claimed_at,
            claim_token
        )
        SELECT
            queue_id,
            teacher_id,
            period_id,
            status,
            last_error,
            created_at,
            processed_at,
            attempts,
            next_attempt_at,
            claimed_at,
            claim_token
        FROM `{$this->liveDatabase}`.teacher_notification_queue
        WHERE period_id = ?
    ");

        $stmt->execute([$periodId]);
    }


    /**
     * Count evaluation-related records for a specific period
     * from either the live or archive database.
     *
     * @throws RuntimeException
     */
    public function countPeriodRecords(
        string $database,
        string $table,
        int $periodId
    ): int {

        // Allowed tables for each database
        $allowedTables = [
            $this->liveDatabase => [
                'evaluation_periods',
                'evaluation_status',
                'evaluation_answers',
                'participation_history',
                'notification_queue',
                'teacher_notification_queue',
            ],

            $this->archiveDatabase => [
                'evaluation_periods',
                'evaluation_status',
                'evaluation_answers',
                'participation_history',
                'notification_queue',
                'teacher_notification_queue',
                'student_snapshots',
                'teacher_snapshots',
                'program_snapshots',
                'question_set_snapshots',
                'question_snapshots',
            ],
        ];

        // Validate period ID
        if ($periodId <= 0) {
            throw new RuntimeException(
                "Invalid evaluation period ID."
            );
        }

        // Validate database and table combination
        if (
            !isset($allowedTables[$database]) ||
            !in_array(
                $table,
                $allowedTables[$database],
                true
            )
        ) {
            throw new RuntimeException(
                "Invalid database or table combination: {$database}.{$table}"
            );
        }

        // Special handling for live evaluation answers
        if (
            $database === $this->liveDatabase &&
            $table === 'evaluation_answers'
        ) {

            $sql = "
            SELECT COUNT(*)
            FROM `{$this->liveDatabase}`.`evaluation_answers` ea
            INNER JOIN `{$this->liveDatabase}`.`evaluation_status` es
                ON es.eval_id = ea.eval_id
            WHERE es.period_id = :period_id
        ";

        } else {

            // Database and table names are validated above
            $sql = "
            SELECT COUNT(*)
            FROM `{$database}`.`{$table}`
            WHERE period_id = :period_id
        ";
        }

        $stmt = $this->livePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return (int) $stmt->fetchColumn();
    }


    public function countEvaluationStatusDifferences(int $periodId): int
    {
        $sql = "
        SELECT COUNT(*)
            FROM `{$this->liveDatabase}`.evaluation_status live
            LEFT JOIN `{$this->archiveDatabase}`.evaluation_status archived
                ON archived.eval_id = live.eval_id
            WHERE live.period_id = :period_id
            AND (
                archived.eval_id IS NULL
                OR NOT (live.student_id <=> archived.student_id)
                OR NOT (live.teacher_id <=> archived.teacher_id)
                OR NOT (live.period_id <=> archived.period_id)
                OR NOT (live.is_submitted <=> archived.is_submitted)
                OR NOT (live.date_taken <=> archived.date_taken)
                OR NOT (live.comment <=> archived.comment)
            )
    ";

        $stmt = $this->livePdo->prepare($sql);
        $stmt->execute(['period_id' => $periodId]);

        return (int) $stmt->fetchColumn();
    }

    public function countEvaluationAnswerDifferences(int $periodId): int
    {
        $sql = "
        SELECT COUNT(*)
        FROM `{$this->liveDatabase}`.evaluation_answers live
        INNER JOIN `{$this->liveDatabase}`.evaluation_status es
            ON es.eval_id = live.eval_id
        LEFT JOIN `{$this->archiveDatabase}`.evaluation_answers archived
            ON archived.answer_id = live.answer_id
        WHERE es.period_id = :period_id
          AND (
              archived.answer_id IS NULL
              OR NOT (live.eval_id <=> archived.eval_id)
              OR NOT (live.question_id <=> archived.question_id)
              OR NOT (live.score <=> archived.score)
          )
    ";

        $stmt = $this->livePdo->prepare($sql);
        $stmt->execute(['period_id' => $periodId]);

        return (int) $stmt->fetchColumn();
    }


    public function countCleanupRecordDifferences(
        string $table,
        int $periodId
    ): int {

        $primaryKeys = [
            'participation_history' => 'history_id',
            'notification_queue' => 'notification_id',
            'teacher_notification_queue' => 'queue_id'
        ];

        if (!isset($primaryKeys[$table])) {
            throw new RuntimeException(
                'Invalid cleanup validation table.'
            );
        }

        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }

        $primaryKey = $primaryKeys[$table];

        $sql = "
        SELECT COUNT(*)
        FROM `{$this->liveDatabase}`.`{$table}` live
        LEFT JOIN `{$this->archiveDatabase}`.`{$table}` archived
            ON archived.`{$primaryKey}` = live.`{$primaryKey}`
            AND archived.period_id = live.period_id
        WHERE live.period_id = :period_id
          AND archived.`{$primaryKey}` IS NULL
    ";

        $stmt = $this->livePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return (int) $stmt->fetchColumn();
    }


    private function countSnapshotDifferences(
        int $periodId,
        string $liveTable,
        string $snapshotTable,
        string $liveKey,
        string $snapshotKey,
        array $columnMappings
    ): int {

        if ($periodId <= 0) {
            throw new RuntimeException(
                "Invalid evaluation period ID."
            );
        }

        $conditions = [
            "live.`{$liveKey}` IS NULL"
        ];

        foreach ($columnMappings as $liveColumn => $snapshotColumn) {
            $conditions[] = "
            NOT (
                live.`{$liveColumn}` <=>
                archived.`{$snapshotColumn}`
            )
        ";
        }

        $comparison = implode(' OR ', $conditions);

        $sql = "
        SELECT COUNT(*)
        FROM `{$this->archiveDatabase}`.`{$snapshotTable}` archived
        LEFT JOIN `{$this->liveDatabase}`.`{$liveTable}` live
            ON live.`{$liveKey}` = archived.`{$snapshotKey}`
        WHERE archived.period_id = :period_id
          AND ({$comparison})
    ";

        $stmt = $this->livePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return (int) $stmt->fetchColumn();
    }


    public function countStudentSnapshotDifferences(int $periodId): int
    {
        return $this->countSnapshotDifferences(
            $periodId,
            'students',
            'student_snapshots',
            'id',
            'student_pk',
            [
                'student_id' => 'student_id',
                'full_name' => 'full_name',
                'email' => 'email',
                'program_id' => 'program_id',
                'year_level' => 'year_level',
                'enrollment_type' => 'enrollment_type',
            ]
        );
    }

    public function countTeacherSnapshotDifferences(int $periodId): int
    {
        return $this->countSnapshotDifferences(
            $periodId,
            'teachers',
            'teacher_snapshots',
            'teacher_id',
            'teacher_id',
            [
                'employee_id' => 'employee_id',
                'full_name' => 'full_name',
                'department' => 'department',
            ]
        );
    }

    public function countProgramSnapshotDifferences(int $periodId): int
    {
        return $this->countSnapshotDifferences(
            $periodId,
            'programs',
            'program_snapshots',
            'program_id',
            'program_id',
            [
                'program_code' => 'program_code',
                'program_name' => 'program_name',
                'department' => 'department',
            ]
        );
    }

    public function countQuestionSetSnapshotDifferences(int $periodId): int
    {
        return $this->countSnapshotDifferences(
            $periodId,
            'question_sets',
            'question_set_snapshots',
            'set_id',
            'set_id',
            [
                'set_name' => 'set_name',
            ]
        );
    }

    public function countQuestionSnapshotDifferences(int $periodId): int
    {
        return $this->countSnapshotDifferences(
            $periodId,
            'questions',
            'question_snapshots',
            'question_id',
            'question_id',
            [
                'set_id' => 'set_id',
                'question_text' => 'question_text',
                'category' => 'category',
                'is_active' => 'is_active',
            ]
        );
    }


    public function countRemainingCleanupRecords(
        int $periodId
    ): array {

        $tables = [
            'evaluation_answers',
            'evaluation_status',
            'participation_history',
            'notification_queue',
            'teacher_notification_queue'
        ];

        $remaining = [];

        foreach ($tables as $table) {
            $remaining[$table] = $this->countPeriodRecords(
                $this->liveDatabase,
                $table,
                $periodId
            );
        }

        return $remaining;
    }


    /**
     * Delete evaluation responses from live database
     */
    public function deleteLiveEvaluationAnswers(
        int $periodId
    ): int {

        $stmt = $this->livePdo->prepare("
            DELETE ea
            FROM evaluation_answers ea
            INNER JOIN evaluation_status es
                ON es.eval_id = ea.eval_id
            WHERE es.period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount();

    }


    public function deleteLiveEvaluationStatus(
        int $periodId
    ): int {

        $stmt = $this->livePdo->prepare("
            DELETE FROM evaluation_status
            WHERE period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount();

    }


    public function deleteLiveParticipationHistory(int $periodId): int
    {
        $stmt = $this->livePdo->prepare("
            DELETE FROM participation_history
            WHERE period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount();
    }


    public function deleteLiveNotifications(int $periodId): int
    {
        $stmt = $this->livePdo->prepare("
            DELETE FROM notification_queue
            WHERE period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount();
    }


    public function deleteLiveTeacherNotifications(int $periodId): int
    {
        $stmt = $this->livePdo->prepare("
            DELETE FROM teacher_notification_queue
            WHERE period_id = :period_id
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount();
    }


    // Mark evaluation period cleaned
    public function markPeriodCleaned(
        int $periodId
    ): void {

        $stmt = $this->archivePdo->prepare("
           UPDATE archive_jobs
            SET
                cleanup_status = 'completed',
                cleaned_at = NOW()
            WHERE period_id = :period_id
            AND status = 'verified'
            AND cleanup_status = 'processing'
            AND cleaned_at IS NULL
        ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to mark evaluation period as cleaned.'
            );
        }

    }


    public function claimCleanup(int $periodId): bool
    {
        $stmt = $this->archivePdo->prepare("
        UPDATE archive_jobs
        SET cleanup_status = 'processing'
        WHERE period_id = :period_id
          AND status = 'verified'
          AND cleanup_status = 'pending'
          AND cleaned_at IS NULL
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return $stmt->rowCount() === 1;
    }


    public function markPeriodCleanedInTransaction(int $periodId): void
    {
        $archiveTable = "`{$this->archiveDatabase}`.`archive_jobs`";

        $stmt = $this->livePdo->prepare("
        UPDATE {$archiveTable}
        SET
            cleanup_status = 'completed',
            cleaned_at = NOW()
        WHERE period_id = :period_id
          AND status = 'verified'
          AND cleanup_status = 'processing'
          AND cleaned_at IS NULL
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to mark cleanup as completed.'
            );
        }
    }


    public function markCleanupForReconciliation(int $periodId): void
    {
        $stmt = $this->archivePdo->prepare("
        UPDATE archive_jobs
        SET cleanup_status = 'needs_reconciliation'
        WHERE period_id = :period_id
          AND status = 'verified'
          AND cleanup_status = 'processing'
          AND cleaned_at IS NULL
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to mark cleanup for reconciliation.'
            );
        }
    }


    public function resetCleanupForRetry(int $periodId): void
    {
        $stmt = $this->archivePdo->prepare("
        UPDATE archive_jobs
        SET cleanup_status = 'pending'
        WHERE period_id = :period_id
          AND status = 'verified'
          AND cleanup_status = 'needs_reconciliation'
          AND cleaned_at IS NULL
    ");

        $stmt->execute([
            'period_id' => $periodId
        ]);

        if ($stmt->rowCount() !== 1) {
            throw new RuntimeException(
                'Unable to reset cleanup for retry.'
            );
        }
    }
}
