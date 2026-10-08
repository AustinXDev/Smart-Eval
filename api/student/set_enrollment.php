<?php


require_once __DIR__ . '/../../app/init.php';

use App\Controllers\students\EnrollmentSelectionController;
use App\Services\Student\EnrollmentSelectionService;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\StudentRepo\EvaluationRepository;

header(
    'Content-Type: application/json; charset=UTF-8'
);

try {

    require_once __DIR__ . '/../../app/config/database.php';

    $data = json_decode(
        file_get_contents('php://input'),
        true
    );

    $student = $_SESSION['student'] ?? null;

    if (!$student) {
        http_response_code(401);

        echo json_encode([
            'status' => 'error',
            'message' => 'Unauthorized'
        ]);

        exit;
    }

    $enrollmentType = $data['enrollmentType'] ?? null;

    $studentRepo = new StudentRepository($pdo);

    $teacherRepo = new TeacherRepository($pdo);

    $evaluationRepo = new EvaluationRepository($pdo);

    $service = new EnrollmentSelectionService(
        $studentRepo,
        $teacherRepo,
        $evaluationRepo
    );

    $controller = new EnrollmentSelectionController($service);

    $result = $controller->selectEnrollmentType(
        $student,
        $enrollmentType
    );


    /**
     * Update session
     */
    $_SESSION['student']['enrollment_type'] = $enrollmentType;

    echo json_encode($result);

} catch (\Throwable $e) {

    http_response_code(500);

    echo json_encode([
      'status' => 'error',
      'message' => $e->getMessage()
    ]);

}
