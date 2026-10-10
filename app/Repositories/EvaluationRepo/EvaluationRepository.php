<?php

namespace App\Repositories\EvaluationRepo;

use App\Models\EvaluationPeriod;
use PDO;
use RuntimeException;

class EvaluationRepository
{
    public function __construct(
        private PDO $pdo
    ) {
    }

    /**
     * Find active evaluation period
     * by department
     */
    public function findActiveByDepartment(
        string $department
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM evaluation_periods
        WHERE target_dept = ?
            AND is_active = 1
        LIMIT 1
        ");

        $stmt->execute([
          $department
        ]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;
    }


    /**
     * Find evaluation period by
     * id
     */
    public function findById(
        int $periodId
    ): ?EvaluationPeriod {

        $stmt = $this->pdo->prepare("
          SELECT  *
          FROM evaluation_periods
            WHERE period_id = ?
          LIMIT 1
        ");

        $stmt->execute([$periodId]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period
              ? EvaluationPeriod::fromArray($period)
              : null;

    }

    public function getById(
        int $periodId
    ): ?array {

        $stmt = $this->pdo->prepare("
        SELECT *
        FROM evaluation_periods
        WHERE period_id = ?
        LIMIT 1
    ");

        $stmt->execute([$periodId]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;
    }


    /**
     * Find all evaluation period
     */
    public function findAll(): array
    {

        $stmt = $this->pdo->prepare("
          SELECT *
          FROM evaluation_periods
          ORDER BY start_date DESC
        ");

        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            fn (array $row): EvaluationPeriod =>
            EvaluationPeriod::fromArray($row),
            $rows
        );

    }


    /**
     * Get the closest evaluation period
     */
    public function getLatestClosedPeriod(
        string $department
    ): ?array {

        $stmt = $this->pdo->prepare("
            SELECT *
            FROM evaluation_periods
            WHERE target_dept = ?
                AND is_closed = 1
            ORDER BY end_date DESC
            LIMIT 1
        ");

        $stmt->execute([$department]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;

    }


    //Verifies if period is open.
    public function isPeriodOpen(
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT EXISTS (
                SELECT 1
                FROM evaluation_periods
                WHERE period_id = ?
                AND is_active = 1
                AND is_closed = 0
                AND start_date <= NOW()
                AND end_date >= NOW()
            )
        ");

        $stmt->execute([$periodId]);

        return (bool) $stmt->fetchColumn();
    }


    //Find active period with student stastics
    public function findActivePeriodsWithStats(): array
    {

        $stmt = $this->pdo->prepare("
        SELECT
            ep.period_id,
            ep.academic_year,
            ep.semester,
            ep.target_dept,
            ep.is_active,

            COUNT(DISTINCT s.student_id) AS total_students,

            COUNT(
                DISTINCT CASE
                    WHEN es.total_evaluations > 0
                     AND es.total_evaluations = es.submitted_evaluations
                    THEN es.student_id
                END
            ) AS total_finished

        FROM evaluation_periods ep

        LEFT JOIN programs p
            ON LOWER(TRIM(p.department))
             = LOWER(TRIM(ep.target_dept))

        LEFT JOIN students s
            ON s.program_id = p.program_id
            AND s.is_active = 1

        LEFT JOIN (
            SELECT
                student_id,
                period_id,

                COUNT(*) AS total_evaluations,

                SUM(
                    CASE
                        WHEN is_submitted = 1 THEN 1
                        ELSE 0
                    END
                ) AS submitted_evaluations

            FROM evaluation_status

            GROUP BY
                student_id,
                period_id
        ) es
            ON es.student_id = s.student_id
            AND es.period_id = ep.period_id

        WHERE ep.is_active = 1

        GROUP BY
            ep.period_id,
            ep.academic_year,
            ep.semester,
            ep.target_dept,
            ep.is_active

        ORDER BY ep.start_date DESC
      ");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Find previous evaluation by department
     */
    public function findPreviousInactive(
        string $department
    ): ?array {

        $stmt = $this->pdo->prepare("
          SELECT period_id
          FROM evaluation_periods
          WHERE target_dept = ?
            AND is_active = 0
          ORDER BY end_date DESC
          LIMIT 1
        ");

        $stmt->execute([$department]);

        $period = $stmt->fetch(PDO::FETCH_ASSOC);

        return $period ?: null;

    }


    /**
     * exist by semester
     */
    public function existBySemester(
        string $academicYear,
        string $semester,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM evaluation_periods
            WHERE academic_year = ?
              AND semester = ?
              AND target_dept = ?
            LIMIT 1
        ");

        $stmt->execute([
            $academicYear,
            $semester,
            $department
        ]);

        return (bool) $stmt->fetchColumn();
    }


    /**
     * Overlap by start date and end date
     */
    public function hasOverlap(
        string $academicYear,
        string $semester,
        string $department,
        string $startDate,
        string $endDate
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT 1
        FROM evaluation_periods
        WHERE target_dept = ?
          AND semester = ?
          AND academic_year = ?
          AND start_date < ?
          AND end_date > ?
        LIMIT 1
      ");

        $stmt->execute([
          $department,
          $semester,
          $academicYear,
          $startDate,
          $endDate
        ]);

        return (bool) $stmt->fetchColumn();

    }


    public function create(
        EvaluationPeriod $period
    ): EvaluationPeriod {

        $stmt = $this->pdo->prepare("
            INSERT INTO evaluation_periods (
                academic_year,
                semester,
                target_dept,
                set_id,
                start_date,
                end_date,
                is_active
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $period->getPeriodName(),
            $period->getSemester(),
            $period->getDepartment(),
            $period->getSetId(),
            $period->getStartDate(),
            $period->getEndDate(),
            $period->isActive() ? 1 : 0
        ]);

        return $this->findById(
            (int) $this->pdo->lastInsertId()
        );

    }


    /**
     * UPDATE REPOSITORIES
     */

    //Exist by semester but exclude period ID
    public function existBySemesterExclueId(
        string $academicYear,
        string $semester,
        string $department,
        int $excludePeriodId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM evaluation_periods
            WHERE academic_year = ?
              AND semester = ?
              AND target_dept = ?
              AND period_id != ?
            LIMIT 1
        ");

        $stmt->execute([
           $academicYear,
           $semester,
           $department,
           $excludePeriodId
        ]);

        return $stmt->fetchColumn() !== false;

    }


    //check if has overlap but exclude period ID
    public function hasOverlapExcludeId(
        string $academicYear,
        string $semester,
        string $department,
        string $startDate,
        string $endDate,
        int $excludePeriodId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM evaluation_periods
            WHERE target_dept = ?
              AND semester = ?
              AND academic_year = ?
              AND period_id != ?
              AND (
                  start_date < ?
                  AND end_date > ?
              )
            LIMIT 1
        ");

        $stmt->execute([
            $department,
            $semester,
            $academicYear,
            $excludePeriodId,
            $endDate,
            $startDate
        ]);

        return $stmt->fetchColumn() !== false;
    }


    //Update
    public function update(
        EvaluationPeriod $period
    ): bool {

        $stmt = $this->pdo->prepare("
            UPDATE evaluation_periods
            SET
                academic_year = ?,
                semester = ?,
                target_dept = ?,
                set_id = ?,
                start_date = ?,
                end_date = ?,
                is_active = ?
            WHERE period_id = ?
        ");

        return $stmt->execute([
            $period->getPeriodName(),
            $period->getSemester(),
            $period->getDepartment(),
            $period->getSetId(),
            $period->getStartDate(),
            $period->getEndDate(),
            $period->isActive() ? 1 : 0,
            $period->getPeriodId()
        ]);
    }


    /**
     * DELETE REPOSITORIES
     */
    public function delete(int $peridoId): bool
    {

        $stmt = $this->pdo->prepare("
          DELETE FROM evaluation_periods
          WHERE period_id = ?
        ");

        return $stmt->execute([$peridoId]);

    }


    /**
     * FORCE ACTIVE REPOSITORIES
     */

    //Find department by Id
    public function findDepartmentById(
        int $periodId
    ): ?string {

        $stmt = $this->pdo->prepare("
          SELECT target_dept
          FROM evaluation_periods
            WHERE period_id = ?
        ");

        $stmt->execute([$periodId]);

        $department = $stmt->fetchColumn();

        return $department !== false
        ? $department
        : null;

    }

    //Find if has active evaluation period
    public function hasActivePeriod(
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT 1
        FROM evaluation_periods
        WHERE target_dept = ?
          AND is_active = 1
        LIMIT 1
      ");

        $stmt->execute([$department]);

        return $stmt->fetchColumn() !== false;

    }

    //Find closest upcoming evaluation period
    public function findClosestUpcomingPeriod(
        string $department,
        string $now
    ): ?EvaluationPeriod {

        $stmt = $this->pdo->prepare("
        SELECT
            period_id,
            academic_year,
            semester,
            target_dept,
            set_id,
            start_date,
            end_date,
            is_active,
            is_closed,
            is_forced
        FROM evaluation_periods
        WHERE target_dept = ?
          AND start_date >= ?
        ORDER BY start_date ASC
        LIMIT 1 
      ");

        $stmt->execute([
          $department,
          $now
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data
            ? EvaluationPeriod::fromArray($data)
            : null;

    }


    //Force activate evaluation period
    public function forceActive(
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare("
          UPDATE evaluation_periods
          SET
              is_active = 1,
              is_forced = 1
          WHERE period_id = ?
        ");

        return $stmt->execute([$periodId]);

    }



    /**
     * FORCE CLOSE REPOSITORIES
     */

    //Count active students
    public function countActiveStudents(
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
          SELECT COUNT(*)
          FROM students s

          INNER JOIN programs p
            ON s.program_id = p.program_id
            
          WHERE s.is_active = 1
            AND p.department = ?
        ");

        $stmt->execute([$department]);

        return (int) $stmt->fetchColumn();

    }


    //Count Finished students
    public function countFinishedStudents(
        int $periodId,
        string $department
    ): int {

        $stmt = $this->pdo->prepare("
          SELECT COUNT(*)
          FROM students s
          INNER JOIN programs p
            ON s.program_id = p.program_id
            
          INNER JOIN (
              SELECT student_id
              FROM evaluation_status
              WHERE period_id = ?
              GROUP BY student_id
              HAVING
                  COUNT(*) > 0
                  AND SUM(is_submitted) = COUNT(*)
          ) completed
              ON completed.student_id = s.student_id

          WHERE s.is_active = 1
            AND p.department = ?
        ");

        $stmt->execute([
          $periodId,
          $department
        ]);

        return (int) $stmt->fetchColumn();

    }


    public function archiveParticipation(
        int $periodId,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
          INSERT INTO participation_history ( 
            period_id, 
            student_id, 
            full_name_at_time, 
            year_level_at_time, 
            dept_at_time, 
            program_name_at_time
            status 
          )

          SELECT 
            ?, 
            s.student_id, 
            s.full_name, 
            s.year_level, 
            p.department, 
            p.program_name,

          CASE 

            WHEN EXISTS (
                SELECT 1
                FROM evaluation_status es
                WHERE es.student_id = s.student_id
                  AND es.period_id = ?
                GROUP BY es.student_id
                HAVING
                    COUNT(*) > 0
                    AND SUM(es.is_submitted = 1) = COUNT(*)
            )
            THEN 'Completed'
 
            WHEN EXISTS ( 
              SELECT 1 
                FROM evaluation_status es 
                WHERE es.student_id = s.student_id 
                  AND es.period_id = ? 
            ) THEN 'Abandoned' 

            ELSE 'Never Started' 
          
          END 
            
        FROM students s

        INNER JOIN programs p 
          ON s.program_id = p.program_id 

        WHERE p.department = ? 
          AND s.is_active = 1
      ");

        return $stmt->execute([
          $periodId,
          $periodId,
          $periodId,
          $department
        ]);

    }


    //Queue Teacher Notifications
    #Note: this will be edited soon
    public function queueTeacherNotifications(
        int $periodId,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare(" 
          INSERT INTO teacher_notification_queue (
            teacher_id, 
            period_id, 
            status 
          ) 

          SELECT DISTINCT 
            t.teacher_id, 
            ?, 
            'pending'

          FROM teachers t 

          WHERE t.department = ? 
            AND t.is_active = 1 

            AND EXISTS ( 
              SELECT 1 
              FROM evaluation_status es 
              WHERE es.teacher_id = t.teacher_id
                AND es.period_id = ? 
                AND es.is_submitted = 1 
            ) 
                
        ");

        return $stmt->execute([ $periodId, $department, $periodId ]);

    }


    public function saveFinalStatistics(int $periodId): bool
    {
        $stmt = $this->pdo->prepare("
        UPDATE evaluation_periods ep 
        SET 
            /*Final average:
             * Only answers from students who completed All evaluations
             */
            ep.final_average = (
                SELECT ROUND( AVG(ea.score), 2 ) 

                FROM evaluation_answers ea 

                INNER JOIN evaluation_status es 
                    ON ea.eval_id = es.eval_id 

                INNER JOIN students s           
                    ON s.student_id = es.student_id 

                INNER JOIN programs p           
                    ON p.program_id = s.program_id 

                WHERE es.period_id = ? 
                    AND es.is_submitted = 1
                    AND s.is_active = 1 
                    AND p.department = ep.target_dept

                    AND es.student_id IN (
                      SELECT es_inner.student_id 
                      FROM evaluation_status es_inner 
                      WHERE es_inner.period_id = ? 
                      GROUP BY es_inner.student_id 
                      HAVING COUNT(*) > 0 
                         AND SUM( es_inner.is_submitted = 1 ) = COUNT(*)
                  )
            ), 
            
            /* Total completed students */
            ep.total_responses = (
                SELECT COUNT(*) 

                FROM (
                    SELECT 
                        es.student_id, 
                        p.department 

                    FROM evaluation_status es

                    INNER JOIN students s 
                        ON s.student_id = es.student_id

                    INNER JOIN programs p 
                        ON p.program_id = s.program_id 

                    WHERE es.period_id = ? 
                      AND s.is_active = 1 

                    GROUP BY 
                        es.student_id, 
                        p.department

                    HAVING COUNT(*) > 0 
                       AND SUM( es.is_submitted = 1 ) = COUNT(*)
                ) AS completed_students

                WHERE completed_students.department = ep.target_dept
            ), 
            
            /* Never started:
             * No evaluation_status exists at all
             */
            ep.total_unresponsive_students = (
                SELECT COUNT(*)

                FROM students s 

                INNER JOIN programs p
                    ON s.program_id = p.program_id 

                WHERE p.department = ep.target_dept 
                  AND s.is_active = 1 

                  AND NOT EXISTS (
                      SELECT 1 
                      FROM evaluation_status es 
                      WHERE es.student_id = s.student_id 
                        AND es.period_id = ?
                  )
            ), 
            
            /* Incomplete:
             * Started at least one evaluation 
             * but did NOT complete all evaluations
             */
            ep.total_incomplete_students = (
                SELECT COUNT(*) 

                FROM students s 

                INNER JOIN programs p ON s.program_id = p.program_id 

                WHERE p.department = ep.target_dept 
                    AND s.is_active = 1

                    AND EXISTS (
                      SELECT 1 
                      FROM evaluation_status es 
                      WHERE es.student_id = s.student_id 
                        AND es.period_id = ? 
                    )

                    AND EXISTS (
                        SELECT 1
                        FROM evaluation_status es
                        WHERE es.student_id = s.student_id
                            AND es.period_id = ?
                            AND es.is_submitted = 0
                    )

            ), 
            
            ep.participation_rate = (
                SELECT ROUND( 

                    COUNT(*) * 100.0 / 
                    
                    NULLIF( 
                        (
                            SELECT COUNT(*)

                            FROM students s2 

                            INNER JOIN programs p2 
                                ON s2.program_id = p2.program_id 

                            WHERE p2.department = ep.target_dept 
                                AND s2.is_active = 1
                        ), 
                    0 ),

                    2 
                ) 

                FROM (
                    SELECT 
                        es.student_id, 
                        p.department 

                    FROM evaluation_status es 

                    INNER JOIN students s 
                        ON s.student_id = es.student_id 

                    INNER JOIN programs p 
                        ON p.program_id = s.program_id 

                    WHERE es.period_id = ? 
                        AND s.is_active = 1 

                    GROUP BY 
                        es.student_id, 
                        p.department

                    HAVING COUNT(*) > 0 
                       AND SUM( es.is_submitted = 1 ) = COUNT(*)
                ) AS completed

                WHERE completed.department = ep.target_dept
            ), 
            
            ep.is_active = 0, 
            ep.is_closed = 1 

        WHERE ep.period_id = ?
    ");

        return $stmt->execute([
            $periodId,
            $periodId,
            $periodId,
            $periodId,
            $periodId,
            $periodId,
            $periodId,
            $periodId
        ]);
    }



    public function resetStudents(
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
          UPDATE students s 
          
          INNER JOIN programs p 
            ON s.program_id = p.program_id 
            
          SET 
            s.enrollment_type = NULL, 
            s.selected_load_ids = NULL, 
            s.is_finished_all = 0 
          
          WHERE p.department = ?
        ");

        return $stmt->execute([$department]);

    }


    /**
     * AUTO LOAD REPOSITORIES
     */

    //Activate scheduled evaluation periods
    public function activateScheduledPeriods(
        string $now
    ): int {

        $stmt = $this->pdo->prepare(" 
          UPDATE evaluation_periods 
          SET is_active = 1 
          WHERE is_active = 0
            AND start_date <= ? 
            AND end_date >= ? 
            AND is_closed = 0 
            AND is_forced = 0 
        ");

        $stmt->execute([ $now, $now ]);

        return $stmt->rowCount();

    }


    //Get all expired active periods
    public function findExpiredPeriods(
        string $now
    ): array {

        $stmt = $this->pdo->prepare("
        SELECT 
          period_id, 
          target_dept 
        FROM evaluation_periods 
        WHERE end_date < ? 
          AND is_active = 1 
          AND is_closed = 0 
        ");

        $stmt->execute([$now]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);

    }


    public function resetAllStudents(
        array $departments
    ): bool {

        if (empty($departments)) {
            return true;
        }

        $placeholders = implode(',', array_fill(0, count($departments), '?'));

        $stmt = $this->pdo->prepare(" 
          UPDATE students s

            INNER JOIN programs p 
              ON s.program_id = p.program_id 
            
            SET 
              s.enrollment_type = NULL, 
              s.selected_load_ids = NULL, 
              s.is_finished_all = 0 
              
            WHERE p.department IN ($placeholders) 
        ");

        return $stmt->execute($departments);

    }


    public function existLoad(
        string $studentId,
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 1
            FROM evaluation_status
            WHERE period_id = ?
                AND student_id = ? 
            LIMIT 1 
        ");

        $stmt->execute([
            $periodId,
            $studentId
        ]);

        return (bool) $stmt->fetchColumn();

    }


    public function checkSubmittedAllAssigned(
        string $studentId,
        int $periodId
    ): bool {

        $stmt = $this->pdo->prepare("
            SELECT 
                COUNT(*) AS total_assigned,
                COUNT(CASE WHEN is_submitted = 1 THEN 1 END) AS total_submitted
            FROM evaluation_status
            WHERE student_id = ?
                AND period_id = ?
        ");

        $stmt->execute([$studentId, $periodId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $result['total_assigned'] === $result['total_submitted'];

    }


    public function getEvaluatedTeacher(
        string $studentId,
        int $periodId
    ): array {

        $stmt = $this->pdo->prepare("
            SELECT 
                t.teacher_id,
                t.full_name,
                t.department,
                es.date_taken,
                COUNT(ea.answer_id) AS total_answers
            FROM evaluation_status es

            INNER JOIN teachers t 
                ON es.teacher_id = t.teacher_id

            LEFT JOIN evaluation_answers ea 
                ON es.eval_id = ea.eval_id

            WHERE es.student_id = ?
              AND es.period_id = ?
              AND es.is_submitted = 1
            GROUP BY es.eval_id
            ORDER BY t.full_name ASC
        ");

        $stmt->execute([$studentId, $periodId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }


    /**
     * Check if the students still need evaluation
     */
    public function stillNeedsEvaluation(
        string $studentId,
        int $periodId,
        string $department
    ): bool {

        $stmt = $this->pdo->prepare("
        SELECT EXISTS (
            SELECT 1
            FROM students s

            INNER JOIN programs p
                ON p.program_id = s.program_id

            WHERE s.student_id = ?
              AND p.department = ?
              AND s.is_active = 1

              AND (
                  -- Student has not started evaluating.
                  NOT EXISTS (
                      SELECT 1
                      FROM evaluation_status es
                      WHERE es.student_id = s.student_id
                        AND es.period_id = ?
                  )

                  OR

                  -- Student still has an unfinished evaluation.
                  EXISTS (
                      SELECT 1
                      FROM evaluation_status es
                      WHERE es.student_id = s.student_id
                        AND es.period_id = ?
                        AND COALESCE(es.is_submitted, 0) = 0
                  )
              )
        )
    ");

        $stmt->execute([
            $studentId,
            $department,
            $periodId,
            $periodId,
        ]);

        return (bool) $stmt->fetchColumn();
    }


}
