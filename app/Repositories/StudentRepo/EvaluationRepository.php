<?php

namespace App\Repositories\StudentRepo;

use PDO;

class EvaluationRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function saveEligibleTeacherSelection(
        string $studentId,
        array $teacherIds,
        int $periodId
    ): void {
        try {
            $this->pdo->beginTransaction();

            $student = $this->pdo->prepare(
                "
                SELECT 
                    p.department, 
                    s.enrollment_type 
                FROM students s 

                INNER JOIN programs p 
                    ON p.program_id = s.program_id 
                    
                WHERE s.student_id = ? 
                AND s.is_active = 1 
                FOR UPDATE"
            );

            $student->execute([$studentId]);

            $row = $student->fetch(PDO::FETCH_ASSOC);

            if (!$row || $row['enrollment_type'] !== 'Irregular') {
                throw new \RuntimeException('Invalid teacher selection.');
            }

            $period = $this->pdo->prepare(
                "
                SELECT 
                    period_id  
                FROM evaluation_periods 

                WHERE period_id = ? 
                    AND target_dept = ? 
                    AND is_active = 1 
                    AND is_closed = 0 
                FOR UPDATE"
            );

            $period->execute([$periodId, $row['department']]);

            if (!$period->fetch()) {

                throw new \RuntimeException('No open evaluation period.');

            }

            $existing = $this->pdo->prepare(
                "
                SELECT 
                    teacher_id 
                FROM evaluation_status 
                WHERE student_id = ? 
                AND period_id = ?"
            );

            $existing->execute([$studentId, $periodId]);

            $assigned = array_map('intval', $existing->fetchAll(PDO::FETCH_COLUMN));

            if ($assigned !== []) {
                sort($assigned);

                $requested = $teacherIds;

                sort($requested);

                if ($assigned !== $requested) {
                    throw new \RuntimeException('Teacher selection is already finalized for this period.');
                }

                $this->pdo->commit();

                return;
            }

            $teacher = $this->pdo->prepare(
                "
                SELECT teacher_id 
                    FROM teachers 
                WHERE teacher_id = ? 
                AND department = ? 
                AND is_active = 1 
                FOR UPDATE"
            );

            foreach ($teacherIds as $teacherId) {

                $teacher->execute([$teacherId, $row['department']]);

                if (!$teacher->fetch()) {
                    throw new \RuntimeException('One or more teachers are unavailable for your department.');
                }

            }

            $this->seedIrregularCollegeEvaluationStatues($studentId, $teacherIds, $periodId);

            $update = $this->pdo->prepare(
                "
                UPDATE students 
                    SET selected_load_ids = ? 
                WHERE student_id = ?"
            );

            $update->execute([json_encode($teacherIds, JSON_THROW_ON_ERROR), $studentId]);

            $this->pdo->commit();

        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
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
            INSERT IGNORE INTO evaluation_status 
                (student_id, teacher_id, period_id)
            SELECT ?, teacher_id, ?
            FROM teacher_load
            WHERE program_id = ? AND year_level = ?
            AND is_active = 1
        ");
        $stmt->execute([$studentId, $periodId, $programId, $yearLevel]);
    }

    public function seedRegularCollegeEvaluationStatus(
        string $studentId,
        int $periodId,
        int $programId,
        string $yearLevel
    ): bool {

        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO evaluation_status
                (student_id, teacher_id, period_id)
            SELECT ?, teacher_id, ?
            FROM teacher_load
            WHERE program_id = ? AND year_level = ?
            AND is_active = 1
        ");

        return $stmt->execute([$studentId, $periodId, $programId, $yearLevel]);

    }


    public function seedIrregularCollegeEvaluationStatues(
        string $studentId,
        array $teacherIds,
        int $periodId
    ): void {

        $stmt = $this->pdo->prepare("
            INSERT IGNORE INTO evaluation_status
                (student_id, teacher_id, period_id)
            VALUES
                (?, ?, ?)
        ");

        foreach ($teacherIds as $teacherId) {
            $stmt->execute([
                $studentId,
                $teacherId,
                $periodId
            ]);
        }

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
