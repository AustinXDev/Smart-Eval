<?php 
require_once __DIR__ . '/../../app/middleware/require_admin_auth.php';

echo "Admin ID: " . var_dump($_SESSION['admin_id'] ?? 'NOT SET') . "<br>";
echo "Is Admin: " . var_dump($_SESSION['is_admin'] ?? 'NOT SET') . "<br>";
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
</head>
<body>
  
</body>
</html>