<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <?php include_once '../../public/assets/includes/head.php'; ?>


</head>
<body>
  <div> <!-- Wrapper -->
    <div> <!-- Forgot Password Wrapper -->
      <h1>Forgot Password</h1>
      <p>Enter your Student ID to reset your password.</p>
      <form id="forgot-password-form" method="POST">
        <input type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>
        <button type="submit">Reset Password</button>
      </form>
    </div>
  </div>

  <script src="../../public/assets/js/auth/forgot_password.js" type="module"></script> <!-- Link to forgot password.js -->
</body>
</html>