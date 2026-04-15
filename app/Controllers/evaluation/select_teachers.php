<?php
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

$student = $_SESSION['student'] ?? null;
if (!$student) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($student['enrollment_type'] !== 'Irregular') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only irregular students can select teachers']);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$teacher_ids = array_map('intval', $input['teachers'] ?? []);  // ← Frontend sends teacher_ids

if (empty($teacher_ids)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No teachers selected']);
    exit;
}

try {
    // Get student's program_id and department
    $stmt = $pdo->prepare("
        SELECT s.program_id, p.department
        FROM students s
        INNER JOIN programs p ON s.program_id = p.program_id
        WHERE s.student_id = ?
    ");
    $stmt->execute([$student['student_id']]);
    $info = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$info) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Student program not found']);
        exit;
    }

    // Get active evaluation period
    $stmt = $pdo->prepare("
        SELECT period_id
        FROM evaluation_periods
        WHERE is_active = 1
          AND target_dept = ?
        ORDER BY start_date DESC
        LIMIT 1
    ");
    $stmt->execute([$info['department']]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$period) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No active evaluation period']);
        exit;
    }
    $period_id = $period['period_id'];

    // Convert teacher_id → load_id using MIN (one per teacher, non-redundant)
    $placeholders = implode(',', array_fill(0, count($teacher_ids), '?'));
    $stmt = $pdo->prepare("
        SELECT MIN(tl.load_id) AS load_id, tl.teacher_id
        FROM teacher_load tl
        WHERE tl.teacher_id IN ($placeholders)
          AND tl.program_id = ?
        GROUP BY tl.teacher_id
    ");
    $stmt->execute(array_merge($teacher_ids, [$info['program_id']]));
    $load_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($load_rows)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'No valid teachers found for your program']);
        exit;
    }

    $valid_load_ids = array_column($load_rows, 'load_id');

    // Insert into evaluation_status
    $insert_stmt = $pdo->prepare("
        INSERT IGNORE INTO evaluation_status (student_id, load_id, period_id)
        VALUES (?, ?, ?)
    ");
    foreach ($valid_load_ids as $load_id) {
        $insert_stmt->execute([$student['student_id'], $load_id, $period_id]);
    }

    // Store resolved load_ids
    $load_ids_json = json_encode($valid_load_ids);
    $stmt = $pdo->prepare("UPDATE students SET selected_load_ids = ? WHERE student_id = ?");
    $stmt->execute([$load_ids_json, $student['student_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Teachers selected successfully',
        'selected_load_ids' => $valid_load_ids,
        'redirect' => '/Smart-Eval/views/student/evaluation.view.php'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>