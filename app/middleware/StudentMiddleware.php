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
