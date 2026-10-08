<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$student = $_SESSION['student'] ?? null;
if (!$student) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($student['enrollment_type'] !== 'Irregular') {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only irregular students can select teachers']);
    exit;
}

// Get student's program
$stmt = $pdo->prepare("
    SELECT s.program_id
    FROM students s
    WHERE s.student_id = ?
");
$stmt->execute([$student['student_id']]);
$info = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$info) {
    echo json_encode(['success' => false, 'error' => 'Student program not found']);
    exit;
}

// Get ALL teachers in the program (deduplicated - one per teacher)
$stmt = $pdo->prepare("
    SELECT DISTINCT
        t.teacher_id,
        t.full_name,
        t.department
    FROM teacher_load tl
    INNER JOIN teachers t ON tl.teacher_id = t.teacher_id
    WHERE tl.program_id = ?
      AND t.is_active = 1
    ORDER BY t.full_name ASC
");
$stmt->execute([$info['program_id']]);
$teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode([
    'success' => true,
    'available_teachers' => $teachers
]);
?>