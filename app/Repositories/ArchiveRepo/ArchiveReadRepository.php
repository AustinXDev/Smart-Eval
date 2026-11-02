<?php

declare(strict_types=1);

namespace App\Repositories\ArchiveRepo;

use PDO;

final class ArchiveReadRepository
{
    public function __construct(
        private PDO $archivePdo
    ) {
    }

    // Retrieve archived evaluation periods
    public function getArchivedPeriods(): array
    {

        $stmt = $this->archivePdo->prepare("
            SELECT
                ep.period_id,
                ep.academic_year,
                ep.semester,
                ep.target_dept,
                ep.start_date,
                ep.end_date,
                ep.final_average,
                ep.total_responses,
                ep.participation_rate,
                ep.archived_at
            FROM evaluation_periods ep
            ORDER BY ep.archived_at DESC
        ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    public function getLatestArchivedPeriod(
        string $department
    ): ?array {

        $sql = "
        SELECT ep.*
        FROM evaluation_periods ep
        WHERE ep.target_dept = :department
          AND ep.is_closed = 1
        ORDER BY ep.end_date DESC, ep.period_id DESC
        LIMIT 1
    ";

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'department' => $department
        ]);

        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }


    /**
     * Check whether an archived evaluation period exists.
     */
    public function isPeriodArchived(int $periodId): bool
    {
        $sql = "
        SELECT 1
        FROM evaluation_periods
        WHERE period_id = :period_id
          AND is_closed = 1
        LIMIT 1
    ";

        $stmt = $this->archivePdo->prepare($sql);

        $stmt->execute([
            'period_id' => $periodId
        ]);

        return (bool) $stmt->fetchColumn();
    }


    //Retrieve a specific archived evaluation period
    public function getArchivedPeriod(
        int $periodId
    ): array|false {

        $stmt = $this->archivePdo->prepare("
            SELECT *
            FROM evaluation_periods
            WHERE period_id = :period_id
                AND is_closed = 1
            LIMIT 1
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetch(PDO::FETCH_ASSOC);

    }


    // Retrieve archived teacher evaluations
    public function getTeacherEvaluations(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT
                ts.teacher_id,
                ts.employee_id,
                ts.full_name,
                ts.department,
                COUNT(DISTINCT es.eval_id) AS total_evaluations,
                ROUND(AVG(ea.score), 2) AS average_score
            FROM teacher_snapshots ts

            LEFT JOIN evaluation_status es
                ON es.teacher_id = ts.teacher_id
               AND es.period_id = ts.period_id
               AND es.is_submitted = 1

            LEFT JOIN evaluation_answers ea
                ON ea.eval_id = es.eval_id
               AND ea.period_id = ts.period_id

            WHERE ts.period_id = ?

            GROUP BY
                ts.teacher_id,
                ts.employee_id,
                ts.full_name,
                ts.department

            ORDER BY ts.full_name ASC
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Retrieve archived student participation summary
    public function getStudentParticipation(int $periodId): array
    {
        $stmt = $this->archivePdo->prepare("
            SELECT
                ss.student_pk,
                ss.student_id,
                ss.full_name,
                ss.year_level,
                ps.program_name,
                COUNT(DISTINCT es.eval_id) AS completed_evaluations
            FROM student_snapshots ss

            LEFT JOIN program_snapshots ps
                ON ps.program_id = ss.program_id
               AND ps.period_id = ss.period_id

            LEFT JOIN evaluation_status es
                ON es.student_pk = ss.student_pk
               AND es.period_id = ss.period_id
               AND es.is_submitted = 1

            WHERE ss.period_id = ?

            GROUP BY
                ss.student_pk,
                ss.student_id,
                ss.full_name,
                ss.year_level,
                ps.program_name

            ORDER BY ss.full_name ASC
        ");

        $stmt->execute([$periodId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
