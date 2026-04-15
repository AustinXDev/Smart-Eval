<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// Get set_id from query parameter
$set_id = $_GET['id'] ?? '';

// Validate set_id
if (!is_numeric($set_id)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid or missing question set ID.'
    ]);
    exit;
}

try {
    // Prepare and execute SELECT query
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE set_id = ? AND is_active = 1");
    $stmt->execute([$set_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $questions
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>