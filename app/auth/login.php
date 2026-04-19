<?php
ob_start();
header('Content-Type: application/json');

require_once '../config/config.php';
require_once '../config/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$input = json_decode(file_get_contents('php://input'), true);
$student_id = trim($input['student_id'] ?? '');
$password = trim($input['password'] ?? '');
$IP = $_SERVER['REMOTE_ADDR'] ?? '';

$maxAttempts = 3;
$lockTime = 1;

try {
    // Validate input
    if (empty($student_id) || empty($password)) {
        echo json_encode(['status' => 'error', 'message' => '❌ Student ID and password are required.']);
        exit;
    }

    // Count failed login attempts
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as attempt_count
        FROM login_attempts
        WHERE ip_address = ?
        AND failed_at > (NOW() - INTERVAL ? MINUTE)
        AND success = 0
    ");
    $stmt->execute([$IP, $lockTime]);
    $attemptData = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attemptData['attempt_count'] >= $maxAttempts) {
        echo json_encode(['status' => 'error', 'message' => "Too many failed login attempts. Try again after {$lockTime} minute(s)."]);
        exit;
    }

    $remainingAttempts = max($maxAttempts - ($attemptData['attempt_count'] + 1), 0);

    // Fetch student
    $stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$user['is_active']){
        echo json_encode([
            'status' => 'error',
            'message' => "This account not available."
        ]);
        exit;
    }

    if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
        // Store failed attempt
        $stmt = $pdo->prepare("INSERT INTO login_attempts (student_id, ip_address, success) VALUES (?, ?, 0)");
        $stmt->execute([$student_id, $IP]);

        echo json_encode([
            'status' => 'error',
            'message' => "Incorrect student ID or password. {$remainingAttempts} attempt(s) remaining."
        ]);
        exit;
    }

    // Successful login
    session_regenerate_id(true);
    $_SESSION['student'] = [
        'student_id'      => $user['student_id'],
        'full_name'       => $user['full_name'],
        'program_id'      => $user['program_id'],
        'year_level'      => $user['year_level'],
        'enrollment_type' => $user['enrollment_type'],
        'is_finished_all' => $user['is_finished_all'] ?? 0
    ];

    $program_id = $user['program_id'];
    $isFinishedAll = isset($user['is_finished_all']) ? (int)$user['is_finished_all'] : 0;

    // Get student's department
    $stmt = $pdo->prepare("SELECT department FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);
    $department = $program['department'] ?? null;

    // Check active evaluation period for student's department
    $stmt = $pdo->prepare("
        SELECT period_id
        FROM evaluation_periods
        WHERE is_active = 1
        AND target_dept = ?
        LIMIT 1
    ");
    $stmt->execute([$department]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);
    $period_id = $period['period_id'] ?? null;

    $isSHS = ($department === 'shs');

    // Determine redirect
    if (!$period_id) {
        $redirect = '/Smart-Eval/views/student/no_evaluation.php';
    } 
    
    elseif ($isFinishedAll === 1) {
        $redirect = '/Smart-Eval/views/student/evaluation_done.php';
    } 
    
    else if ($isSHS){
        if($period_id) {
            $stmt = $pdo->prepare("
                INSERT IGNORE INTO evaluation_status (student_id, load_id, period_id)
                SELECT ?, load_id, ?
                FROM teacher_load
                WHERE program_id = ? AND year_level = ?
            ");
            $stmt->execute([$student_id, $period_id, $program_id, $user['year_level']]);

            //mark the shs student as regular to not shown select_enrollment 
            $pdo->prepare("UPDATE students SET enrollment_type = 'Regular' WHERE student_id = ?")
                ->execute([$student_id]);
            $_SESSION['student']['enrollment_type'] = 'Regular';
        }

        $redirect = '/Smart-Eval/views/student/evaluation.view.php';
    }
    
    elseif (!empty($user['enrollment_type'])) {
        // Check if student already selected teachers for irregular
        if ($user['enrollment_type'] === 'Irregular') {
            $stmt = $pdo->prepare("
                SELECT COUNT(*) AS selected_count
                FROM evaluation_status
                WHERE student_id = ? AND period_id = ?
            ");
            $stmt->execute([$student_id, $period_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $redirect = ($result['selected_count'] > 0)
                ? '/Smart-Eval/views/student/evaluation.view.php'
                : '/Smart-Eval/views/student/select_teachers.php';
        } else {
            // Regular student goes straight to evaluation
            $redirect = '/Smart-Eval/views/student/evaluation.view.php';
        }
    } 
    
    else {
        $redirect = '/Smart-Eval/views/student/enrollment_selection.php';
    }

    // Clear previous attempts
    $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$IP]);

    // Log successful login
    $stmt = $pdo->prepare("INSERT INTO login_attempts (student_id, ip_address, success) VALUES (?, ?, 1)");
    $stmt->execute([$student_id, $IP]);

    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful!',
        'redirect' => $redirect
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
    exit;
}

ob_end_flush();