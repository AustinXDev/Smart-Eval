<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$student_id = $_POST['student_id'] ?? '';

if(!$student_id){
    echo json_encode(['status'=>'error','message'=>'Student ID is required.']);
    exit;
}

//reactive student
$stmt = $pdo->prepare("UPDATE students SET is_active = 1 WHERE student_id = ?");
$success = $stmt->execute([$student_id]);

if($success){
    echo json_encode(['status'=>'success','message'=>'Student reactivated successfully.']);
} else {
    echo json_encode(['status'=>'error','message'=>'Failed to reactivate student.']);
}
exit;
?>