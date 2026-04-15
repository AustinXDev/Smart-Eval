<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../../config/database.php';

$period_id = $_POST['period_id'] ?? null;

if (!$period_id) {
    echo json_encode(['status' => 'error', 'message' => 'Period ID not found.']);
    exit;
}

try {
    $pdo->beginTransaction();

    // Get period info
    $stmt = $pdo->prepare("
        SELECT period_id, target_dept, is_active
        FROM evaluation_periods
        WHERE period_id = ?
    ");
    $stmt->execute([$period_id]);
    $period = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$period) {
        throw new Exception("Period not found.");
    }

    $dept = $period['target_dept'];

    // Count total expected evaluations (based on total students)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_expected
        FROM students s
        INNER JOIN programs p
            ON s.program_id = p.program_id
        WHERE s.is_active = 1
          AND p.department = ?
    ");
    $stmt->execute([$dept]);
    $totalExpected = (int) ($stmt->fetchColumn() ?? 0);

    if ($totalExpected === 0) {
        throw new Exception("No expected evaluations found for this period.");
    }

    // Count total finished evaluations (students with is_finished_all = 1)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) AS total_finished
        FROM students s
        INNER JOIN programs p
            ON s.program_id = p.program_id
        WHERE s.is_active = 1
          AND s.is_finished_all = 1
          AND p.department = ?
    ");
    $stmt->execute([$dept]);
    $totalFinished = (int) ($stmt->fetchColumn() ?? 0);

    // Calculate participation rate safely
    $participationRate = $totalExpected > 0
        ? ($totalFinished / $totalExpected) * 100
        : 0;

    // Check if participation is 100%
    if ($participationRate < 100) {
        throw new Exception("Cannot force close. Participation is only " . number_format($participationRate, 2) . "%.");
    }

    // Force close the period
    $stmt = $pdo->prepare("
        UPDATE evaluation_periods
        SET is_active = 0, is_closed = 1
        WHERE period_id = ?
    ");
    $stmt->execute([$period_id]);

    $pdo->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Evaluation period successfully closed. Participation: ' . number_format($participationRate, 2) . '%'
    ]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}