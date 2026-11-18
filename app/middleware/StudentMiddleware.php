<?php

namespace App\middleware;

use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;

class StudentMiddleware
{
    public function __construct(
        private StudentRepository $studentRepo,
        private EvaluationRepository $evaluationRepo
    ) {
    }

    public static function requireAuth(): array
    {
        $student = $_SESSION['student'] ?? null;

        if (!$student) {

            self::redirect('/Smart-Eval/public/login');
        }

        return $student;

    }


    public function handleEnrollmentRedirect(
        array $student,
        string $currentPage
    ): array {

        $enrollmentType = $student['enrollment_type'] ?? null;

        if (empty($enrollmentType)) {
            return [
                'status' => 'error',
                'message' => 'no enrollment type',
                'type' => $enrollmentType
            ];
        }

        /**
         * Student is already Regular
         */
        if ($enrollmentType === 'Regular' && $currentPage === 'enrollment-selection') {

            self::redirect('/Smart-Eval/public/evaluation');

            return [
                'status' => 'success',
                'message' => 'Regular student.',
                'type' => $enrollmentType
            ];
        }

        /**
         * student is already Irregular
         */
        if (
            $enrollmentType === 'Irregular' &&
            $currentPage === 'enrollment-selection'
        ) {

            self::redirect('/Smart-Eval/public/teacher-selection');

            return [
                'status' => 'success',
                'message' => 'Irregular student.',
                'type' => $enrollmentType
            ];

        }

        return [
                'status' => 'error',
                'message' => 'no enrollment type',
                'type' => $enrollmentType
            ];

    }


    public function preventDuplicateTeacherSelection(
        array $student,
        string $currentPage
    ): void {

        $enrollmentType = $student['enrollment_type'] ?? null;
        $department = $student['department'] ?? null;

        // Teacher selection is only for Irregular students.
        if ($enrollmentType === 'Regular' && $currentPage === 'teacher-selection') {
            self::redirect('/Smart-Eval/public/evaluation');
        }

        if (
            $enrollmentType !== 'Irregular' ||
            $currentPage !== 'teacher-selection'
        ) {
            return;
        }

        $period = $this->evaluationRepo->findActiveByDepartment($department);

        $periodId = $period['period_id'] ?? null;

        if (!$periodId) {
            self::redirect('/Smart-Eval/public/unavailable');
        }

        $studentId = $student['student_id'] ?? null;

        $hasEvaluation = $this->studentRepo->hasEvaluationStatus($studentId, $periodId);

        if ($hasEvaluation) {
            self::redirect('/Smart-Eval/public/evaluation');
        }



    }


    public function evaluationProtect(
        array $student
    ): void {
        $studentId = $student['student_id'] ?? null;
        $department = $student['department'] ?? null;
        $studentEnrollmentType = $student['enrollment_type'] ?? null;

        // Guard against unauthenticated
        if (!$studentId || !$department) {
            self::redirect('/Smart-Eval/public/login');
            return;
        }

        // Check active evaluation period
        $period = $this->evaluationRepo->findActiveByDepartment($department);
        $periodId = $period['period_id'] ?? null;

        if (!$periodId) {
            self::redirect('/Smart-Eval/public/unavailable');
            return;
        }

        // Check enrollment selection
        if (empty(trim((string)$studentEnrollmentType))) {
            self::redirect('/Smart-Eval/public/enrollment-selection');
            return;
        }

        // Check assigned loads / teacher selection
        $hasSetLoads = $this->evaluationRepo->existLoad($studentId, $periodId);

        if (!$hasSetLoads && $studentEnrollmentType === 'Regular') {
            // Regular students never pick teachers. Their rows come from teacher_load,
            // so rebuild them if they are missing (e.g. a new period started).
            $this->evaluationRepo->seedAssignedLoads(
                $studentId,
                (int) $periodId,
                (int) ($student['program_id'] ?? 0),
                (string) ($student['year_level'] ?? '')
            );

            $hasSetLoads = $this->evaluationRepo->existLoad($studentId, $periodId);

            if (!$hasSetLoads) {
                // No teachers assigned to the program/year: let the student choose again.
                $this->studentRepo->clearEnrollmentType($studentId);
                unset($_SESSION['student']['enrollment_type']);
                self::redirect('/Smart-Eval/public/enrollment-selection');
            }
        }

        if (!$hasSetLoads) {
            self::redirect('/Smart-Eval/public/teacher-selection');
            return;
        }

        // Check completion status
        $isSubmittedAll = $this->evaluationRepo->checkSubmittedAllAssigned($studentId, $periodId);

        if ($isSubmittedAll) {
            self::redirect('/Smart-Eval/public/evaluation-done');
            return;
        }

    }


    private static function redirect(string $location): never
    {

        header("Location: {$location}");
        exit;

    }

}
