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

function get_TeacherCountByDepartment($department){
  global $pdo;

  $stmt = $pdo->prepare("
        SELECT 
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END),0) AS active,
            COALESCE(SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END),0) AS inactive
        FROM teachers
        WHERE department = ?
    ");
    $stmt->execute([$department]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

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
    // Fetch all teachers
    $teachers = get_Allteacher($department);
    $counts = get_TeacherCountByDepartment($department);
    echo json_encode([
      'counts' => $counts,
      'teachers' => $teachers
    ]);
} else {
    echo json_encode([]);
}
?>