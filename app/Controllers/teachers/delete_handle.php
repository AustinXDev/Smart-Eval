<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

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
$stmt = $pdo->prepare("SELECT COUNT(*) FROM evaluation_status WHERE load_id = ? AND is_submitted = 1");
$stmt->execute([$load_id]);
$studentCount = $stmt->fetchColumn();

if ($studentCount > 0) {
    $stmt = $pdo->prepare("UPDATE teacher_load SET is_active = 0 WHERE load_id = ?");
    if ($stmt->execute([$load_id])) {
        echo json_encode([
            'status' => 'success',
            'message' => "⚠️ Load contains {$evaluationCount} evaluations. It has been deactivated instead of deleted to preserve data integrity."
        ]);
    }
} else {
    // If NO students have evaluated yet, it is safe to actually delete
    $stmt = $pdo->prepare("DELETE FROM teacher_load WHERE load_id = ?");
    if ($stmt->execute([$load_id])) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Unused teacher handle deleted successfully.'
        ]);
    }
} 

echo json_encode(['status' => 'error', 'message' => 'Failed to delete teacher handle.']);

?>