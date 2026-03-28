<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$input = $_POST;

$student_id = $input['student_id'];
$full_name = $input['full_name'];
$email = $input['email'];
$year_level = $input['year'];
$program_id = $input['program'];

try{

  if(!$student_id || !$full_name || !$email || !$year_level || !$program_id){
      echo json_encode(['status'=>'error','message'=>'All fields are required.']);
      exit;
  }

  //check duplicate student id or email
  $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ? OR email = ?");
  $stmt->execute([$student_id, $email]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if($existing){
    if($existing['is_active'] == 0){
        echo json_encode([
            'status' => 'inactive',
            'message' => 'Student exists but inactive.',
            'student_id' => $existing['student_id']
        ]);
        exit;
    } else {
        echo json_encode([
            'status'=>'error',
            'message'=>'Student ID or Email already exists.'
        ]);
        exit;
    }
}

  //insert student
  $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, email, program_id, year_level, is_active) VALUES (?, ?, ?, ?, ?, 1)");
  $success = $stmt->execute([$student_id, $full_name, $email,$program_id, $year_level]);

  if($success){
      echo json_encode(['status'=>'success','message'=>"Student added successfully.$program_id"]);
  } else {
      echo json_encode(['status'=>'error','message'=>'Failed to add student.']);
  }

} catch (PDOException $e) {

  echo json_encode(['status'=>'error','message'=>'Database error: '.$e->getMessage()]);

}
exit;
?>