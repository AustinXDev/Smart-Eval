<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$program_id = $_POST['program_id'] ?? '';

if(empty($program_id)){
  echo json_encode(['status' => 'error', 'message' => 'Missing fields.']);
  exit;
}

try{
  $pdo->beginTransaction();

  $stmt = $pdo->prepare("SELECT program_name, is_active FROM programs WHERE program_id = ?");
  $stmt->execute([$program_id]);
  $program = $stmt->fetch(PDO::FETCH_ASSOC);

  if(!$program){
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Program not found.']);
    exit;
  }

  $program_name = $program['program_name'];

  //Dependency checks

  //Check linked students
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE program_id = ?");
  $stmt->execute([$program_id]);
  $students_count = (int)$stmt->fetchColumn();

  //Check linked teacher loads
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM teacher_load WHERE program_id = ?");
  $stmt->execute([$program_id]);
  $teacher_count = (int)$stmt->fetchColumn();

  //Check active evaluation periods targeting this program's department
  $stmt = $pdo->prepare("
      SELECT 1 FROM evaluation_periods ep
      INNER JOIN programs p ON ep.target_dept = p.department
      WHERE ep.is_active = 1 AND p.program_id = ?
      LIMIT 1
  ");
  $stmt->execute([$program_id]);
  $active_eval = $stmt->fetch() ? true : false;

  //Decide deletion type
  if ($students_count > 0 || $teacher_count > 0 || $active_eval) {
      //Cannot fully delete -> Soft delete
      $stmt = $pdo->prepare("UPDATE programs SET is_active = 0 WHERE program_id = ?");
      $stmt->execute([$program_id]);

      $pdo->commit();
      echo json_encode([
          'status' => 'warning',
          'message' => "Program '$program_name' cannot be fully deleted because it has linked records or active evaluations. It has been deactivated instead."
      ]);
      exit;
  }

  //Safe to hard delete
  $stmt = $pdo->prepare("DELETE FROM programs WHERE program_id = ?");
  $stmt->execute([$program_id]);

  $pdo->commit();
  echo json_encode([
      'status' => 'success',
      'message' => "Program '$program_name' has been successfully deleted."
  ]);
  exit;

} catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>