<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$teacher_id = $data['teacher_id'] ?? null;
$year_level = $data['year_level'] ?? null;
$program_name = $data['program_name'] ?? null;
$department = ucfirst($data['department']) ?? null;
$force_delete = $data['force_delete'] ?? false;

//empty field
if (!$teacher_id || !$year_level || !$program_name) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required data.']);
    exit;
}

//check for active evaluation by department
$stmt = $pdo->prepare("SELECT is_active FROM evaluation_periods WHERE target_dept = ? AND is_active = 1");
$stmt->execute([$department]);
$is_active_period = $stmt->fetch(PDO::FETCH_ASSOC);

if($is_active_period){
  echo json_encode([
        'status' => 'error',
        'message' => '🚫 Cannot delete: An evaluation is currently active. Please end the evaluation period first.'
    ]);
  exit;
}

//get teacher_load for this teacher handle
$stmt = $pdo->prepare("
    SELECT tl.load_id
    FROM teacher_load tl
    JOIN programs p ON tl.program_id = p.program_id
    WHERE tl.teacher_id = ? AND tl.year_level = ? AND p.program_name = ?
");
$stmt->execute([$teacher_id, $year_level, $program_name]);
$load = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$load) {
    echo json_encode(['status' => 'error', 'message' => 'Teacher handle not found.']);
    exit;
}

$load_id = $load['load_id'];

//check if this handle already evaluated by students
$stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluation_status WHERE load_id = ?");
$stmt->execute([$load_id]);
$studentCount = $stmt->fetchColumn();

if ($studentCount > 0 && !$force_delete) {
    echo json_encode([
        'status' => 'warning',
        'message' => "⚠️ Warning: {$studentCount} students have already evaluated this teacher. Deleting this handle will hide their results. Proceed anyway?"
    ]);
    exit;
}

//Delete the handle
$stmt = $pdo->prepare("DELETE FROM teacher_load WHERE load_id = ?");
if ($stmt->execute([$load_id])) {
    echo json_encode([
        'status' => 'success',
        'message' => '✅ Teacher handle deleted successfully.'
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to delete teacher handle.']);
}
?>