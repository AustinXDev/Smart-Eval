<?php

namespace App\Services\Student;

use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\StudentRepo\EvaluationRepository;
use RuntimeException;

class EnrollmentSelectionService
{
    private const VALID_ENROLLMENT_TYPE = [
      'Regular',
      'Irregular'
    ];

    public function __construct(
        private StudentRepository $studentRepo,
        private TeacherRepository $teacherRepo,
        private EvaluationRepository $evaluationRepo
    ) {
    }

    public function setEnrollmentType(
        array $student,
        string $enrollmentType
    ): array {

        if (
            !in_array($enrollmentType, self::VALID_ENROLLMENT_TYPE, true)
        ) {
            throw new RuntimeException(
                "Invalid enrollment type: {$enrollmentType}"
            );
        }


        $studentId = $student['student_id'] ?? null;
        $programId = $student['program_id'] ?? null;
        $department = $student['department'] ?? null;
        $yearLevel = $student['year_level'] ?? null;

        if (!$studentId || !$programId || !$department || !$yearLevel) {
            throw new RuntimeException(
                'Incomplete student information.'
            );
        }


        /**
         * Get Active Evaluation Period Id
         */
        $periodId = $this->evaluationRepo->getActivePeriodId($department);

        if ($periodId === null) {
            throw new RuntimeException(
                "No active evaluation period found for department: {$department}"
            );
        }


        /*
         * REGULAR STUDENT
         *
         * Regular students must have teachers
         * assigned through teacher_load.
         */
        if ($enrollmentType === 'Regular') {

            $hasAvailableTeachers =
                $this->teacherRepo->hasTeacherAssigned(
                    $yearLevel,
                    $programId
                );

            if (!$hasAvailableTeachers) {
                throw new RuntimeException(
                    'No teachers are currently assigned to your program and year level. ' .
                    'Please choose Irregular to select your teachers manually, ' .
                    'or contact the administrator.'
                );
            }

            /*
             * Update enrollment type
             */
            $this->studentRepo->markEnrollmentType(
                $studentId,
                $enrollmentType
            );

            /*
             * Create evaluation status
             * for assigned teachers
             */
            $this->evaluationRepo->seedRegularCollegeEvaluationStatus(
                $studentId,
                $periodId,
                $programId,
                $yearLevel
            );

            return [
                'status' => 'success',
                'message' => 'Enrollment type set to Regular.',
                'redirect' => 'evaluation'
            ];
        }


        /*
         * IRREGULAR STUDENT
         *
         * No teacher assignment check is required.
         * The student will manually select teachers.
         */
        $this->studentRepo->markEnrollmentType(
            $studentId,
            $enrollmentType
        );

        return [
            'status' => 'success',
            'message' => 'Enrollment type set to Irregular.',
            'redirect' => 'teacher-selection'
        ];

    }

}
