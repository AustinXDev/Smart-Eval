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

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
   
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <!-- Modal JS -->
  <script src="../../public/assets/js/modal/modal.js" type="module"></script>

</head>
<body>

  <!-- header -->
  <?php require __DIR__ . '/../partials/header.php'; ?>
  
  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="pt-22 lg:ml-90 p-6  min-h-screen">
    <?php require __DIR__ . '/../pages/students_content.php'?>
  </main>

  <!-- Modal Content -->
  <?php require_once __DIR__ . '/../../app/modals/students_modal/add_student_modal.php'; ?>
  <?php require_once __DIR__ . '/../../app/modals/students_modal/confirmation_modal.php'; ?>
  <?php require_once __DIR__ . '/../../app/modals/students_modal/import_csv_modal.php'; ?>
  <?php require_once __DIR__ . '/../../app/modals/students_modal/summary_report_modal.php'; ?>
  <?php require_once __DIR__ . '/../../app/modals/students_modal/loader_modal.php'; ?>


<script src="../../public/assets/js/admin/students/table.js" type="module"></script>
<script src="../../public/assets/js/admin/students/actions.js" type="module"></script> 
</body>
</html>