<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../middleware/require_auth.php';

// ============================
// GET STUDENT FROM SESSION
// ============================
$student = $_SESSION['student'] ?? null;

if (!$student) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Unauthorized'
    ]);
    exit;
}

// ============================
// GET POST DATA
// ============================
$enrollment_type = $_POST['enrollment_type'] ?? '';

if (!in_array($enrollment_type, ['Regular', 'Irregular'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid enrollment type'
    ]);
    exit;
}

// ============================
// UPDATE SESSION
// ============================
$_SESSION['student']['enrollment_type'] = $enrollment_type;

// Extract needed data
$student_id = $student['student_id'];
$program_id = $student['program_id'];
$year_level = $student['year_level'];

try {

    //update database
    $stmt = $pdo->prepare("
      UPDATE students 
      SET enrollment_type = ?
      WHERE student_id = ?
      ");
    $stmt->execute([$enrollment_type, $student_id]);

    // ============================
    // GET ACTIVE PERIOD
    // ============================
    $stmt = $pdo->prepare("
        SELECT ep.period_id
        FROM evaluation_periods ep
        JOIN programs p ON p.program_id = ?
        WHERE ep.is_active = 1
        AND ep.target_dept = p.department
        LIMIT 1
    ");

    $stmt->execute([$program_id]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$period) {
        echo json_encode([
            'status' => 'error',
            'message' => 'No active evaluation period'
        ]);
        exit;
    }

    $period_id = $period['period_id'];

    // ============================
    // REGULAR FLOW
    // ============================
    if ($enrollment_type === 'Regular') {

        $stmt = $pdo->prepare("
            INSERT IGNORE INTO evaluation_status (student_id, load_id, period_id)
            SELECT ?, load_id, ?
            FROM teacher_load
            WHERE program_id = ? AND year_level = ?
        ");

        $stmt->execute([
            $student_id,
            $period_id,
            $program_id,
            $year_level
        ]);

        echo json_encode([
            'status' => 'success',
            'type' => 'Regular',
            'redirect' => '/Smart-Eval/views/student/evaluation.view.php'
        ]);
        exit;
    }

    // ============================
    // IRREGULAR FLOW
    // ============================
    if ($enrollment_type === 'Irregular') {

        echo json_encode([
            'status' => 'success',
            'type' => 'Irregular',
            'redirect' => '/Smart-Eval/views/student/select_teachers.php'
        ]);
        exit;
    }

} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error',
    ]);
    exit;
}