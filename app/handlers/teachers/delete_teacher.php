<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$input = $_POST;
$teacher_id = $input['teacher_id'];

//check if id not empty
if(!$teacher_id){
  echo json_encode(['status' => 'error', 'message' => 'Teacher ID is missing.']);
  exit;
}

$stmt = $pdo->prepare("SELECT image_path, is_active FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$teacher = $stmt->fetch(PDO::FETCH_ASSOC);

//check if teacher found
if(!$teacher){
  echo json_encode(['status' => 'error', 'message' => 'Teacher not found.']);
  exit;
}

//check if teacher is inactive
if($teacher['is_active'] == 0){
  echo json_encode(['status' => 'error', 'message' => 'This teacher is already deactive.']);
  exit;
}

//check if these teacher is already present in evaluation
$stmt = $pdo->prepare("
    SELECT 1
    FROM teacher_load tl
    JOIN evaluation_periods ep 
      ON ep.is_active = 1
     AND ep.target_dept = (SELECT department FROM teachers WHERE teacher_id = tl.teacher_id)
    WHERE tl.teacher_id = ?
    LIMIT 1
");
$stmt->execute([$teacher_id]);
$is_evaluation_exist = $stmt->fetch(PDO::FETCH_ASSOC);

if($is_evaluation_exist){
  echo json_encode(['status' => 'error', 'message' => 'Cannot Delete: This teacher is currently being evaluated in an active period for their department.']);
  exit;
}

//check historical evaluation records
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM evaluation_status es JOIN teacher_load tl ON es.load_id = tl.load_id WHERE tl.teacher_id = ?");
$stmt->execute([$teacher_id]);
$count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

if($count > 0 ){
  echo  json_encode(['status' => 'warning', 'message' => "Restricted: This teacher has $count past evaluation records. Use 'Deactivate' instead of deleting."]);
  exit;
}

$stmt = $pdo->prepare("DELETE FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
 
echo json_encode(['status' => 'success', 'message' => 'Teacher Successfully delete.']);
exit;

?>