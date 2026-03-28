<?php 
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

$response = [
    'total' => 0,
    'success' => 0,
    'failed' => 0,
    'errors' => []
];

$department = $_POST['department'] ?? '';

if (!$department) {
    $response['errors'][] = "Department not provided.";
    echo json_encode($response);
    exit;
}

// Check if a file was uploaded
if (!isset($_FILES['csv']) || $_FILES['csv']['error'] != 0) {
    $response['errors'][] = "No file uploaded or upload error.";
    echo json_encode($response);
    exit;
}

// File info
$file = $_FILES['csv']['tmp_name'];
$filename = $_FILES['csv']['name'];
$filesize = $_FILES['csv']['size'];

// Extension check
if (pathinfo($filename, PATHINFO_EXTENSION) !== 'csv') {
    $response['errors'][] = "Invalid file type. Only CSV allowed.";
    echo json_encode($response);
    exit;
}

// Open file
if (($handle = fopen($file, "r")) === FALSE) {
    $response['errors'][] = "Cannot open file.";
    echo json_encode($response);
    exit;
}

$expected_headers = ['student_id', 'full_name', 'email', 'program_id', 'year_level'];
$headers = fgetcsv($handle);

if ($headers !== $expected_headers) {
    $response['errors'][] = "Invalid template: CSV columns do not match required format.";
    fclose($handle);
    echo json_encode($response);
    exit;
}

$line = 2; // first row after headers
$errors = [];
$successCount = 0;

while (($row = fgetcsv($handle)) !== FALSE) {
    $response['total']++;
    list($student_id, $full_name, $email, $program_id, $year_level) = $row;

    // Check empty fields
    if (empty($student_id) || empty($full_name) || empty($email) || empty($program_id) || empty($year_level)) {
        $errors[] = "Row $line: Missing field(s)";
        $line++;
        continue;
    }

    // Check program existence
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM programs WHERE program_id = ? AND department = ?");
    $stmt->execute([$program_id, $department]);
    if ($stmt->fetchColumn() == 0) {
        $errors[] = "Row $line: Invalid Program ID for this department";
        $line++;
        continue;
    }

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE student_id = ? OR email = ?");
    $stmt->execute([$student_id, $email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Row $line: Duplicate Student ID or Email";
        $line++;
        continue;
    }

    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Row $line: Invalid Email format";
        $line++;
        continue;
    }

    // Insert student
    $stmt = $pdo->prepare("INSERT INTO students (student_id, full_name, email, program_id, year_level) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $full_name, $email, $program_id, $year_level]);

    $successCount++;
    $line++;
}

fclose($handle);

$response['success'] = $successCount;
$response['failed'] = count($errors);
$response['errors'] = $errors;

echo json_encode($response);
exit;
?>