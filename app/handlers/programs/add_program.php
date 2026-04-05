<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$program_code = $_POST['program_code'] ?? '';
$program_name = $_POST['program_name'] ?? '';
$department = $_POST['department'] ?? '';

if(empty($program_code) || empty($program_name) || empty($department)){
  echo json_encode(['status' => 'error', 'message' => 'Missing Fields.']);
  exit;
}

try{
  $pdo->beginTransaction();

  //check character length
  if (strlen($program_code) < 2 || strlen($program_code) > 15) {
    throw new Exception('Program Code must be 2-15 characters long.');
  }

  if (strlen($program_name) < 10 || strlen($program_name) > 100) {
    throw new Exception('Program Name must be 10-100 characters long.');
  }

  //Unique program code
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE program_code = ?");
  $stmt->execute([$program_code]);
  if ($stmt->fetchColumn() > 0) {
    throw new Exception("Program Code '$program_code' is already registered.");
  }

  //Unique program name
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE LOWER(program_name) = LOWER(?)");
  $stmt->execute([$program_name]);
  if ($stmt->fetchColumn() > 0) {
    throw new Exception("Program Name '$program_name' is already registered.");
  }

  //Insert new program
  $stmt = $pdo->prepare("
    INSERT INTO programs (program_code, program_name, department, is_active)
    VALUES (?, ?, ?, 1)
  "); 

  $success = $stmt->execute([$program_code, $program_name, $department]);

  $pdo->commit();

  echo json_encode(['status' => 'success', 'message' => "Program '$program_name' ($program_code) has been successfully added."]);
}
catch (Exception $e) {
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>