<?php

namespace App\Models;

class Teacher
{
    public ?int $teacherId = null;
    public string $employeeId;
    public string $fullName;
    public string $email;
    public ?string $department;
    public string $imagePath;
    public bool $isActive;

    public array $handles = [];

    public static function fromArray(array $row): self
    {
        $teacher = new self();

        $teacher->teacherId = isset($row['teacher_id'])
            ? (int) $row['teacher_id']
            : null;

        $teacher->employeeId = (string) ($row['employee_id'] ?? '');
        $teacher->fullName = (string) ($row['full_name'] ?? '');
        $teacher->email = (string) ($row['email'] ?? '');
        $teacher->department = $row['department'] ?? null;
        $teacher->imagePath = $row['image_path'] ?? 'default_teacher.png';
        $teacher->isActive = !empty($row['is_active']);

        return $teacher;
    }

    public function setHandles(
      array $handles
    ): void
    {
      $this->handles = $handles;
    }

    public function getImagePath(): ?string
    {
        return $this->imagePath;
    }

    public function toArray(): array 
    {

      return[
        'teacher_id'  => $this->teacherId,
        'employee_id' => $this->employeeId,
        'full_name'   => $this->fullName,
        'email'       => $this->email,
        'department'  => $this->department,
        'image_path'  => $this->imagePath,
        'is_active'   => $this->isActive ? 1 : 0,
        'handles'     => $this->handles
      ];

    }
}