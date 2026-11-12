<?php

declare(strict_types=1);

namespace App\Services\MonitoringServices;

use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\MonitoringRepo\ParticipationMonitoringRepository;
use InvalidArgumentException;

/**
 * Validates the monitoring request, resolves the active period, and derives the
 * KPIs. Search, filtering, counting and pagination (LIMIT/OFFSET) run in the
 * repository's SQL, which is also the single source of truth for statuses.
 */
final class ParticipationMonitoringService
{
    private const DEPARTMENTS = ['college', 'shs'];

    public function __construct(
        private EvaluationRepository $periodRepository,
        private ParticipationMonitoringRepository $monitoringRepository
    ) {
    }

    public function getActiveParticipation(
        string $department,
        int $limit = 10,
        int $offset = 0,
        string $search = '',
        string $yearLevel = '',
        string $status = '',
    ): array {
        if (!in_array($department, self::DEPARTMENTS, true)) {
            throw new InvalidArgumentException('Invalid department.');
        }
        if ($limit < 1 || $limit > 100 || $offset < 0 || $offset > 1000000) {
            throw new InvalidArgumentException('Invalid table request.');
        }
        if (mb_strlen($search) > 100 || mb_strlen($yearLevel) > 20) {
            throw new InvalidArgumentException('Search or filter value is too long.');
        }
        if ($status !== '' && !in_array($status, ParticipationMonitoringRepository::STATUSES, true)) {
            throw new InvalidArgumentException('Invalid participation status.');
        }

        // Reuse the application's active-period lookup; a closed period is not monitored.
        $period = $this->periodRepository->findActiveByDepartment($department);
        if (!$period || (int) ($period['is_closed'] ?? 0) === 1) {
            return [
                'period' => null,
                'summary' => $this->summarize([]),
                'year_levels' => [],
                'records_total' => 0,
                'records_filtered' => 0,
                'students' => [],
            ];
        }

        $periodId = (int) $period['period_id'];
        $summary = $this->summarize($this->monitoringRepository->getSummary($periodId, $department));
        $page = $this->monitoringRepository->getStudentPage(
            $periodId,
            $department,
            $limit,
            $offset,
            $search,
            $yearLevel,
            $status,
        );

        return [
            'period' => [
                'period_id' => $periodId,
                'academic_year' => (string) $period['academic_year'],
                'semester' => (string) $period['semester'],
                'start_date' => (string) $period['start_date'],
                'end_date' => (string) $period['end_date'],
            ],
            'summary' => $summary,
            'year_levels' => $this->monitoringRepository->getYearLevels($periodId, $department),
            'records_total' => $summary['eligible'],
            'records_filtered' => $page['records_filtered'],
            'students' => $page['students'],
        ];
    }

    /** Derived KPIs come from the same status counts, so they always reconcile. */
    private function summarize(array $counts): array
    {
        $eligible = (int) ($counts['eligible'] ?? 0);
        $completed = (int) ($counts['completed'] ?? 0);
        $incomplete = (int) ($counts['incomplete'] ?? 0);
        $unresponsive = (int) ($counts['unresponsive'] ?? 0);
        $participating = $completed + $incomplete;

        return [
            'eligible' => $eligible,
            'submitted_today' => (int) ($counts['submitted_today'] ?? 0),
            'completed' => $completed,
            'incomplete' => $incomplete,
            'unresponsive' => $unresponsive,
            'remaining' => $incomplete + $unresponsive,
            'participating' => $participating,
            'participation_rate' => $eligible > 0 ? round($participating / $eligible * 100, 1) : 0.0,
            'completion_rate' => $eligible > 0 ? round($completed / $eligible * 100, 1) : 0.0,
        ];
    }
}
