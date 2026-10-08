<?php


declare(strict_types=1);

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\students\TeacherSelectionController;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\StudentRepo\EvaluationRepository;
use App\Services\Student\TeacherSelectionService;

try {

    $student = $_SESSION['student'] ?? null;

    if (!$student) {
        http_response_code(401);

        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized'
        ]);

        exit;
    }


    $input = json_decode(
        file_get_contents('php://input'),
        true
    );

    if (!is_array($input)) {
        throw new RuntimeException(
            'Invalid request data.'
        );
    }

    $teacherIds = $input['teachers'] ?? [];

    require_once __DIR__ . '/../../app/config/database.php';

    $studentRepo = new StudentRepository($pdo);
    $evaluationRepo = new EvaluationRepository($pdo);

    $service = new TeacherSelectionService($studentRepo, $evaluationRepo);

    $controller = new TeacherSelectionController($service);

    $result = $controller->setTeacherSelection(
        $student,
        $teacherIds
    );

    /**
    * Update session
    */
    $_SESSION['student']['selected_load_ids'] = $teacherIds;

    echo json_encode([
        'success' => true,
        ...$result
    ]);

} catch (RuntimeException $e) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'error' => 'A database error occurred.'
    ]);
}
