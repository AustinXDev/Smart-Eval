<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$studentId = $input['student_id'] ?? '';
$password = $input['password_hash'] ?? '';

if(!$studentId || !$password){
    echo json_encode(['success'=>false,'message'=>'Missing fields']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$studentId]);
$active = $stmt->fetch();

if($active){
    echo json_encode(['success'=>false,'message'=>'Student already exists']);
} else {
    // Insert logic here if needed
    echo json_encode(['success'=>true,'message'=>'User registered successfully']);
}