<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

function get_Student($student_id){
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT s.*, p.program_name, p.department
        FROM students s
        INNER JOIN programs p ON s.program_id = p.program_id
        WHERE s.student_id = ?
        LIMIT 1
    ");
    $stmt->execute([$student_id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_AllStudents($department){
  global $pdo;

  //get the student by department, join in program table
  $stmt = $pdo->prepare("
    SELECT s.*, p.department, p.program_name
    FROM students s
    INNER JOIN programs p ON s.program_id = p.program_id
    WHERE p.department = ?
    AND s.is_active = 1
    ORDER BY s.full_name ASC
  ");

  $stmt->execute([$department]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$department = $_GET['department'] ?? '';
$student_id = $_GET['id'] ?? '';

if ($department) {
    $students = get_AllStudents($department);
    echo json_encode(['students' => $students]);
} else if ($student_id) {
    $student = get_Student($student_id);
    echo json_encode(['student' => $student]);
} else {
    echo json_encode([]);
}
?>