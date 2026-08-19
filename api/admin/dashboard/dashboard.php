<?php 

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/../../../app/init.php';

/**
 * Repositories
 */
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\EvaluationRepo\EvaluationAnswerRepository;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\ProgramRepo\ProgramRepository;

/**
 * Service
 */
use App\Services\DashboardServices\DashboardService;

/**
 * Controller
 */
use App\Controllers\dashboard\DashboardApiController;


require_once __DIR__ . '/../../../app/config/database.php';

$evaluationRepo = new EvaluationRepository($pdo);

$evaluationAnswerRepo = new EvaluationAnswerRepository($pdo);

$evaluationStatusRepo = new EvaluationStatusRepository($pdo);

$studentRepo = new StudentRepository($pdo);

$teacherRepo = new TeacherRepository($pdo);

$programRepo = new ProgramRepository($pdo);

$service = new DashboardService(
  $evaluationRepo,
  $studentRepo,
  $teacherRepo,
  $evaluationStatusRepo,$evaluationAnswerRepo,
  $programRepo
);


$controller = new DashboardApiController(
  $service
);

$controller->bundle();
?>