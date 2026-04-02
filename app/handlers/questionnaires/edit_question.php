<?php
header('Content-type: application/json');
require_once __DIR__ . '/../../config/database.php';

$question_id = $_POST['question_id'] ?? '';
$set_id = $_POST['set_id'] ?? '';
$new_text = trim($_POST['question_text'] ?? '');
$new_category = $_POST['category'] ?? '';

if (!$question_id || !$set_id || !$new_text || !$new_category) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields.']);
    exit;
}

try {
    $pdo->beginTransaction();

    //Integrity Lock: check if question has student answers
    $stmt = $pdo->prepare("SELECT 1 FROM evaluation_answers WHERE question_id = ? LIMIT 1");
    $stmt->execute([$question_id]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        echo json_encode([
            'status' => 'error',
            'message' => 'This question has student responses. Text cannot be changed.'
        ]);
        exit;
    }

    //Duplicate Check within the same set (excluding current question)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as cnt 
        FROM questions 
        WHERE set_id = ? 
        AND LOWER(REPLACE(REPLACE(question_text,' ',''), '?', '')) = LOWER(REPLACE(REPLACE(?, ' ',''), '?',''))
        AND question_id != ?
    ");
    $stmt->execute([$set_id, $new_text, $question_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row['cnt'] > 0) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Another question in this set already uses this exact text.']);
        exit;
    }

    //Change Detection: skip update if no actual changes
    $stmt = $pdo->prepare("SELECT question_text, category FROM questions WHERE question_id = ?");
    $stmt->execute([$question_id]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($current['question_text'] === $new_text && $current['category'] === $new_category) {
        $pdo->rollBack();
        echo json_encode(['status' => 'warning', 'message' => 'No changes detected.']);
        exit;
    }

    //Category Validation: ensure category exists
    $valid_categories = ['Punctuality', 'Communication', 'Subject Mastery', 'Professionalism', 'Classroom Management']; // example
    if (!in_array($new_category, $valid_categories)) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Invalid category.']);
        exit;
    }

    //Relationship Check: ensure set_id exists
    $stmt = $pdo->prepare("SELECT 1 FROM question_sets WHERE set_id = ? LIMIT 1");
    $stmt->execute([$set_id]);
    if (!$stmt->fetch()) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'The selected question set does not exist.']);
        exit;
    }

    //Update Question
    $stmt = $pdo->prepare("
        UPDATE questions 
        SET question_text = ?, category = ? 
        WHERE question_id = ?
    ");
    $stmt->execute([$new_text, $new_category, $question_id]);

    $pdo->commit();
    echo json_encode(['status' => 'success', 'message' => 'Question updated successfully.']);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}