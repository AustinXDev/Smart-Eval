<?php 
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';

$new_id     = trim($_POST['student_id'] ?? '');
$old_id     = trim($_POST['old_student_id'] ?? '');
$full_name  = trim($_POST['full_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$year_level = trim($_POST['year_level'] ?? '');
$program_id = trim($_POST['program_id'] ?? '');

if(empty($new_id) || empty($old_id) || empty($full_name) || empty($email) || empty($year_level) || empty($program_id)){
    echo json_encode(['status' => 'error', 'message' => "All fields are required."]);
    exit;
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
    exit;
}

try {
    $pdo->beginTransaction();

    if($old_id !== $new_id){
        $stmt = $pdo->prepare("SELECT student_id FROM students WHERE student_id = ?");
        $stmt->execute([$new_id]);
        if($stmt->fetch()){
            echo json_encode(['status' => 'error', 'message' => 'The new Student ID is already taken.']);
            $pdo->rollBack();
            exit;
        }
    }

    $stmt = $pdo->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
    $stmt->execute([$email, $old_id]);
    if($stmt->fetch()){
        echo json_encode(['status' => 'error', 'message' => 'Email is already used by another student.']);
        $pdo->rollBack();
        exit;
    }

    $stmt = $pdo->prepare("SELECT program_id FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    if(!$stmt->fetch()){
        echo json_encode(['status' => 'error', 'message' => 'Selected program does not exist.']);
        $pdo->rollBack();
        exit;
    }

    $stmt = $pdo->prepare("
        UPDATE students 
        SET student_id = ?, 
            full_name = ?, 
            email = ?, 
            year_level = ?, 
            program_id = ?
        WHERE student_id = ?
    ");

    $stmt->execute([
        $new_id,
        $full_name,
        $email,
        $year_level,
        $program_id,
        $old_id 
    ]);

    $pdo->commit();
    
    echo json_encode(['status' => 'success', 'message' => 'Student record and all linked evaluations updated successfully.']);

} catch (PDOException $e) {

    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    http_response_code(500); 
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>