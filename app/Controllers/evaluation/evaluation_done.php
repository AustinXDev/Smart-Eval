<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$student = $_SESSION['student'] ?? null;
if (!$student) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

try {
    // Get student's program and department
    $stmt = $pdo->prepare("
        SELECT s.program_id, p.department
        FROM students s
        INNER JOIN programs p ON s.program_id = p.program_id
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student['student_id']]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        echo json_encode(['success' => false, 'error' => 'Student program not found']);
        exit;
    }

    // Get active evaluation period for student's department
    $stmt = $pdo->prepare("
        SELECT period_id, academic_year, semester, target_dept
        FROM evaluation_periods
        WHERE is_active = 1
          AND target_dept = ?
        ORDER BY start_date DESC
        LIMIT 1
    ");
    $stmt->execute([$info['department']]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$period) {
        echo json_encode([
            'success' => true,
            'period_name' => 'No Active Period',
            'total_evaluated' => 0,
            'evaluated_teachers' => []
        ]);
        exit;
    }

    // Get all teachers the student has submitted evaluations for in the current period
    // Only show those where is_submitted = 1
    $stmt = $pdo->prepare("
        SELECT DISTINCT
            t.teacher_id,
            t.full_name,
            t.department,
            es.date_taken,
            COUNT(ea.answer_id) as total_answers
        FROM evaluation_status es
        INNER JOIN teacher_load tl ON es.load_id = tl.load_id
        INNER JOIN teachers t ON tl.teacher_id = t.teacher_id
        LEFT JOIN evaluation_answers ea ON es.eval_id = ea.eval_id
        WHERE es.student_id = ?
          AND es.period_id = ?
          AND es.is_submitted = 1
        GROUP BY es.eval_id, t.teacher_id, t.full_name, t.department, es.date_taken
        ORDER BY t.full_name ASC
    ");
    $stmt->execute([$student['student_id'], $period['period_id']]);
    $evaluated_teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format period name
    $period_name = "{$period['academic_year']} - {$period['semester']}";

    echo json_encode([
        'success' => true,
        'period_name' => $period_name,
        'total_evaluated' => count($evaluated_teachers),
        'evaluated_teachers' => $evaluated_teachers
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>