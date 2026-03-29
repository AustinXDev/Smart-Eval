<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$data = json_encode($_POST);

$academic_year = $_POST['academic_year'] ?? '';
$semester = $_POST['semester'] ?? '';
$department = $_POST['department'] ?? '';
$start_date = $_POST['start_date'] ?? '';
$end_date = $_POST['end_date'] ?? '';
$question_set_id = $_POST['question_set'] ?? null;

// basic validation empty fields
if(!$academic_year || !$semester || !$department || !$start_date || !$end_date || !$question_set_id) {
  echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
  exit;
}

// Academic year format validation (e.g., 2023-2024)
if(!preg_match('/^\d{4}-\d{4}$/', $academic_year)){
  echo json_encode(['status' => 'error', 'message' => 'Academic year must be in format YYYY-YYYY.']);
  exit;
}

//date validation
if(strtotime($end_date) < strtotime($start_date)){
  echo json_encode(['status' => 'error', 'message' => 'End date cannot be earlier than start date.']);
  exit;
}

//prevent pass date
if(strtotime($start_date) < time()){
  echo json_encode(['status' => 'error', 'message' => 'Start date cannot be in the past.']);
  exit;
}

//duplicate semester and academic year
$checkSemester = $pdo->prepare("
        SELECT COUNT(*)
        FROM evaluation_periods
        WHERE academic_year = ?
        AND semester = ?
        AND target_dept = ?
  ");

$checkSemester->execute([$academic_year, $semester, $department]);
$countSemester = $checkSemester->fetchColumn();

if($countSemester > 0){
  echo json_encode(['status' => 'error', 'message' => 'An evaluation period for this academic year, semester, and department already exists.']);
}


//prevent duplicate active
$check = $pdo->prepare("
              SELECT COUNT(*) FROM evaluation_periods
              WHERE academic_year = ?
              AND semester = ?
              AND target_dept = ?
              AND (
                (start_date BETWEEN ? AND ?) OR
                (end_date BETWEEN ? AND ?)
              )
");

$check->execute([$academic_year, $semester, $department, $start_date, $end_date, $start_date, $end_date]);
$count = $check->fetchColumn();

if($count > 0){
  echo json_encode(['status' => 'error', 'message' => 'Evaluation period overlaps with existing schedule.']);
  exit;
}

//set active if start date is today
$now = date('Y-m-d H:i:s');

$is_active = 0;

if($now >= $start_date && $now <= $end_date){
  $is_active = 1;
}

//Insert evaluation period
$stmt = $pdo->prepare("INSERT INTO evaluation_periods (academic_year, semester, target_dept, set_id, start_date, end_date
, is_active) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$academic_year, $semester, $department, $question_set_id, $start_date, $end_date, $is_active]);

echo json_encode(['status' => 'success', 'message' => 'Evaluation period created successfully.']);
?>