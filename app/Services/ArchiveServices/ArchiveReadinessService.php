<?php

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveRepository;

final class ArchiveReadinessService
{
    public function __construct(
        private ArchiveRepository $archiveRepo
    ) {
    }

    public function check(int $periodId): array
    {
        $stats = $this->archiveRepo
            ->getTeacherNotificationStats($periodId);

        $total = (int) $stats['total_jobs'];
        $pending = (int) $stats['pending_jobs'];
        $processing = (int) $stats['processing_jobs'];
        $retryable = (int) $stats['retryable_failed_jobs'];
        $exhausted = (int) $stats['exhausted_failed_jobs'];
        $sent = (int) $stats['sent_jobs'];
        $skipped = (int) $stats['skipped_jobs'];

        $unfinished = $pending + $processing + $retryable;
        $completed = $sent + $skipped + $exhausted;

        $ready = $unfinished === 0
            && $completed === $total;

        return [
            'period_id' => $periodId,
            'ready' => $ready,
            'total_jobs' => $total,
            'unfinished_jobs' => $unfinished,
            'pending_jobs' => $pending,
            'processing_jobs' => $processing,
            'retryable_failed_jobs' => $retryable,
            'exhausted_failed_jobs' => $exhausted,
            'sent_jobs' => $sent,
            'skipped_jobs' => $skipped,
        ];
    }

}
