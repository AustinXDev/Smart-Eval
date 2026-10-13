<?php

namespace App\Services\Student;

use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\EvaluationRepository;
use RuntimeException;

class TeacherSelectionService
{
    public function __construct(
        private StudentRepository $studentRepo,
        private EvaluationRepository $evaluationRepo
    ) {
    }


    public function selectedTeachers(
        array $student,
        array $teacherIds
    ): array {

        /*
           * Only Irregular students can manually
           * select teachers.
           */
        if (($student['enrollment_type'] ?? null) !== 'Irregular') {
            throw new RuntimeException(
                'Only irregular students can select teachers.'
            );
        }


        /*
         * Validate teacher selection
         */
        if (empty($teacherIds)) {
            throw new RuntimeException(
                'No teachers selected.'
            );
        }


        if (count($teacherIds) > 200) {
            throw new RuntimeException('Too many teachers selected.');
        }

        foreach ($teacherIds as $id) {
            if ((!is_int($id) && !is_string($id)) || !ctype_digit((string) $id) || (int) $id < 1) {
                throw new RuntimeException('Invalid teacher ID.');
            }
        }
        $teacherIds = array_values(array_unique(array_map('intval', $teacherIds)));

        $studentId = $student['student_id'] ?? null;
        $department = $student['department'] ?? null;


        if (!$studentId) {
            throw new RuntimeException(
                'Invalid student session.'
            );
        }


        /**
         * Get active period
         */
        $periodId = $this->evaluationRepo->getActivePeriodId($department);

        if ($periodId === null) {
            throw new RuntimeException(
                'No active evaluation period.'
            );
        }


        /**
         * Check if have duplicate teacher to evaluate
         */


        /**
         * Create evaluation status
         */
        $this->evaluationRepo->saveEligibleTeacherSelection($studentId, $teacherIds, $periodId);

        return [
            'status' => 'success',
            'message' => 'Teachers selected successfully.',
            'selected_teacher_ids' => $teacherIds,
            'redirect' => 'evaluation'
        ];

    }

}
