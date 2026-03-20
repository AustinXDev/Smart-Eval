<?php 
require_once __DIR__ . '/../../app/middleware/require_admin_auth.php';

$admin = getAdmin();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
</head>
<body>
  <p><?php echo $admin['admin_id'];?></p>
  <p><?php echo $admin['username'];?></p>
  <p><?php echo $admin['role'];?></p>
</body>
</html>