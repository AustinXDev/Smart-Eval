<?php 
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-type: application/json');

require_once __DIR__ . '/../../config/database.php';

$department = $_GET['department'] ?? null;
$req = $_GET['req'] ?? null;

switch($req){
  case 'get_dashboard_data':
    getDashboardData($department, $pdo);
    break;
  
  default:
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit();
}

function getDashboardData($department, $pdo){
    $data = [];

    //Student total
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM students s
      INNER JOIN programs p
        ON s.program_id = p.program_id
      WHERE p.department = ?
      AND s.is_active = 1
    ');
    $stmt->execute([$department]);
    $data['student_total'] = $stmt->fetchColumn();


    //Teacher Total
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM teachers
      WHERE department = ?
      AND is_active = 1
    ');
    $stmt->execute([$department]);
    $data['teacher_total'] = $stmt->fetchColumn();


    //Evaluation Period
    $stmt = $pdo->prepare('
      SELECT academic_year, semester, start_date, end_date
      FROM evaluation_periods
      WHERE target_dept = ?
      AND is_active = 1
    ');
    $stmt->execute([$department]);
    $data['evaluation_period'] = $stmt->fetch(PDO::FETCH_ASSOC);

    //total student completed
    $stmt = $pdo->prepare('
      SELECT COUNT(*)
      FROM students s
      INNER JOIN programs p
        ON s.program_id = p.program_id
      WHERE p.department = ?
      AND s.is_finished_all = 1
      AND s.is_active = 1
    ');
    $stmt->execute([$department]);
    $data['completed_student'] = $stmt->fetchColumn();

    echo json_encode($data);
}
?>