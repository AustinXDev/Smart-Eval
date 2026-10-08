<?php

namespace App\Models;

class QuestionModel
{
    public ?int $setId = null;
    public string $setName = '';
    public bool $isActive = true;
    public ?string $createdAt = null;


    public static function fromArray(array $row): self
    {
        $model = new self();
        $model->setId = isset($row['set_id']) ? (int) $row['set_id'] : null;
        $model->setName = $row['set_name'] ?? '';
        $model->isActive = (bool) ($row['is_active'] ?? true);
        $model->createdAt = $row['created_at'] ?? null;

        return $model;
    }


    public function toArray(): array
    {
        return [
            'set_id' => $this->setId,
            'set_name' => $this->setName,
            'is_active' => $this->isActive ? 1 : 0,
            'created_at' => $this->createdAt,
        ];
    }
}
