<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$set_id =  $_POST['set_id'];
$new_name = $_POST['set_name'];

if(empty($set_id) || empty($new_name)){
  echo json_encode(['status' => 'error', 'message' => 'Missing field.']);
  exit;
}

try{
  $pdo->beginTransaction();

  $new_name = trim($new_name);

  if (strlen($new_name) < 3) {
    throw new Exception("Set name must be at least 3 characters.");
  }

  if(strlen($new_name) > 100) {
    throw new Exception("Set name must be at least 3 characters.");
  }

  //get current name
  $stmt = $pdo->prepare("SELECT set_name FROM question_sets WHERE set_id = ?");
  $stmt->execute([$set_id]);
  $existing = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$existing) {
      throw new Exception("Set not found.");
  }

  $current_name = $existing['set_name'];

  //no change detection
  if(strcasecmp(trim($current_name), $new_name) === 0) {
    echo json_encode([
      'status' => 'info',
      'message' => 'No changes were made to the set name.'
    ]);
    exit;
  }

  //Duplicate check
  $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM question_sets 
    WHERE LOWER(set_name) = LOWER(?) 
    AND set_id != ?
  ");
  $stmt->execute([$new_name, $set_id]);

  if($stmt->fetchColumn() > 0) {
    throw new Exception("Another question set already uses this name.");
  }

  //check if set is used in active evaluation
  $stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM evaluation_periods 
    WHERE set_id = ? 
    AND is_active = 1
  ");
  $stmt->execute([$set_id]);
  $is_active = $stmt->fetchColumn();

  if ($is_active > 0) {
    echo json_encode(['status' => 'error', 'message' => 'You cannot perform edit action because the set is in used']);
    exit;
  }


  //update set name
  $stmt = $pdo->prepare("UPDATE question_sets SET set_name = ? WHERE set_id = ?");
  $stmt->execute([$new_name, $set_id]);

  $pdo->commit();

  echo json_encode([
    'status' => 'success',
    'message' => 'Set name updated successfully.'
  ]);
}
catch (Exception $e){
  $pdo->rollBack();
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>