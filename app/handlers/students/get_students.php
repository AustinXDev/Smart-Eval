<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

function get_AllStudents($department){
  global $pdo;

  $department_param = ucfirst($department);

  //get the student by department, join in program table
  $stmt = $pdo->prepare("
    SELECT s.*, p.department, p.program_name
    FROM students s
    INNER JOIN programs p ON s.program_id = p.program_id
    WHERE p.department = ?
    ORDER BY s.full_name ASC
  ");

  $stmt->execute([$department_param]);

  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$department = $_GET['department'] ?? '';

$students = get_AllStudents($department);
echo json_encode(['students' => $students]);
?>