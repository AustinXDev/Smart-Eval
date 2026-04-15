<?php 
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$program_id = $_POST['program_id'];

if (!$program_id) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No program ID provided'
    ]);
    exit;
}

// Fetch specific program
$stmt = $pdo->prepare("SELECT program_id, program_name, department FROM programs WHERE program_id = ?");
$stmt->execute([$program_id]);

$program = $stmt->fetch(PDO::FETCH_ASSOC);

if ($program) {
    echo json_encode([
        'status' => 'success',
        'data' => $program
    ]);
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Program not found'
    ]);
}

?>