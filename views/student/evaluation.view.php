<?php require '../../app/middleware/require_auth.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <h1>Hello <?php echo getStudent(); ?> </h1>

  

  <script>
    window.addEventListener('pageshow', (e) => {
      if (e.persisted || performance.getEntriesByType('navigation')[0]?.type === 'back_forward') {
          window.location.replace('../../views/student/login.view.php');
      }
    });
  </script>
</body>
</html>