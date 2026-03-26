<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$input = $_POST;

$teacher_id = $input['teacher_id']?? '';
$department = $input['department'] ?? '';
$program = $input['program'] ?? '';
$level = $input['level'] ?? '';


//check if not empty
if(!$teacher_id || !$program || !$level){
  echo json_encode(['status'=>'error','message'=> '❗ Select Teacher, Program, Year Level']);
  exit;
}

//check if teacher is active
$stmt = $pdo->prepare("SELECT is_active FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$active_teacher = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$active_teacher || $active_teacher['is_active'] == 0){
  echo json_encode(['status'=>'error','message'=> 'Select Teacher, Program, Year Level']);
  exit;
}

//check active period
$stmt = $pdo->prepare("SELECT * FROM evaluation_periods WHERE target_dept = ? AND is_active = 1");
$stmt->execute([$department]);
$period = $stmt->fetch(PDO::FETCH_ASSOC);
if($period && $period['is_active'] == 1){
    echo json_encode([
        'status'=>'error',
        'message'=>'⚠️ Cannot add handles: There is an active evaluation period for this department.'
    ]);
    exit;
}

//check if duplicate handle
$stmt = $pdo->prepare("SELECT 1 FROM teacher_load WHERE teacher_id = ? AND year_level = ? AND program_id = ?
");
$stmt->execute([$teacher_id, $level, $program]);
$stmtDuplicate = $stmt->fetch(PDO::FETCH_ASSOC);

if($stmtDuplicate){
   echo json_encode([
        'status' => 'error',
        'message' => '⚠️ This teacher is already assigned to this program and year level.'
    ]);
    exit;

}

//insert handle
$stmt = $pdo->prepare("
    INSERT INTO teacher_load (teacher_id, program_id, year_level)
    VALUES (?, ?, ?)
");

$stmt->execute([$teacher_id, $program, $level]);

echo json_encode([
    'status' => 'success',
    'message' => '✅ Handle added successfully.'
]);
exit;

?>