<?php
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="program_reference_guide.csv"');

require_once __DIR__ . '/../../config/database.php';

$department = $_GET['department'] ?? '';

$output = fopen("php://output", "w");
fputcsv($output, ['ID', 'Program Name']);

$stmt= $pdo->prepare("SELECT program_id, program_name FROM programs WHERE department = ?");
$stmt->execute([$department]);
$programs = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($programs as $p) {
    fputcsv($output, [$p['program_id'], $p['program_name']]);
}
fclose($output);
exit;
?>