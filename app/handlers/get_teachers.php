<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

//$department = $_GET['department'] ?? '';

function get_teacher($department){
  global $pdo;

  if($department){
    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE department = ? ORDER BY full_name ASC");
    $stmt->execute([$department]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}

// Get department from query string
$department = $_GET['department'] ?? '';

// Fetch teachers
$teachers = get_teacher($department);

// Send JSON
echo json_encode($teachers);

?>