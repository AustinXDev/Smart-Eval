<?php require '../../app/middleware/require_auth.php'; ?>
<?php require_once __DIR__ . '/../../app/config/nav.php';  ?>

<?php 
if (isAdminLoggedIn()) {
    $admin     = getAdmin();
    $role      = strtolower(str_replace(' ', '_', $admin['role']));
    $name      = $admin['username'];
    $logout    = '/Smart-Eval/app/auth/logout.admin.php';
} else {
    $role      = 'student';
    $student = getStudent();
    $logout    = '/Smart-Eval/app/auth/logout.student.php';
}

$nav        = $navigation[$role] ?? [];
$currentUrl = $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluation</title>

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

  <main class="pt-22 lg:ml-90 p-4 min-h-screen">
    <?php require __DIR__ . '/../pages/evaluation_content.php'; ?>
  </main>

  <!-- Modals -->
  <?php require __DIR__ . '/../../app/modals/evaluation_modal/review_modal.php'; ?>

  <script>
    window.addEventListener('pageshow', (e) => {
      if (e.persisted || performance.getEntriesByType('navigation')[0]?.type === 'back_forward') {
          window.location.replace('../../views/student/login.view.php');
      }
    });
  </script>
  <script src="../../public/assets/js/evaluation/evaluation.js" type="module"></script>
</body>
</html>