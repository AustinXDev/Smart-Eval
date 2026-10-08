<?php

namespace App\Models;

class Program
{
    private ?int $programId = null;
    private string $programCode = '';
    private string $programName = '';
    private string $department = '';
    private bool $isActive = true;
    private ?string $createdAt = null;
    private ?string $updatedAt = null;

    public static function fromArray(array $data): self
    {
        $program = new self();

        $program->programId = isset($data['program_id'])
            ? (int) $data['program_id']
            : null;

        $program->programCode = trim(
            $data['program_code'] ?? ''
        );

        $program->programName = trim(
            $data['program_name'] ?? ''
        );

        $program->department = trim(
            $data['department'] ?? ''
        );

        $program->isActive = isset($data['is_active'])
            ? (int) $data['is_active'] === 1
            : true;

        return $program;
    }

    public function getProgramId(): ?int
    {
        return $this->programId;
    }

    public function getProgramCode(): string
    {
        return $this->programCode;
    }

    public function getProgramName(): string
    {
        return $this->programName;
    }

    public function getDepartment(): string
    {
        return $this->department;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function toArray(): array
    {
        return [
            'program_id'   => $this->programId,
            'program_code' => $this->programCode,
            'program_name' => $this->programName,
            'department'   => $this->department,
            'is_active'    => $this->isActive,
            'created_at'   => $this->createdAt,
            'updated_at'   => $this->updatedAt
        ];
    }
}
