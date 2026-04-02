<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$set_name = trim($_POST['set_name']);

if(!$set_name){
  echo json_encode(['status' => 'error', 'message' => 'set name required']);
  exit;
}

try{
  $pdo->beginTransaction();

  //check duplicate 
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM question_sets WHERE set_name = ?");
  $stmt->execute([$set_name]);
  $hasDuplicate = $stmt->fetchColumn();

  if($hasDuplicate > 0){
    throw new Exception('Question set already exist.');
  }


  //insert question set
  $stmt = $pdo->prepare("INSERT INTO question_sets (set_name, is_active) VALUES (?, 1)");
  $stmt->execute([$set_name]);

  $pdo->commit();

  echo json_encode(['status' => 'success', 'message' => 'Question set successfully created']);

}
catch (Exception $e){
  $pdo->rollBack();
  echo  json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

?>