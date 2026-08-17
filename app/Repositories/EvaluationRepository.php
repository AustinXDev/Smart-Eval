<?php

namespace App\Repositories;

use PDO;

class EvaluationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function getActivePeriodId(?string $department): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT period_id
            FROM evaluation_periods
            WHERE is_active = 1
            AND target_dept = ?
            LIMIT 1
        ");
        $stmt->execute([$department]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return isset($row['period_id']) ? (int)$row['period_id'] : null;
    }

    public function seedShsEvaluationStatus(string $studentId, int $periodId, int $programId, string $yearLevel): void
    {
        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO evaluation_status (student_id, load_id, period_id)
            SELECT ?, load_id, ?
            FROM teacher_load
            WHERE program_id = ? AND year_level = ?
        ");
        $stmt->execute([$studentId, $periodId, $programId, $yearLevel]);
    }

    public function countSelectedTeachers(string $studentId, ?int $periodId): int
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) AS selected_count
            FROM evaluation_status
            WHERE student_id = ? AND period_id = ?
        ");
        $stmt->execute([$studentId, $periodId]);

        return (int)($stmt->fetch(PDO::FETCH_ASSOC)['selected_count'] ?? 0);
    }
}