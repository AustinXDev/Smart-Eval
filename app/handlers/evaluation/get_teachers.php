<?php
  session_start();
  require_once __DIR__ . '/../../config/database.php';

  $student = $_SESSION['student'] ?? null;

  if (!$student) {
      echo json_encode([
          'status' => 'error',
          'message' => 'Unauthorized'
      ]);
      exit;
  }

  //Get student's department (College / SHS)
  $stmt = $pdo->prepare("
      SELECT department 
      FROM programs 
      WHERE program_id = ?
  ");
  $stmt->execute([$student['program_id']]);
  $program = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$program) {
      echo json_encode([
          'status' => 'error',
          'message' => 'Program not found'
      ]);
      exit;
  }

  $department = $program['department']; // College or SHS

  //Filter teachers based on student department
  $stmt = $pdo->prepare("
      SELECT teacher_id, full_name, department
      FROM teachers
      WHERE is_active = 1
      AND department = ?
      ORDER BY full_name ASC
  ");
  $stmt->execute([$department]);

  $teachers = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
      'status' => 'success',
      'data' => $teachers
  ]);
?>