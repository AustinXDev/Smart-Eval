<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

try{
  
  $stmt = $pdo->prepare("
    SELECT * FROM programs WHERE is_active = 1
  ");
  $stmt->execute();
  $programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode(['status' => 'success', 'data' => $programs]);

}
catch (Exception $e){
  echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>