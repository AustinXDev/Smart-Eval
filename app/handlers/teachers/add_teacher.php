<?php 
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type: application/json');
session_start();

require_once __DIR__ . '/../../config/database.php';

$input = $_POST;

$employee_id = trim($input['employee_id'] ?? '');
$full_name  = trim($input['full_name'] ?? '');
$email      = trim($input['email'] ?? '');
$department = trim($input['department'] ?? '');

$errors = [];

if(empty($employee_id)){
  $errors[] = "Please enter the Employee ID.";
}

if (empty($full_name)) {
    $errors[] = "Please enter the Faculty Name.";
}

if (empty($email)) {
    $errors[] = "Email is required.";
}

if (empty($department)) {
    $errors[] = "Please select a Department.";
}


//format validation
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address (e.g., name@school.edu.ph).";
}

if (strlen($full_name) > 100) {
    $errors[] = "Faculty Name is too long (Maximum 100 characters).";
}

//duplicate check
if (empty($errors)) {

    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE employee_id = ?");
    $stmt->execute([$employee_id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Error: A teacher with this Employee ID ($employee_id) is already registered.";
    }

    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE full_name = ? AND department = ?");
    $stmt->execute([$full_name, $department]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Warning: A teacher named '$full_name' already exists in the $department Department.";
    }


    $stmt = $pdo->prepare("SELECT teacher_id FROM teachers WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->rowCount() > 0) {
        $errors[] = "Error: A teacher with this email already exists.";
    }
}

//file validation
$image_path = 'default_teacher.png';

if (!empty($_FILES['photo']['name'])) {

    $file = $_FILES['photo'];
    $allowed_types = ['image/jpeg', 'image/png'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if ($file['size'] > $max_size) {
        $errors[] = "File is too large. Maximum size is 2MB.";
    }

    if (!in_array($file['type'], $allowed_types)) {
        $errors[] = "Invalid file type. Please upload a JPG or PNG image.";
    }
}

//error handler
if (!empty($errors)) {
    echo json_encode([
        'status' => 'error',
        'errors' => $errors
    ]);
    exit;
}

//upload image
if (!empty($_FILES['photo']['name'])) {
    $uploadDir = __DIR__ . '/../../public/uploads/teachers/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    $image_path = time() . "_" . basename($_FILES['photo']['name']);
    $targetFile = $uploadDir . $image_path;

    if (!move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)) {
        echo json_encode(['status' => 'error', 'errors' => ["Failed to upload the photo."]]);
        exit;
    }
}

$stmt = $pdo->prepare("
    INSERT INTO teachers (employee_id, full_name, email, department, image_path) 
    VALUES (?, ?, ?, ?, ?)
");

$success = $stmt->execute([
    $employee_id,
    $full_name,
    $email,
    $department,
    $image_path
]);

if ($success) {
    echo json_encode([
        'status'  => 'success',
        'message' => "✅ Teacher '$full_name' has been successfully added to the $department Department."
    ]);
} else {
  echo json_encode([
        'status'  => 'error',
        'message' => 'Something went wrong. Please try again.'
  ]);
}

?>