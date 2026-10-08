<?php

namespace App\Services\EvaluationServices;

use App\Models\EvaluationPeriod;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use Exception;
use PDO

;
use PDOException;
use RuntimeException;

class EvaluationService
{
    public function __construct(
        private EvaluationRepository $evaluationRepo,
        private PDO $pdo
    ) {
    }


    public function getPeriodById(
        int $periodId
    ): EvaluationPeriod {

        if ($periodId <= 0) {

            throw new RuntimeException(
                "Invalid period ID."
            );

        }

        $period = $this->evaluationRepo->findById(
            $periodId
        );

        if (!$period) {
            throw new RuntimeException(
                'Period not found.'
            );
        }

        return $period;

    }


    public function getAllPeriods(): array
    {

        return $this->evaluationRepo->findAll();

    }


    public function getActivePeriodWithStudentStats(): array
    {

        return $this->evaluationRepo->findActivePeriodsWithStats();

    }


    public function getStudentEvaluationSummary(
        array $student
    ): array {

        $studentId = $student['student_id'] ?? "";
        $department = $student['department'] ?? '';

        $period = $this->evaluationRepo->findActiveByDepartment($department);

        if (!$period) {
            header("Location: unavailable");
        }

        $evaluatedTeachers = $this->evaluationRepo->getEvaluatedTeacher($studentId, (int)$period['period_id']);

        return [
            'period_name' => "{$period['academic_year']} - {$period['semester']}",
            'total_evaluated' => count($evaluatedTeachers),
            'evaluated_teachers' => $evaluatedTeachers
        ];


    }


