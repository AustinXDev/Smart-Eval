<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

//Collect & sanitize inputs
$program_id = trim($_POST['edit_program_id'] ?? '');
$new_code   = strtoupper(trim($_POST['edit_program_code'] ?? '')); // normalize code
$new_name   = trim($_POST['edit_program_name'] ?? '');
$department = trim($_POST['edit_department'] ?? '');

//Basic validation
if (empty($program_id) || empty($new_code) || empty($new_name) || empty($department)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing required fields.'
    ]);
    exit;
}

//Character length checks
if (strlen($new_code) < 2 || strlen($new_code) > 15) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Program code must be 2-15 characters long.'
    ]);
    exit;
}

if (strlen($new_name) < 10 || strlen($new_name) > 100) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Program name must be 10-100 characters long.'
    ]);
    exit;
}


try {
    $pdo->beginTransaction();

    //Check program ID exists
    $stmt = $pdo->prepare("SELECT * FROM programs WHERE program_id = ?");
    $stmt->execute([$program_id]);
    $program = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$program) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'Program not found.'
        ]);
        exit;
    }

    //Unique program_code check
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM programs 
        WHERE program_code = ? AND program_id != ?
    ");
    $stmt->execute([$new_code, $program_id]);
    if ($stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => "The code '$new_code' is already assigned to another program."
        ]);
        exit;
    }

    //Unique program_name check
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM programs 
        WHERE LOWER(program_name) = LOWER(?) AND program_id != ?
    ");
    $stmt->execute([$new_name, $program_id]);
    if ($stmt->fetchColumn() > 0) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => "The program name '$new_name' is already in use."
        ]);
        exit;
    }

    //Department change impact
    if ($program['department'] !== $department) {
        $stmt = $pdo->prepare("
            SELECT 1 FROM evaluation_periods 
            WHERE is_active = 1 AND target_dept = ?
            LIMIT 1
        ");
        $stmt->execute([$program['department']]);
        if ($stmt->fetch()) {
            $pdo->rollBack();
            echo json_encode([
                'status' => 'error',
                'message' => 'Cannot change department while there is an active evaluation targeting the current department.'
            ]);
            exit;
        }
    }

    //Update program
    $stmt = $pdo->prepare("
        UPDATE programs
        SET program_code = ?, program_name = ?, department = ?
        WHERE program_id = ?
    ");
    $stmt->execute([$new_code, $new_name, $department, $program_id]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => "Program '$new_name' ($new_code) updated successfully."
    ]);
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'status' => 'error',
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    exit;
}
?>