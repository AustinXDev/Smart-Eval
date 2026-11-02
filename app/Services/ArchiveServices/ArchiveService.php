<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveRepository;
use App\Services\ArchiveServices\ArchiveEligibilityService;
use PDO;
use RuntimeException;
use Throwable;

final class ArchiveService
{
    private ArchiveRepository $repository;

    public function __construct(
        private PDO $livePdo,
        private PDO $archivePdo,
        private ArchiveEligibilityService $eligibilityService
    ) {
        $this->repository = new ArchiveRepository($livePdo, $archivePdo);
    }

    public function archivePeriod(
        int $periodId,
        bool $localTest = false
    ): array {

        if ($periodId <= 0) {
            throw new RuntimeException(
                "Invalid evaluation period."
            );
        }

        // Never allow the seven-day bypass in production.
        if ($localTest && !$this->isLocalEnvironment()) {
            throw new RuntimeException(
                "Local testing is not allowed in production."
            );
        }

        if ($this->livePdo->inTransaction()) {
            throw new RuntimeException(
                "An existing transaction is active."
            );
        }

        $this->livePdo->beginTransaction();

        try {

            // Lock and validate the source period.
            $stmt = $this->livePdo->prepare("
                SELECT period_id, is_closed, end_date
                FROM dbsmarteval.evaluation_periods
                WHERE period_id = ?
                FOR UPDATE
            ");

            $stmt->execute([$periodId]);
            $period = $stmt->fetch();

            if (!$period) {
                throw new RuntimeException(
                    "Evaluation period not found."
                );
            }

            if ((int) $period['is_closed'] !== 1) {
                throw new RuntimeException(
                    "Evaluation period is not closed."
                );
            }

            // Validate archive eligibility using the shared service.
            if (
                !$localTest &&
                !$this->eligibilityService->isEligible($period)
            ) {
                throw new RuntimeException(
                    'Evaluation period is not yet eligible for archiving.'
                );
            }

            // 3. Prevent duplicate archiving.
            if ($this->repository->archiveExists($periodId)) {
                throw new RuntimeException(
                    "Evaluation period is already archived."
                );
            }

            // 4. Validate references before copying.
            $this->validateSourceData($periodId);

            $this->validatePeriodDepartment($periodId);

            // 5. Copy parent record.
            echo "[1] Checking evaluation period..." . PHP_EOL;

            echo "[2] Copying evaluation period..." . PHP_EOL;
            $this->repository->copyPeriod($periodId);

            // 6. Copy historical snapshots.
            echo "[3] Copying question set..." . PHP_EOL;
            $this->repository->copyQuestionSet($periodId);

            echo "[4] Copying questions..." . PHP_EOL;
            $this->repository->copyQuestions($periodId);

            echo "[5] Copying teachers..." . PHP_EOL;
            $this->repository->copyTeachers($periodId);

            echo "[6] Copying students..." . PHP_EOL;
            $this->repository->copyStudents($periodId);

            echo "[7] Copying programs..." . PHP_EOL;
            $this->repository->copyPrograms($periodId);

            // 7. Copy evaluation transactions.
            echo "[8] Copying evaluations..." . PHP_EOL;
            $this->repository->copyEvaluations($periodId);

            echo "[9] Copying answers..." . PHP_EOL;
            $this->repository->copyAnswers($periodId);

            // 8. Copy participation and notification history.
            $this->repository->copyParticipationHistory($periodId);
            $this->repository->copyNotifications($periodId);
            $this->repository->copyTeacherNotifications($periodId);

            // 9. Verify source and archive counts.
            $counts = $this->verifyCounts($periodId);

            // 10. Verify snapshot references.
            $this->verifySnapshots($periodId);

            $this->verifyDepartmentSnapshots($periodId);

            $this->verifySnapshotDepartment($periodId);

            // 11. Record the local archive copy.
            // "pending" means cloud backup is NOT verified.
            $stmt = $this->livePdo->prepare("
                INSERT INTO dbsmarteval_archive.archive_jobs (
                    period_id,
                    status,
                    manifest_json,
                    archived_at
                )
                VALUES (?, 'pending', ?, NOW())
            ");

            $manifest = json_encode(
                [
                    'type' => 'database_copy',
                    'period_id' => $periodId,
                    'counts' => $counts,
                    'cloud_backup_verified' => false
                ],
                JSON_THROW_ON_ERROR
            );

            $stmt->execute([$periodId, $manifest]);

            // 12. Commit all copied records.
            $this->livePdo->commit();

            return [
                'status' => 'success',
                'message' => 'Evaluation period copied successfully.',
                'period_id' => $periodId,
                'backup_verified' => false,
                'counts' => $counts
            ];

        } catch (Throwable $e) {

            if ($this->livePdo->inTransaction()) {
                $this->livePdo->rollBack();
            }

            throw $e;
        }
    }

    private function isLocalEnvironment(): bool
    {
        // Must be explicitly configured in your local .env.
        return ($_ENV['APP_ENV'] ?? '') === 'local'
            && ($_ENV['ARCHIVE_LOCAL_TEST'] ?? '') === 'true'
            && in_array(
                trim($_ENV['DB_HOST'] ?? ''),
                ['localhost', '127.0.0.1'],
                true
            )
            && ($_ENV['DB_NAME'] ?? '') === 'dbsmarteval'
            && ($_ENV['ARCHIVE_DB_NAME'] ?? '') === 'dbsmarteval_archive';
    }

    private function validateSourceData(int $periodId): void
    {
        $checks = [
            'missing_students' => "
                SELECT COUNT(*)
                FROM dbsmarteval.evaluation_status es
                LEFT JOIN dbsmarteval.students s
                    ON s.student_id = es.student_id
                WHERE es.period_id = ?
                  AND s.id IS NULL
            ",
            'missing_teachers' => "
                SELECT COUNT(*)
                FROM dbsmarteval.evaluation_status es
                LEFT JOIN dbsmarteval.teachers t
                    ON t.teacher_id = es.teacher_id
                WHERE es.period_id = ?
                  AND t.teacher_id IS NULL
            ",
            'missing_notification_teachers' => "
                SELECT COUNT(*)
                FROM dbsmarteval.teacher_notification_queue tn
                LEFT JOIN dbsmarteval.teachers t
                    ON t.teacher_id = tn.teacher_id
                WHERE tn.period_id = ?
                  AND t.teacher_id IS NULL
            ",
            'missing_questions' => "
                SELECT COUNT(*)
                FROM dbsmarteval.evaluation_answers ea
                INNER JOIN dbsmarteval.evaluation_status es
                    ON es.eval_id = ea.eval_id
                INNER JOIN dbsmarteval.evaluation_periods ep
                    ON ep.period_id = es.period_id
                LEFT JOIN dbsmarteval.questions q
                    ON q.question_id = ea.question_id
                   AND q.set_id = ep.set_id
                WHERE es.period_id = ?
                  AND q.question_id IS NULL
            "
        ];

        foreach ($checks as $name => $sql) {

            $stmt = $this->livePdo->prepare($sql);
            $stmt->execute([$periodId]);

            if ((int) $stmt->fetchColumn() > 0) {
                throw new RuntimeException(
                    "Archive validation failed: {$name}"
                );
            }
        }
    }

    private function verifyCounts(int $periodId): array
    {
        $tables = [
            'evaluation_periods',
            'evaluation_status',
            'evaluation_answers',
            'participation_history',
            'notification_queue',
            'teacher_notification_queue'
        ];

        $counts = [];

        foreach ($tables as $table) {

            $live = $this->repository->countPeriodRecords(
                'dbsmarteval',
                $table,
                $periodId
            );

            $archive = $this->repository->countPeriodRecords(
                'dbsmarteval_archive',
                $table,
                $periodId
            );

            if ($live !== $archive) {
                throw new RuntimeException(
                    "Archive verification failed for {$table}: " .
                    "Live={$live}, Archive={$archive}"
                );
            }

            $counts[$table] = $archive;
        }

        return $counts;
    }

    private function verifySnapshots(int $periodId): void
    {
        $checks = [
            'student_snapshots' => "
                SELECT COUNT(*)
                FROM dbsmarteval_archive.evaluation_status es
                LEFT JOIN dbsmarteval_archive.student_snapshots ss
                    ON ss.period_id = es.period_id
                   AND ss.student_pk = es.student_pk
                WHERE es.period_id = ?
                  AND (es.student_pk IS NULL OR ss.student_pk IS NULL)
            ",
            'teacher_snapshots' => "
                SELECT COUNT(*)
                FROM dbsmarteval_archive.evaluation_status es
                LEFT JOIN dbsmarteval_archive.teacher_snapshots ts
                    ON ts.period_id = es.period_id
                   AND ts.teacher_id = es.teacher_id
                WHERE es.period_id = ?
                  AND ts.teacher_id IS NULL
            ",
            'question_snapshots' => "
                SELECT COUNT(*)
                FROM dbsmarteval_archive.evaluation_answers ea
                LEFT JOIN dbsmarteval_archive.question_snapshots qs
                    ON qs.period_id = ea.period_id
                   AND qs.question_id = ea.question_id
                WHERE ea.period_id = ?
                  AND qs.question_id IS NULL
            "
        ];

        foreach ($checks as $name => $sql) {

            $stmt = $this->livePdo->prepare($sql);
            $stmt->execute([$periodId]);

            if ((int) $stmt->fetchColumn() !== 0) {
                throw new RuntimeException(
                    "Missing archive reference: {$name}"
                );
            }
        }
    }

    private function verifyDepartmentSnapshots(int $periodId): void
    {
        $checks = [
            'students' => "
            SELECT COUNT(*)
            FROM dbsmarteval.students s
            JOIN dbsmarteval.programs p
                ON p.program_id = s.program_id
            JOIN dbsmarteval.evaluation_periods ep
                ON ep.period_id = ?
            LEFT JOIN dbsmarteval_archive.student_snapshots ss
                ON ss.period_id = ep.period_id
                AND ss.student_pk = s.id
            WHERE p.department = ep.target_dept
              AND ss.student_pk IS NULL
        ",

            'teachers' => "
            SELECT COUNT(*)
            FROM dbsmarteval.teachers t
            JOIN dbsmarteval.evaluation_periods ep
                ON ep.period_id = ?
            LEFT JOIN dbsmarteval_archive.teacher_snapshots ts
                ON ts.period_id = ep.period_id
                AND ts.teacher_id = t.teacher_id
            WHERE t.department = ep.target_dept
              AND ts.teacher_id IS NULL
        ",

            'programs' => "
            SELECT COUNT(*)
            FROM dbsmarteval.programs p
            JOIN dbsmarteval.evaluation_periods ep
                ON ep.period_id = ?
            LEFT JOIN dbsmarteval_archive.program_snapshots ps
                ON ps.period_id = ep.period_id
                AND ps.program_id = p.program_id
            WHERE p.department = ep.target_dept
              AND ps.program_id IS NULL
        ",
            'questions' => "
                SELECT COUNT(*)
                FROM dbsmarteval.questions q
                INNER JOIN dbsmarteval.evaluation_periods ep
                    ON ep.set_id = q.set_id
                LEFT JOIN dbsmarteval_archive.question_snapshots qs
                    ON qs.period_id = ep.period_id
                AND qs.question_id = q.question_id
                WHERE ep.period_id = ?
                AND qs.question_id IS NULL
            "
        ];

        foreach ($checks as $name => $sql) {

            $stmt = $this->livePdo->prepare($sql);
            $stmt->execute([$periodId]);

            $missing = (int) $stmt->fetchColumn();

            if ($missing > 0) {
                throw new RuntimeException(
                    "Archive verification failed: "
                    . "{$missing} missing {$name} snapshots."
                );
            }
        }
    }


    private function verifySnapshotDepartment(int $periodId): void
    {
        $checks = [
            'students' => "
            SELECT COUNT(*)
            FROM dbsmarteval_archive.student_snapshots ss
            INNER JOIN dbsmarteval_archive.program_snapshots ps
                ON ps.period_id = ss.period_id
               AND ps.program_id = ss.program_id
            INNER JOIN dbsmarteval_archive.evaluation_periods ep
                ON ep.period_id = ss.period_id
            WHERE ss.period_id = ?
              AND ps.department <> ep.target_dept
        ",

            'teachers' => "
            SELECT COUNT(*)
            FROM dbsmarteval_archive.teacher_snapshots ts
            INNER JOIN dbsmarteval_archive.evaluation_periods ep
                ON ep.period_id = ts.period_id
            WHERE ts.period_id = ?
              AND ts.department <> ep.target_dept
        ",

            'programs' => "
            SELECT COUNT(*)
            FROM dbsmarteval_archive.program_snapshots ps
            INNER JOIN dbsmarteval_archive.evaluation_periods ep
                ON ep.period_id = ps.period_id
            WHERE ps.period_id = ?
              AND ps.department <> ep.target_dept
        "
        ];

        foreach ($checks as $name => $sql) {

            $stmt = $this->livePdo->prepare($sql);
            $stmt->execute([$periodId]);

            $count = (int) $stmt->fetchColumn();

            if ($count > 0) {
                throw new RuntimeException(
                    "Archive contains {$count} {$name} "
                    . "from another department."
                );
            }
        }
    }


    private function validatePeriodDepartment(
        int $periodId
    ): void {

        //Get the department of the evaluation period
        $stmt = $this->livePdo->prepare("
            SELECT target_dept
            FROM dbsmarteval.evaluation_periods
            WHERE period_id = ?
        ");

        $stmt->execute([$periodId]);

        $department = $stmt->fetchColumn();

        if ($department === false || $department === '') {
            throw new RuntimeException(
                "Evaluation period department is missing."
            );
        }

        //Check evaluated students from another department
        $stmt = $this->livePdo->prepare("
            SELECT COUNT(*)
            FROM dbsmarteval.evaluation_status es

            INNER JOIN dbsmarteval.students s
                ON s.student_id = es.student_id

            LEFT JOIN dbsmarteval.programs p
                ON p.program_id = s.program_id

            WHERE es.period_id = ?
                AND (
                    p.program_id IS NULL
                    OR p.department IS NULL
                    OR p.department <> ?
                )
        ");

        $stmt->execute([$periodId, $department]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException(
                "Some evaluated students do not belong to "
                . "the evaluation period department."
            );
        }

        // Check evaluated teachers from another department
        $stmt = $this->livePdo->prepare("
            SELECT COUNT(*)
            FROM dbsmarteval.evaluation_status es

            INNER JOIN dbsmarteval.teachers t
                ON t.teacher_id = es.teacher_id

            WHERE es.period_id = ?
            AND (
                t.department IS NULL
                OR t.department <> ?
            )
        ");

        $stmt->execute([$periodId, $department]);

        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException(
                "Some evaluated teachers do not belong to "
                . "the evaluation period department."
            );
        }

    }
}
