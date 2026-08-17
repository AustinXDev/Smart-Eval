<?php 

namespace App\Services\LoginServices;

use App\Models\Student;
use App\Repositories\EvaluationRepository;
use App\Repositories\StudentRepository;


/**
 * Decides which page a student should land on right after login,
 * based on their evaluation period, enrollment type, and progress.
 * This is a straight extraction of the original if/elseif chain
 * into named, testable steps.
 */


class EvaluationRedirectResolver
{
  private const NO_EVALUATION      = 'unavailable';
  private const EVALUATION_DONE    = 'evaluation-done';
  private const EVALUATION_VIEW    = 'evaluation';
  private const SELECT_TEACHERS    = 'teacher-selection';
  private const ENROLLMENT_SELECT  = 'enrollment-selection';

  public function __construct(
    private StudentRepository $students,
    private EvaluationRepository $evaluations
  )
  {
  }

  public function resolve(Student $student): string
  {
    $department = $student->programId
    ? $this->students->getDepartment($student->programId) : null;

    $periodId = $this->evaluations->getActivePeriodId($department);
    $isShs = $department === 'shs';

    if(!$periodId) {
      return self::NO_EVALUATION;
    }

    if($student->isFinishedAll) {
      return self::EVALUATION_DONE;
    }

    if($isShs){
      $this->prepareShsStudent($student, $periodId);
    }

    return self::ENROLLMENT_SELECT;
  }

  private function prepareShsStudent(Student $student, int $periodId): void
  {
    $this->evaluations->seedShsEvaluationStatus(
      $student->studentId,
      $periodId,
      $student->programId,
      $student->yearLevel,
    );

     // SHS students are treated as Regular so they skip enrollment-type selection.

     $this->students->markEnrollmentType($student->studentId, 'Regular');
     $student->enrollmentType = 'Regular';
  }

  private function resolveByEnrollmentType(Student $student, int $periodId): string
  {
    if($student->enrollmentType !== 'Irregular') {
      return self::EVALUATION_VIEW;
    }

    $selectedCount = $this->evaluations->countSelectedTeachers($student->studentId, $periodId);

    return $selectedCount > 0 ? self::EVALUATION_VIEW : self::SELECT_TEACHERS;
  }


}

?>