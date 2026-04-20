<?php require '../../app/middleware/require_auth.php'; ?>

<?php 
$student = $_SESSION['student'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Teachers</title>

<?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

<!-- Custom CSS -->
<link rel="stylesheet" href="../../public/assets/css/custom.css">
<link rel="stylesheet"  href="../../public/assets/css/receipt.css">

<!-- Icons cdn --->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-gray-200 min-h-screen flex items-center justify-center p-4">

  <?php require __DIR__ . '/../pages/evaluation_done.php'; ?>


  <script src="../../public/assets/js/evaluation/evaluation_done.js" type="module"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>

</html>