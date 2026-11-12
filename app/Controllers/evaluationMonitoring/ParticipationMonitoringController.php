<?php

declare(strict_types=1);

namespace App\Controllers\evaluationMonitoring;

use App\Services\MonitoringServices\ParticipationMonitoringService;
use InvalidArgumentException;

final class ParticipationMonitoringController
{
    public function __construct(
        private ParticipationMonitoringService $service
    ) {
    }

    /** Server-side DataTables payload plus the KPI/period context for the same scope. */
    public function index(array $query): array
    {
        $draw = $this->int($query['draw'] ?? 1, 0);
        $start = $this->int($query['start'] ?? 0, 0);
        $length = $this->int($query['length'] ?? 10, 1, 100);

        $data = $this->service->getActiveParticipation(
            strtolower($this->string($query['dept'] ?? '')),
            $length,
            $start,
            $this->string($query['search'] ?? ''),
            $this->string($query['year_level'] ?? ''),
            $this->string($query['status_filter'] ?? ''),
        );

        return [
            'status' => 'success',
            'draw' => $draw,
            'recordsTotal' => $data['records_total'],
            'recordsFiltered' => $data['records_filtered'],
            'data' => $data['students'],
            'monitoring' => [
                'period' => $data['period'],
                'summary' => $data['summary'],
                'year_levels' => $data['year_levels'],
                'generated_at' => date('c'),
            ],
        ];
    }

    private function int(mixed $value, int $min, ?int $max = null): int
    {
        $options = ['min_range' => $min] + ($max === null ? [] : ['max_range' => $max]);
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => $options]);
        if ($int === false) {
            throw new InvalidArgumentException('Invalid table pagination.');
        }

        return $int;
    }

    private function string(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('Invalid request parameter.');
        }

        return trim($value);
    }
}
