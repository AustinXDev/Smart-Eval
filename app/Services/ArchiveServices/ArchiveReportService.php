<?php

declare(strict_types=1);

namespace App\Services\ArchiveServices;

use App\Repositories\ArchiveRepo\ArchiveReadRepository;
use PDO;
use RuntimeException;

final class ArchiveReportService
{
    private ArchiveReadRepository $repository;

    public function __construct(
        PDO $archivePdo
    ) {

        $this->repository = new ArchiveReadRepository(
            $archivePdo
        );

    }

    public function listPeriods(): array
    {
        return $this->repository->getArchivedPeriods();
    }

    public function getPeriodDetails(int $periodId): array
    {
        if ($periodId <= 0) {
            throw new RuntimeException(
                "Invalid evaluation period."
            );
        }

        $period = $this->repository->getArchivedPeriod(
            $periodId
        );

        if (!$period) {
            throw new RuntimeException(
                "Archived evaluation period not found."
            );
        }

        return [
            'period' => $period,

            'teachers' => $this->repository
                ->getTeacherEvaluations($periodId),

            'students' => $this->repository
                ->getStudentParticipation($periodId)
        ];
    }

}
