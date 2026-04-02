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
  <title>Manage Questionnaires</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- header -->
  <?php require __DIR__ . '/../partials/header.php'; ?>
  
  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <main class="pt-22 lg:ml-90 p-6 border-1 min-h-screen">
    <?php require __DIR__ . '/../pages/questionnaire_content.php'?>
  </main>

  <!-- Modals -->
  <?php require __DIR__ . '/../../app/modals/manage_questionnaires_modal/add_set_modal.php'; ?>
  <?php require __DIR__ . '/../../app/modals/manage_questionnaires_modal/confirmation_modal.php'; ?>
  <?php require __DIR__ . '/../../app/modals/manage_questionnaires_modal/manage_questions_modal.php'; ?>
  <?php require __DIR__ . '/../../app/modals/manage_questionnaires_modal/edit_questions_modal.php'; ?>

  <script src="../../public/assets/js/admin/manage_questionnaires/action.js" type="module"></script>
  <script src="../../public/assets/js/admin/manage_questionnaires/list.js" type="module"></script>
</body>
</html>