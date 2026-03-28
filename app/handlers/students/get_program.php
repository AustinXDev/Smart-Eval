<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

function getAllPrograms($department){
  global $pdo;

  if(!$department) return [];

  $stmt = $pdo->prepare("SELECT program_id, program_name FROM programs WHERE department = ? AND is_active = 1 ORDER BY program_name ASC");
  $stmt->execute([$department]);
  
  return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$department = $_GET['department'] ?? '';

$programs = getAllPrograms($department);
echo json_encode(['programs' => $programs]);
?>