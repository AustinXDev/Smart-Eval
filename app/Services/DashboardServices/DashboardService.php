<?php 

namespace App\Services\DashboardServices;

use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\EvaluationRepo\EvaluationAnswerRepository;
use App\Repositories\ProgramRepo\ProgramRepository;
use RuntimeException;

class DashboardService
{

  public function __construct(
    private EvaluationRepository $evaluationRepo,
    private StudentRepository $studentRepo,
    private TeacherRepository $teacherRepo,
    private EvaluationStatusRepository $evalStatusRepo,
    private EvaluationAnswerRepository $evalAnswerRepo,
    private ProgramRepository $programRepo
  )
  {
  }

  
  public function getDashboard(
    string $department
  ): array {

    $department = trim($department);

    if ($department === '') {
        throw new RuntimeException(
            'Department is required.'
        );
    }


    /**
     * Get active evaluation period
     */
    $period = $this->evaluationRepo->findActiveByDepartment(
      $department
    );
    

    if(!$period) {

      return [
        'cards' => [

          'students_total'  => $this->studentRepo
                ->countActiveByDepartment($department),

          'teacher_total'   => $this->teacherRepo
                ->countActiveByDepartment($department),

          'completed_student' => 0,

          'not_evaluated' => 0,

          'total_submitted' => 0,

          'evaluation_period' => null

        ],

        'teacher_ranking' => [],

        'participation_chart' => null,
        
        'score_chart' => null,

        'categorical_breakdown' => [],
      ];

    }


    $periodId = (int) $period['period_id'];

    
    /**
     * Evaluation period
     */
    $evalPeriod = $this->evaluationRepo->findById(
      $periodId
    );


    /**
     * Student Total
     */
    $studentTotal = $this->studentRepo->countActiveByDepartment(
      $department
    );


    /**
     * Teacher Total
     */
    $teacherTotal = $this->teacherRepo->countActiveByDepartment(
      $department
    );


    /**
     * Completed Students
     */
    $completedStudent = $this->evalStatusRepo->countCompletedStudents(
      $department,
      $periodId
    );


    /**
     * Total submitted evaluations
     */
    $totalSubmitted = $this->evalStatusRepo->countTotalSubmitted(
      $department,
      $periodId
    );


    /**
     * Participation
     */
    $participation = $this->evalStatusRepo->getParticipation(
      $department,
      $periodId
    );


    /**
     * Students who have not started evaluation
     */
    $notEvaluatedTotal = $this->studentRepo->countNotEvaluated(
      $department,
      $periodId
    );


    /**
     * Teacher Ranking
     */
    $teacherRanking = $this->teacherRepo->getTeacherRanking(
      $periodId,
      $department
    );


    /**
     * Program Chart
     */
    $programRows = $this->programRepo->getProgramChart(
        $periodId,
        $department
    );

    $programLabels = [];
    $programFinished = [];
    $programNotFinished = [];
    $programTotals = [];

    foreach ($programRows as $row) {

        $programLabels[] =
            $row['program_name'];

        $programFinished[] =
            (int) $row['finished'];

        $programNotFinished[] =
            (int) $row['not_finished'];

        $programTotals[] =
            (int) $row['total_students'];
    }


    /**
     * Score Distribution
     */
    $scoreRows = $this->evalAnswerRepo->getScoreDistribution(
      $department,
      $periodId
    );

    $distribution = [
      5 => 0,
      4 => 0,
      3 => 0,
      2 => 0,
      1 => 0
    ];

    foreach ($scoreRows as $row) {
        $score = (int) $row['score'];

        if(isset($distribution[$score])) {
          $distribution[$score] = (int) $row['total_count'];
        }
    }


    /*
    *  Categorical breakdown
    */
    $categoricalBreakdown =
    $this->evalAnswerRepo
          ->getCategoricalBreakdown(
              $department,
              $periodId
    );


    /*
    * 11. Previous period
    */
    /*$previousPeriod =
        $this->evaluationRepo
            ->findPreviousInactive($department);
    */

            
    /*
    * 12. Participation change
    */
    $finishedChange = null;
    $isUp = true;

    return [
      'cards' => [

        'students_total'  => $studentTotal,

        'teacher_total'   => $teacherTotal,

        'completed_student' => $completedStudent,

        'not_evaluated' => $notEvaluatedTotal,

        'total_submitted' => $totalSubmitted,

        'evaluation_period' => $evalPeriod

      ],

      'teacher_ranking' => $teacherRanking,

      'participation_chart' => [

        'labels' => [
          'Completed',
          'Pending'
        ],

        'data' => [
          (int) $participation['finished'],
          (int) $participation['not_finished']
        ],

        'total' => (int) $participation['total_evaluators'],

        'finished' => (int) $participation['finished'],

        'not_finished' => (int) $participation['not_finished'],

        'finished_change' => $finishedChange,

        'is_up' => $isUp

      ],
      
      'score_chart' => [
        'labels' => [
          'Score 5',
          'Score 4',
          'Score 3',
          'Score 2',
          'Score 1'
        ],
        
        'data' => [
          $distribution[5],
          $distribution[4],
          $distribution[3],
          $distribution[2],
          $distribution[1]
        ]
      ],

      'program_chart' => [
          'labels' => $programLabels,

          'finished' => $programFinished,

          'not_finished' => $programNotFinished,

          'totals' => $programTotals,
      ],

      'categorical_breakdown' => $categoricalBreakdown,
    ];


    /**
     * 
     */


    
  }


}

?>