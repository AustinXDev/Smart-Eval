<?php

declare(strict_types=1);

use App\Repositories\EvaluationHistoryRepository;

header('Content-Type: application/json; charset=utf-8');

try {

    require_once __DIR__ . '/../../app/api_bootstrap.php';

    require_once __DIR__ . '/../../app/init.php';

    require_once __DIR__ . '/../../../app/config/database.php';
    require_once __DIR__ . '/../../../app/config/archive-database.php';

    if (!isset($pdo) || !$pdo instanceof PDO) {
        throw new RuntimeException(
            'Live database connection is unavailable.'
        );
    }

    if (!isset($archivePdo) || !$archivePdo instanceof PDO) {
        throw new RuntimeException(
            'Archive database connection is unavailable.'
        );
    }

    $department = $_GET['department'] ?? null;

    if ($department !== null) {
        if (!is_string($department)) {
            http_response_code(400);

            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid department.'
            ]);

            exit;
        }

        $department = strtolower(trim($department));

        if (!in_array($department, ['college', 'shs'], true)) {
            http_response_code(400);

            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid department.'
            ]);

            exit;
        }
    }

    // Initialize repository
    $historyRepository = new EvaluationHistoryRepository($pdo, $archivePdo);


    // Fetch evaluation history
    $history = $historyRepository->getEvaluationHistory($department);


    /*
     * Return response.
     */
    echo json_encode([
        'status' => 'success',
        'message' => 'Evaluation history retrieved successfully.',
        'data' => $history,
        'total' => count($history)
    ], JSON_THROW_ON_ERROR);

} catch (Throwable $e) {

    error_log(
        '[EVALUATION HISTORY] '
        . get_class($e)
        . ': '
        . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Unable to retrieve evaluation history.'
    ]);
}
