<?php 
require_once __DIR__ . '/../../app/middleware/require_admin_auth.php';
require_once __DIR__ . '/../../app/config/nav.php'; 
require_once __DIR__ . '/../../app/helpers/session.php';

if (isAdminLoggedIn()) {
    $admin     = getAdmin();
    $role      = strtolower(str_replace(' ', '_', $admin['role']));
    $name      = $admin['username'];
    $logout    = '/Smart-Eval/app/auth/logout.admin.php';
} else {
    $role      = 'student';
    $studentID = getStudent();
    $logout    = '/Smart-Eval/app/auth/logout.student.php';
}

$nav        = $navigation[$role] ?? [];
$currentUrl = $_SERVER['REQUEST_URI'];

$department = $_GET['dept'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">
</head>
<body>

  <!-- header -->
  <?php require __DIR__ . '/../partials/header.php'; ?>
  
  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="pt-22 lg:ml-90 p-6 border-1 min-h-screen">
    <?php require __DIR__ . '/../pages/dashboard_content.php'?>
  </main>
</body>
</html>