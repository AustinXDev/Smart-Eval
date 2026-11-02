<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use InvalidArgumentException;

final class EvaluationHistoryRepository
{
    public function __construct(
        private PDO $livePdo,
        private PDO $archivePdo
    ) {
    }

    public function getEvaluationHistory(
        ?string $department = null
    ): array {

        if (
            $department !== null &&
            !in_array($department, ['college', 'shs'], true)
        ) {

            throw new InvalidArgumentException(
                'Invalid department.'
            );

        }

        // Closed periods in live database
        $liveSql = "
      SELECT 
        period_id,
        academic_year,
        semester,
        target_dept,
        start_date,
        end_date,
        'live' AS source
      FROM evaluation_periods
      WHERE is_closed = 1
        
    ";

        // Successfully archived periods in archive database
        $archiveSql = "
      SELECT
        ep.period_id,
        ep.academic_year,
        ep.semester,
        ep.target_dept,
        ep.start_date,
        ep.end_date,
        'archive' AS source
      FROM evaluation_periods ep
      WHERE EXISTS (
        SELECT 1
        FROM archive_jobs aj
        WHERE aj.period_id = ep.period_id
          AND aj.status = 'verified'
          AND aj.cleanup_status = 'completed'
          AND aj.cleaned_at IS NOT NULL
      )
    ";

        $params = [];

        if ($department !== null) {

            $liveSql .= " AND target_dept = :department";
            $archiveSql .= " AND target_dept = :department";

            $params['department'] = $department;

        }

        $liveStmt = $this->livePdo->prepare($liveSql);
        $liveStmt->execute($params);

        $archiveStmt = $this->archivePdo->prepare($archiveSql);
        $archiveStmt->execute($params);

        $records = [];

        foreach ($liveStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $records[$row['period_id']] = $row;
        }

        foreach ($archiveStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $records[$row['period_id']] = $row;
        }

        $history = array_values($records);

        usort(
            $history,
            fn ($a, $b) =>
                strcmp($b['end_date'], $a['end_date'])
        );

        return $history;
    }

}
