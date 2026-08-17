<?php 
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

$employee_id = trim($_POST['employee_id'] ?? '');
$full_name   = trim($_POST['full_name'] ?? '');
$email       = trim($_POST['email'] ?? '');
$department  = trim($_POST['department'] ?? '');


//BASIC VALIDATION
if(!$employee_id || !$full_name || !$email || !$department) {
    echo json_encode(['status' => 'error', 'message' => 'Missing  fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => 'error', 'message' => "Invalid email format."]);
    exit;
}

if (strlen($full_name) > 100) {
    echo json_encode(['status' => 'error', 'message' => "Faculty Name must not exceed 100 characters."]);
    exit;
}

//ACTIVE EVALUATION LOCK
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM evaluation_periods 
    WHERE target_dept = ? 
    AND is_active = 1
");
$stmt->execute([$department]);

if ($stmt->fetchColumn() > 0) {
    exit(json_encode(['status' => 'error', 'message' => "Cannot add teacher. There is an active evaluation for the $department department."]));
}

//DUPLICATE CHECK
// Employee ID
$stmt = $pdo->prepare("SELECT 1 FROM teachers WHERE employee_id = ?");
$stmt->execute([$employee_id]);
if ($stmt->fetch()) {
    exit(json_encode(['status' => 'error', 'message' => "Employee ID already exists."]));
}

// Email
$stmt = $pdo->prepare("SELECT 1 FROM teachers WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    exit(json_encode(['status' => 'error', 'message' => "Email already exists."]));
}

// Name + Department (warning-level)
$stmt = $pdo->prepare("SELECT 1 FROM teachers WHERE full_name = ? AND department = ?");
$stmt->execute([$full_name, $department]);
if ($stmt->fetch()) {
    exit(json_encode(['status' => 'error', 'message' => "A teacher with the same name already exists in this department."]));
}

//FILE VALIDATION
$image_path = 'default_teacher.png';

if (!empty($_FILES['photo']['name'])) {

    $file = $_FILES['photo'];
    $allowed = ['image/jpeg', 'image/png'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if ($file['size'] > $maxSize) {
        exit(json_encode(['status' => 'error', 'message' => "Image must be less than 2MB."]));
    }

    if (!in_array($file['type'], $allowed)) {
        exit(json_encode(['status' => 'error', 'message' => "Only JPG and PNG files are allowed."]));
    }
}

//PROCESS INSERT
try {
    $pdo->beginTransaction();

    // Upload image
    if (!empty($_FILES['photo']['name'])) {
        $uploadDir = __DIR__ . '/../../../public/uploads/teachers/';

        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $image_path = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['photo']['name']);
        $target = $uploadDir . $image_path;

        if (!move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            throw new Exception("Failed to upload image.");
        }
    }

    // Insert teacher
    $stmt = $pdo->prepare("
        INSERT INTO teachers (employee_id, full_name, email, department, image_path, is_active) 
        VALUES (?, ?, ?, ?, ?, 1)
    ");
    $stmt->execute([$employee_id, $full_name, $email, $department, $image_path]);

    $pdo->commit();

    exit(json_encode([
        'status'  => 'success',
        'message' => "Teacher '$full_name' added successfully."
    ]));

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    exit(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
}