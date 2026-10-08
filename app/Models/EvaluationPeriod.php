<?php

namespace App\Models;

class EvaluationPeriod
{
    private ?int $periodId;
    private string $periodName;
    private string $semester;
    private string $department;
    private ?int $set;
    private string $startDate;
    private string $endDate;
    private bool $isActive;
    private bool $isClosed;
    private bool $isForced;

    public static function fromArray(array $data): self
    {

        $period = new self();

        $period->periodId = isset($data['period_id'])
                            ? (int) $data['period_id']
                            : null;

        $period->periodName = $data['academic_year'] ?? '';
        $period->semester = $data['semester'] ?? '';
        $period->department = $data['target_dept'] ?? '';
        $period->set = isset($data['set_id'])
                        ? (int) $data['set_id']
                        : null;
        $period->startDate = $data['start_date'] ?? '';
        $period->endDate = $data['end_date'] ?? '';
        $period->isActive = isset($data['is_active'])
                            ? (bool) $data['is_active']
                            : false;
        $period->isClosed = isset($data['is_closed'])
                            ? (bool) $data['is_closed']
                            : false;
        $period->isForced = isset($data['is_forced'])
                            ? (bool) $data['is_forced']
                            : false;

        return $period;

    }

    public function getPeriodId(): ?int
    {
        return $this->periodId;
    }

    public function getPeriodName(): string
    {
        return $this->periodName;
    }

    public function getSemester(): string
    {
        return $this->semester;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function getSetId(): int
    {
        return $this->set;
    }

    public function getStartDate(): string
    {
        return $this->startDate;
    }

    public function getEndDate(): string
    {
        return $this->endDate;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isClosed(): bool
    {
        return $this->isClosed;
    }

    public function isForced(): bool
    {
        return $this->isForced;
    }

    public function toArray(): array
    {
        return [
            'period_id'   => $this->periodId,
            'academic_year' => $this->periodName,
            'semester' => $this->semester,
            'target_dept' => $this->department,
            'set_id' => $this->set,
            'start_date'  => $this->startDate,
            'end_date'    => $this->endDate,
            'is_active'   => $this->isActive,
            'is_closed'   => $this->isClosed,
            'is_forced'   => $this->isForced
        ];
    }

}
