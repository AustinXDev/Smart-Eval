<?php

namespace App\Services\EvaluationServices;

use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use RuntimeException;

class EvaluationStatusService
{
    public function __construct(
        private EvaluationRepository $evalRepo,
        private EvaluationStatusRepository $evalStatusRepo,
        private TeacherRepository $teacherRepo,
        private QuestionRepositories $questionRepo
    ) {
    }


    public function getEvaluation(
        array $student
    ): array {

        $studentId = $student['student_id'];
        $department = $student['department'];

        if ($studentId === '' ||
          $department === ''
        ) {

            throw new RuntimeException(
                "Unauthorized"
            );

        }

        //get active period by department
        $period = $this->evalRepo->findActiveByDepartment(
            $department
        );

        if (!$period) {

            throw new RuntimeException("No active evaluation in this department.");

        }

        $periodId = $period['period_id'];

        $teachers = $this->teacherRepo->getAssignedTeachers($studentId, $periodId);

        $questions = $this->questionRepo->getQuestionsByPeriodId(
            $periodId
        );

        $evaluation = $this->evalStatusRepo->getStudentEvaluationData($studentId, $periodId);

        return [
          'teachers' => $teachers,
          'questions' => $questions,
          'evaluations' => $evaluation
        ];


    }


    public function markAsSubmitted(
        int $evalId
    ): void {

        if (!$this->evalStatusRepo->markAsSubmitted($evalId)) {
            throw new \RuntimeException(
                'Unable to mark the evaluation as submitted.'
            );
        }

    }


}
