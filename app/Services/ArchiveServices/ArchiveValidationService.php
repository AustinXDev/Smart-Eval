<?php

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveRepository;
use App\Services\BackupServices\ArchiveBackupService;
use InvalidArgumentException;
use RuntimeException;

final class ArchiveValidationService
{
    private const REQUIRED_TABLES = [
        'evaluation_periods',
        'question_set_snapshots',
        'student_snapshots',
        'program_snapshots',
        'teacher_snapshots',
        'question_snapshots',
        'evaluation_status',
        'evaluation_answers',
        'participation_history',
        'notification_queue',
        'teacher_notification_queue',
    ];

    public function __construct(
        private ArchiveRepository $archiveRepo,
        private ArchiveBackupService $backupService
    ) {
    }

    public function validateCounts(
        int $periodId,
        array $expectedCounts
    ): array {

        if ($periodId <= 0) {
            throw new InvalidArgumentException(
                "Invalid evaluation period ID."
            );
        }

        // Ensure every required table is included.
        $missingTables = array_diff(
            self::REQUIRED_TABLES,
            array_keys($expectedCounts)
        );

        $unexpectedTables = array_diff(
            array_keys($expectedCounts),
            self::REQUIRED_TABLES
        );

        if (!empty($missingTables) || !empty($unexpectedTables)) {
            throw new InvalidArgumentException(
                "Invalid or incomplete expected record counts."
            );
        }

        $differences = [];
        $archiveCounts = [];

        foreach (self::REQUIRED_TABLES as $table) {

            $expected = $expectedCounts[$table];

            if (!is_int($expected) || $expected < 0) {
                throw new InvalidArgumentException(
                    "Invalid expected count for {$table}."
                );
            }

            $actual = $this->archiveRepo->countPeriodRecords(
                'dbsmarteval_archive',
                $table,
                $periodId
            );

            $archiveCounts[$table] = $actual;

            if ($actual !== $expected) {
                $differences[$table] = [
                    'expected' => $expected,
                    'archived' => $actual,
                ];
            }
        }

        return [
            'period_id' => $periodId,
            'valid' => empty($differences),
            'expected_counts' => $expectedCounts,
            'archive_counts' => $archiveCounts,
            'differences' => $differences,
        ];
    }


    public function validateContents(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new InvalidArgumentException(
                "Invalid evaluation period ID."
            );
        }

        $statusDifferences = $this->archiveRepo
            ->countEvaluationStatusDifferences($periodId);

        $answerDifferences = $this->archiveRepo
            ->countEvaluationAnswerDifferences($periodId);

        $valid = $statusDifferences === 0
            && $answerDifferences === 0;

        return [
            'period_id' => $periodId,
            'valid' => $valid,
            'evaluation_status_differences' => $statusDifferences,
            'evaluation_answer_differences' => $answerDifferences,
        ];
    }

    public function validateSnapshots(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new InvalidArgumentException(
                "Invalid evaluation period ID."
            );
        }

        $differences = [
            'students' => $this->archiveRepo
                ->countStudentSnapshotDifferences($periodId),

            'teachers' => $this->archiveRepo
                ->countTeacherSnapshotDifferences($periodId),

            'programs' => $this->archiveRepo
                ->countProgramSnapshotDifferences($periodId),

            'question_sets' => $this->archiveRepo
                ->countQuestionSetSnapshotDifferences($periodId),

            'questions' => $this->archiveRepo
                ->countQuestionSnapshotDifferences($periodId),
        ];

        return [
            'period_id' => $periodId,
            'valid' => array_sum($differences) === 0,
            'differences' => $differences,
        ];
    }

    public function validateCleanupRecords(int $periodId): array
    {
        $tables = [
            'participation_history',
            'notification_queue',
            'teacher_notification_queue'
        ];

        $differences = [];

        foreach ($tables as $table) {

            $count = $this->archiveRepo
                ->countCleanupRecordDifferences(
                    $table,
                    $periodId
                );

            $differences[$table] = $count;
        }

        return [
            'valid' => array_sum($differences) === 0,
            'differences' => $differences
        ];
    }

    public function validateBackup(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new InvalidArgumentException(
                'Invalid evaluation period ID.'
            );
        }

        try {
            $metadata = $this->archiveRepo->findArchiveJob(
                $periodId
            );

            if (!$metadata) {
                throw new RuntimeException(
                    'Archive job not found.'
                );
            }

            if (
                ($metadata['status'] ?? null) !== 'verified' ||
                empty($metadata['manifest_json'])
            ) {
                throw new RuntimeException(
                    'Archive backup metadata is incomplete.'
                );
            }

            $manifest = json_decode(
                $metadata['manifest_json'],
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (!is_array($manifest)) {
                throw new RuntimeException(
                    'Invalid archive backup manifest.'
                );
            }

            $verification = $this->backupService
                ->verifyExistingBackup($periodId);

            return [
                'period_id' => $periodId,
                'valid' => $verification['valid'],
                'backup_status' => 'verified',
                'checksum_valid' => $verification['checksum_valid'],
                'manifest_parseable' => true,
                'message' => 'Backup metadata and remote checksum verified.'
            ];

        } catch (\Throwable $e) {
            return [
                'period_id' => $periodId,
                'valid' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
