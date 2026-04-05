<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

$student = $_SESSION['student'] ?? null;

if (!$student) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$teachers = $input['teachers'] ?? [];

if (empty($teachers)) {
    echo json_encode(['status' => 'error', 'message' => 'No teachers selected']);
    exit;
}

try {

    // Get active period based on department
    $stmt = $pdo->prepare("
        SELECT ep.period_id
        FROM evaluation_periods ep
        JOIN programs p ON p.program_id = ?
        WHERE ep.is_active = 1
        AND ep.target_dept = p.department
        LIMIT 1
    ");
    $stmt->execute([$student['program_id']]);
    $period = $stmt->fetch();

    if (!$period) {
        echo json_encode(['status' => 'error', 'message' => 'No active period']);
        exit;
    }

    $period_id = $period['period_id'];

    // Insert selected teachers
    $placeholders = implode(',', array_fill(0, count($teachers), '?'));

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO evaluation_status (student_id, load_id, period_id)
        SELECT ?, load_id, ?
        FROM teacher_load
        WHERE teacher_id IN ($placeholders)
    ");

    $stmt->execute(array_merge(
        [$student['student_id'], $period_id],
        $teachers
    ));

    echo json_encode([
        'status' => 'success',
        'redirect' => '/Smart-Eval/views/student/evaluation.view.php'
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error'
    ]);
}