    public function create(
        array $data
    ): EvaluationPeriod {

        $academicYear = trim(
            $data['academic_year'] ?? ''
        );

        $semester = trim(
            $data['semester'] ?? ''
        );

        $department = strtolower(trim(
            $data['department'] ?? ''
        ));

        $startDateInput = trim(
            $data['start_date'] ?? ''
        );

        $endDateInput = trim(
            $data['end_date'] ?? ''
        );

        $setId = (int) (
            $data['question_set'] ?? 0
        );


        /*
         * Required fields
         */
        if (
            $academicYear === '' ||
            $semester === '' ||
            $department === '' ||
            $startDateInput === '' ||
            $endDateInput === '' ||
            $setId <= 0
        ) {
            throw new RuntimeException(
                'All fields are required.'
            );
        }


        /*
         * Academic year validation
         */
        if (!preg_match(
            '/^\d{4}-\d{4}$/',
            $academicYear
        )) {
            throw new RuntimeException(
                'Academic year must be in format YYYY-YYYY.'
            );
        }

        /*
         * Date validation
         */
        $startTimestamp = strtotime(
            $startDateInput
        );

        $endTimestamp = strtotime(
            $endDateInput
        );

        if (
            $startTimestamp === false ||
            $endTimestamp === false
        ) {
            throw new RuntimeException(
                'Invalid date format.'
            );
        }


        /*
        * End date cannot be before start date
        */
        if ($endTimestamp < $startTimestamp) {
            throw new RuntimeException(
                'End date cannot be earlier than start date.'
            );
        }

        /*
         * Start date cannot be in the past
         */
        if ($startTimestamp < time()) {
            throw new RuntimeException(
                'Start date cannot be in the past.'
            );
        }

        /*
         * Normalize dates
         */
        $startDate = date(
            'Y-m-d H:i:s',
            $startTimestamp
        );

        $endDate = date(
            'Y-m-d H:i:s',
            $endTimestamp
        );

        if ($this->evaluationRepo->existBySemester(
            $academicYear,
            $semester,
            $department
        )) {
            throw new RuntimeException(
                'An evaluation period already exists.'
            );
        }

        /*
         * Determine active status
         */
        $now = time();

        $isActive =
            $now >= $startTimestamp &&
            $now <= $endTimestamp;


        $period = EvaluationPeriod::fromArray([
            'academic_year' => $academicYear,
            'semester'      => $semester,
            'target_dept'   => $department,
            'set_id'        => $setId,
            'start_date'    => $startDate,
            'end_date'      => $endDate,
            'is_active'     => $isActive
        ]);


        try {

            $this->pdo->beginTransaction();

            $period =
                $this->evaluationRepo->create(
                    $period
                );

            $this->pdo->commit();

            return $period;

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

    }


    public function update(
        array $data
    ): EvaluationPeriod {

        $periodId = (int) (
            $data['period_id'] ?? 0
        );

        $academicYear = trim(
            $data['update_academic_year'] ?? ''
        );

        $semester = trim(
            $data['update_semester'] ?? ''
        );

        $department = strtolower(trim(
            $data['update_department'] ?? ''
        ));

        $startDateInput = trim(
            $data['update_start_date'] ?? ''
        );

        $endDateInput = trim(
            $data['update_end_date'] ?? ''
        );

        $setId = (int) (
            $data['update_question_set'] ?? 0
        );


        if (
            $periodId <= 0 ||
            $academicYear === '' ||
            $semester === '' ||
            $department === '' ||
            $startDateInput === '' ||
            $endDateInput === '' ||
            $setId <= 0
        ) {
            throw new RuntimeException(
                'All fields are required.'
            );
        }

        /*
         * Academic year validation
         */
        if (!preg_match(
            '/^\d{4}-\d{4}$/',
            $academicYear
        )) {
            throw new RuntimeException(
                'Academic year must be in format YYYY-YYYY.'
            );
        }

        $startTimestamp = strtotime(
            $startDateInput
        );

        $endTimestamp = strtotime(
            $endDateInput
        );

        if (
            $startTimestamp === false ||
            $endTimestamp === false
        ) {
            throw new RuntimeException(
                'Invalid date format.'
            );
        }

        /*
         * Date validation
         */
        if ($endTimestamp < $startTimestamp) {
            throw new RuntimeException(
                'End date cannot be earlier than start date.'
            );
        }

        /*
        * Get existing period
        */
        $period = $this->evaluationRepo->findById(
            $periodId
        );

        if (!$period) {
            throw new RuntimeException(
                'Evaluation period not found.'
            );
        }

        $startDate = date(
            'Y-m-d H:i:s',
            $startTimestamp
        );

        $endDate = date(
            'Y-m-d H:i:s',
            $endTimestamp
        );

        /**
         * Check if have changes made
         */
        if (
            $period->getPeriodName() === $academicYear &&
            $period->getSemester() === $semester &&
            strtolower($period->getDepartment()) === strtolower($department) &&
            $period->getStartDate() === $startDate &&
            $period->getEndDate() === $endDate &&
            $period->getSetId() === $setId
        ) {
            throw new RuntimeException(
                'No changes were made to the evaluation period.'
            );
        }

        /*
         * Prevent editing active period
         */
        if ($period->isActive()) {
            throw new RuntimeException(
                'Cannot edit an active evaluation period.'
            );
        }

        /*
         * Prevent editing closed period
         */
        if ($period->isClosed()) {
            throw new RuntimeException(
                'Cannot edit a closed evaluation period.'
            );
        }

        /*
         * Duplicate check
         */
        if ($this->evaluationRepo->existBySemesterExclueId(
            $academicYear,
            $semester,
            $department,
            $periodId
        )) {
            throw new RuntimeException(
                'An evaluation period for this academic year, semester, and department already exists.'
            );
        }


        /*
         * Overlap check
         */
        if ($this->evaluationRepo->hasOverlapExcludeId(
            $academicYear,
            $semester,
            $department,
            date('Y-m-d H:i:s', $startTimestamp),
            date('Y-m-d H:i:s', $endTimestamp),
            $periodId
        )) {
            throw new RuntimeException(
                'Evaluation period overlaps with an existing schedule for this department and semester.'
            );
        }


        /*
         * Determine active status
         */
        $now = time();

        $isActive =
            $now >= $startTimestamp &&
            $now <= $endTimestamp;

        $updatedPeriod = EvaluationPeriod::fromArray([
            'period_id'     => $periodId,
            'academic_year' => $academicYear,
            'semester'      => $semester,
            'target_dept'   => $department,
            'set_id'        => $setId,
            'start_date'    => date(
                'Y-m-d H:i:s',
                $startTimestamp
            ),
            'end_date'      => date(
                'Y-m-d H:i:s',
                $endTimestamp
            ),
            'is_active'     => $isActive,
            'is_closed'     => false
        ]);

        try {

            $this->pdo->beginTransaction();

            $updated =
                $this->evaluationRepo->update(
                    $updatedPeriod
                );

            if (!$updated) {
                throw new RuntimeException(
                    'Failed to update evaluation period.'
                );
            }

            $this->pdo->commit();

            return $this->evaluationRepo->findById(
                $periodId
            );

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

    }


    public function delete(int $periodId): void
    {

        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid evaluation period ID.'
            );
        }

        /*
        * Check if period exists
        */
        $period = $this->evaluationRepo->findById(
            $periodId
        );

        if (!$period) {
            throw new RuntimeException(
                'Evaluation period not found.'
            );
        }

        /*
        * Prevent deleting active period
        */
        if ($period->isActive()) {
            throw new RuntimeException(
                'Cannot delete an active evaluation period.'
            );
        }

        /*
        * Prevent deleting closed period
        */
        if ($period->isClosed()) {
            throw new RuntimeException(
                'Cannot delete a closed evaluation period.'
            );
        }

        try {

            $this->pdo->beginTransaction();

            $deleted = $this->evaluationRepo->delete(
                $periodId
            );

            if (!$deleted) {
                throw new RuntimeException(
                    'Failed to delete evaluation period.'
                );
            }

            $this->pdo->commit();

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

    }


    public function forceActivate(
        int $periodId
    ): EvaluationPeriod {

        if ($periodId <= 0) {
            throw new RuntimeException(
                'Invalid Period.'
            );
        }

        /*
        * Get department
        */
        $department =
            $this->evaluationRepo->findDepartmentById(
                $periodId
            );

        if ($department === null) {
            throw new RuntimeException(
                'Period not found.'
            );
        }


        /*
        * Prevent another active period
        */
        if (
            $this->evaluationRepo->hasActivePeriod(
                $department
            )
        ) {
            throw new RuntimeException(
                'Another evaluation period is already active.'
            );
        }

        /*
        * Find closest upcoming period
        */
        $now = date(
            'Y-m-d H:i:s'
        );

        $closest =
        $this->evaluationRepo->findClosestUpcomingPeriod(
            $department,
            $now
        );

        if (!$closest) {
            throw new RuntimeException(
                'No upcoming period to activate.'
            );
        }

        if (
            $closest->getPeriodId() !== $periodId
        ) {
            throw new RuntimeException(
                'You can only activate the period closest to today.'
            );
        }

        try {

            $this->pdo->beginTransaction();

            $updated =
                $this->evaluationRepo->forceActive(
                    $periodId
                );

            if (!$updated) {
                throw new RuntimeException(
                    'Failed to activate evaluation period.'
                );
            }

            $this->pdo->commit();

        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }

        return $this->evaluationRepo->findById(
            $periodId
        );

    }


    public function forceClose(
        int $periodId
    ): array {

        $period = $this->evaluationRepo->findById($periodId);

        if (!$period) {
            throw new RuntimeException("Evaluation period not found.");
        }

        $department = $period->getDepartment();

        $totalStudents = $this->evaluationRepo->countActiveStudents($department);

        if ($totalStudents === 0) {

            throw new RuntimeException(
                "No active students found for this department."
            );

        }

        $totalFinished = $this->evaluationRepo->countFinishedStudents($periodId, $department);

        $participationRate =
            ($totalFinished / $totalStudents) * 100;

        if ($participationRate < 100) {
            throw new RuntimeException(
                "Cannot force close. Participation is only "
                . number_format($participationRate, 2)
                . "%."
            );
        }

        $this->pdo->beginTransaction();

        try {

            $this->evaluationRepo->archiveParticipation(
                $periodId,
                $department
            );

            $this->evaluationRepo->queueTeacherNotifications(
                $periodId,
                $department
            );

            $this->evaluationRepo->saveFinalStatistics(
                $periodId
            );

            $this->evaluationRepo->resetStudents(
                $department
            );

            $this->pdo->commit();


        } catch (\Throwable $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;

        }

        return [
          'period_id' => $periodId,
          'total_students' => $totalStudents,
          'total_finished' => $totalFinished,
          'paticipation_rate' => round(
              $participationRate,
              2
          )
        ];

    }


    public function runAutomaticUpdate(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->pdo->beginTransaction();

        try {

            // Activate scheduled evaluation periods
            $activated = $this->evaluationRepo->activateScheduledPeriods($now);

            // Find expired evaluation periods
            $expiredPeriods = $this->evaluationRepo->findExpiredPeriods($now);

            // Nothing expired, but activation may have happened
            if (empty($expiredPeriods)) {
                $this->pdo->commit();
                return;
            }

            $processed = 0;
            $departments = [];

            foreach ($expiredPeriods as $period) {

                $periodId = (int) $period['period_id'];
                $department = $period['target_dept'];

                $departments[] = $department;

                // Archive student participation
                $this->evaluationRepo->archiveParticipation(
                    $periodId,
                    $department
                );

                // Queue teacher notification
                $this->evaluationRepo->queueTeacherNotifications(
                    $periodId,
                    $department
                );

                // Save final statistics
                $this->evaluationRepo->saveFinalStatistics(
                    $periodId
                );

                $processed++;
            }

            $departments = array_values(
                array_unique($departments)
            );

            $this->evaluationRepo->resetAllStudents(
                $departments
            );

            $this->pdo->commit();

        } catch (Exception $e) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            error_log(
                'Evaluation automatic update failed: '
                . $e->getMessage()
            );
        }
    }

}
