<?php
// Include database connection
require_once __DIR__ . '/../config/database.php';

// Get student from session
$student = $_SESSION['student'] ?? null;

if (!$student) {
    // Not logged in, redirect to login
    header('Location: /Smart-Eval/views/student/login.php');
    exit();
}

$student_id = $student['student_id'];
$enrollment_type = $student['enrollment_type'] ?? null;

// Get current page
$currentPage = basename($_SERVER['PHP_SELF']);

// If student has enrollment type set
if (!empty($enrollment_type)) {

    // If already Regular, redirect to evaluation page
    if ($enrollment_type === 'Regular' && $currentPage === 'enrollment_selection.php') {
        header('Location: /Smart-Eval/views/student/evaluation.view.php');
        exit();
    }

    // If already Irregular, redirect to teacher selection page
    if ($enrollment_type === 'Irregular' && $currentPage === 'enrollment_selection.php') {
        header('Location: /Smart-Eval/views/student/select_teachers.view.php');
        exit();
    }
}

// If student already selected teachers (Irregular)
// Prevent accessing select_teachers.view.php again

if ($enrollment_type === 'Irregular' && $currentPage === 'select_teachers.view.php') {

    // Check active period
    $stmt = $pdo->prepare("SELECT period_id FROM evaluation_periods WHERE is_active = 1 LIMIT 1");
    $stmt->execute();
    $period = $stmt->fetch(PDO::FETCH_ASSOC);
    $period_id = $period['period_id'] ?? null;

    if ($period_id) {
        // Check if student already has evaluation_status
        $stmt = $pdo->prepare("
            SELECT COUNT(*) AS selected_count
            FROM evaluation_status
            WHERE student_id = ? AND period_id = ?
        ");
        $stmt->execute([$student_id, $period_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result['selected_count'] > 0) {
            // Already selected, redirect to evaluation page
            header('Location: /Smart-Eval/views/student/evaluation.view.php');
            exit();
        }
    }
}