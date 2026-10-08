<?php 
/* Evaluation Finished Guard */

require_once __DIR__ . '/../config/database.php';

// Safe session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$student = $_SESSION['student'] ?? null;

if (!$student) {
    // Not logged in
    header('Location: /Smart-Eval/views/student/login.php');
    exit();
}

$student_id = $student['student_id'];

// Get finished status
$stmt = $pdo->prepare(
    'SELECT is_finished_all 
     FROM students 
     WHERE student_id = ?'
);

$stmt->execute([$student_id]);

$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result && $result['is_finished_all'] == 1) {

    header('Location: /Smart-Eval/views/student/evaluation_done.php');
    exit();
}
?>