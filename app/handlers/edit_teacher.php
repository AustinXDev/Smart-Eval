<?php
header('Content-Type: application/json');
ini_set('display_errors', 0); // hide errors from frontend
ini_set('log_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../config/database.php';

$input = $_POST;

$teacher_id  = $input['teacher_id'] ?? '';
$employee_id = trim($input['employee_id'] ?? '');
$full_name   = trim($input['full_name'] ?? '');
$email       = trim($input['email'] ?? '');

// check if not empty
if (!$teacher_id) {
    echo json_encode(['status'=>'success','error' => 'Teacher id canno be blank']);
}
if ($employee_id === '') {
    echo json_encode(['status'=>'success','error' => 'Employee id cannot be blank']);
}
if ($full_name === '') {
    echo json_encode(['status'=>'success','error' => 'Full name cannot be blank.']);
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status'=>'success','error' => 'Please enter email address.']);
}

//Check duplicate employee_id
$stmt = $pdo->prepare("SELECT 1 FROM teachers WHERE employee_id = ? AND teacher_id != ?");
$stmt->execute([$employee_id, $teacher_id]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['status'=>'error','error' => 'Employee id already exist for another teacher.']);
}

//Check duplicate email
$stmt = $pdo->prepare("SELECT 1 FROM teachers WHERE email = ? AND teacher_id != ?");
$stmt->execute([$email, $teacher_id]);
if ($stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode(['status'=>'error','error' => 'Email alreader exist for another teacher']);
}


// Get current teacher record
$stmt = $pdo->prepare("SELECT image_path FROM teachers WHERE teacher_id = ?");
$stmt->execute([$teacher_id]);
$current_teacher = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$current_teacher) {
    echo json_encode(['status'=>'error','error' => 'Teacher not found.']); 
}

$photo_path = $current_teacher['image_path'] ?? 'default_teacher.png';
$uploadDir = __DIR__ . '/../../public/uploads/teachers/';

//Handle photo upload
if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES['photo']['tmp_name'];
    $fileName    = $_FILES['photo']['name'];
    $fileSize    = $_FILES['photo']['size'];
    $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowed_ext = ['jpg','jpeg','png'];

    if (!in_array($fileExt, $allowed_ext)) {
       echo json_encode(['error'=>'success','error' => ' Invalid file type. Only jpg, jpeg, png allowed.']);
    }

    if ($fileSize > 2 * 1024 * 1024) { // 2MB
        echo json_encode(['error'=>'error','message' => 'file too large. Max 2 MB.']);
    }

    $newFileName = uniqid('teacher_') . '.' . $fileExt;
    $destPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmpPath, $destPath)) {
       echo json_encode(['status'=>'error','message' => 'Failed upload photo.']);
    }

    // Delete old photo if not default
    if ($photo_path && $photo_path !== 'default_teacher.png' && file_exists($uploadDir . $photo_path)) {
        unlink($uploadDir . $photo_path);
    }

    $photo_path = $newFileName;
}

$stmt = $pdo->prepare("
    UPDATE teachers
    SET employee_id = ?, full_name = ?, email = ?, image_path = ?
    WHERE teacher_id = ?
");

$updated = $stmt->execute([$employee_id, $full_name, $email, $photo_path, $teacher_id]);

if ($updated) {
    echo json_encode(['status'=>'success','message' => ' Teacher updated successfully.']);
} else {
    echo json_encode(['status'=>'error','message' => 'Failed to update teacher']);
}

exit;