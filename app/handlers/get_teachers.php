<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';


function get_Allteacher($department){
  global $pdo;

  if($department){
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE department = ? ORDER BY full_name ASC");
    $stmt->execute([$department]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

function get_TeacherById($id){
  global $pdo;

  $stmt = $pdo->prepare("SELECT * FROM teachers WHERE teacher_id = ?");
  $stmt->execute([$id]);
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_HandlesByTeacher($teacher_id){
    global $pdo;
    $stmt = $pdo->prepare("
          SELECT tl.year_level, p.program_name
          FROM teacher_load tl
          JOIN programs p ON tl.program_id = p.program_id
          WHERE tl.teacher_id = ?
    ");
    $stmt->execute([$teacher_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get department from query string
$department = $_GET['department'] ?? '';

$id = $_GET['id'] ?? '';

if($id){
    // Fetch single teacher
    $teacher = get_TeacherById($id);

    if($teacher){
        $teacher['handles'] = get_HandlesByTeacher($id);
    }

    echo json_encode($teacher ?: []);
} else if($department) {
    // Fetch all teachers in the department
    $teachers = get_Allteacher($department);
    echo json_encode($teachers);
} else {
    // Return empty array if no ID or department
    echo json_encode([]);
}
?>