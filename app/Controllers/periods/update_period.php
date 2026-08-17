<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

date_default_timezone_set('Asia/Manila');

$period_id     = $_POST['period_id'] ?? null;
$academic_year = $_POST['update_academic_year'] ?? '';
$semester      = $_POST['update_semester'] ?? '';
$department    = $_POST['update_department'] ?? '';
$start_date    = $_POST['update_start_date'] ?? '';
$end_date      = $_POST['update_end_date'] ?? '';
$question_set_id = $_POST['update_question_set'] ?? null;

// Basic validation
if (!$period_id || !$academic_year || !$semester || !$department || !$start_date || !$end_date || !$question_set_id) {
    echo json_encode(['status' => 'error', 'message' => 'All fields are required.']);
    exit;
}

$start_date = date("Y-m-d H:i:s", strtotime($start_date));
$end_date   = date("Y-m-d H:i:s", strtotime($end_date));

// Academic year format validation
if (!preg_match('/^\d{4}-\d{4}$/', $academic_year)) {
    echo json_encode(['status' => 'error', 'message' => 'Academic year must be in format YYYY-YYYY.']);
    exit;
}

// Date validation
if (strtotime($end_date) < strtotime($start_date)) {
    echo json_encode(['status' => 'error', 'message' => 'End date cannot be earlier than start date.']);
    exit;
}

// Check if period exists
$checkExists = $pdo->prepare("SELECT * FROM evaluation_periods WHERE period_id = ?");
$checkExists->execute([$period_id]);
$existingPeriod = $checkExists->fetch(PDO::FETCH_ASSOC);

if (!$existingPeriod) {
    echo json_encode(['status' => 'error', 'message' => 'Evaluation period not found.']);
    exit;
}

// Prevent editing active or closed periods
if ($existingPeriod['is_active'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot edit an active evaluation period.']);
    exit;
}

if ($existingPeriod['is_closed'] == 1) {
    echo json_encode(['status' => 'error', 'message' => 'Cannot edit a closed evaluation period.']);
    exit;
}

// Duplicate check — exclude current period
$checkDuplicate = $pdo->prepare("
    SELECT COUNT(*)
    FROM evaluation_periods
    WHERE academic_year = ?
      AND semester = ?
      AND target_dept = ?
      AND period_id != ?
");
$checkDuplicate->execute([$academic_year, $semester, $department, $period_id]);
$countDuplicate = $checkDuplicate->fetchColumn();

if ($countDuplicate > 0) {
    echo json_encode(['status' => 'error', 'message' => 'An evaluation period for this academic year, semester, and department already exists.']);
    exit;
}

// Overlap check — exclude current period
$checkOverlap = $pdo->prepare("
    SELECT COUNT(*) 
    FROM evaluation_periods
    WHERE target_dept = ?
      AND semester = ?
      AND academic_year = ?
      AND period_id != ?
      AND (
          start_date < ?
          AND end_date > ?
      )
");
$checkOverlap->execute([
    $department,
    $semester,
    $academic_year,
    $period_id,
    $end_date,
    $start_date
]);

if ($checkOverlap->fetchColumn() > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Evaluation period overlaps with an existing schedule for this department and semester.']);
    exit;
}

// Recalculate is_active based on new dates
$now = date('Y-m-d H:i:s');
$is_active = ($now >= $start_date && $now <= $end_date) ? 1 : 0;

// Update
$stmt = $pdo->prepare("
    UPDATE evaluation_periods
    SET academic_year = ?,
        semester = ?,
        target_dept = ?,
        set_id = ?,
        start_date = ?,
        end_date = ?,
        is_active = ?
    WHERE period_id = ?
");
$stmt->execute([
    $academic_year,
    $semester,
    $department,
    $question_set_id,
    $start_date,
    $end_date,
    $is_active,
    $period_id
]);

echo json_encode(['status' => 'success', 'message' => 'Evaluation period updated successfully.']);
?>