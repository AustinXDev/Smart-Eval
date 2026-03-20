<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
?>  

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartEval Login</title>

  <?php include_once '../../public/assets/includes/head.php'; ?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

</head>

<body class="bg-[url('../../public/assets/images/BG-login.png')] bg-cover bg-center h-screen w-screen">

  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <?php include '../../public/assets/includes/auth-floating-icons.php'; ?> <!-- Floating Icons -->

  <div class="overflow-hidden h-screen flex justify-center items-center relative">  <!-- Wrapper -->
    <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg"> <!-- Login Wrapper -->
      <form id="login-form" method="POST"> 
        <div class="shadow-[0_1px_5px_rgba(0,0,0,0.1)] flex justify-center items-center gap-3 border-b-2 pt-2 -mx-6 -my-2 border-gray-200 -mx-2 md:shadow-[0] md:-mx-10 pb-2 md:flex-row"> <!-- Login Header Container -->
          <div> <!-- Logo wrapper -->
            <img class="h-12 w-12 md:h-20 md:w-20 drop-shadow-xl" src="../../public/assets/images/aite-logo.png" alt="aite-logo.png">
          </div>
          <div class="select-none"> <!-- System name wrapper -->
            <h1 class="font-roboto text-2xl font-bold text-purple-900 uppercase  md:text-4xl md:text-left md:font-extrabold text-shadow-md text-shadow-gray-300">Smart<span class="text-green-600">Eval</span></h1>
            <span class="font-roboto text-gray-400 text-center text-xs font-regular w-5  md:text-sm md:w-full">Your feedback helps improve teaching quality</span>
          </div>
        </div>

        <div class="mt-10 my-8 my-5 md:my-6 md:my-8"> <!-- Greetings Wrapper -->
          <h1 class="font-roboto text-2xl text-purple-900 font-bold md:text-3xl">Hi, AITEans!</h1>
          <p class="font-roboto text-gray-500 text-sm md:text-md">Sign in to get started with your evaluation.</p>
        </div>

        <div class="flex flex-col gap-7 md:gap-10"> <!-- Inputs Wrapper -->
          <div class="relative ">
            <span class="absolute left-5 top-3 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/user.png" alt="user-icon.png">
            </span>
            <input class="bg-gray-100 w-full font-roboto py-4 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-lg" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>
          </div>
          <div class="relative">
            <span class="absolute left-5 top-3.5 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/key.png" alt="password-icon.png">
            </span>
            <div></div>
            <input class="bg-gray-100 w-full font-roboto py-4 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-lg" type="password" name="password" id="inputPassword" placeholder="Password" required>
            <div class="absolute right-12 top-4">
              <div class="relative">
                <span class="absolute -top-1 w-8 h-8 cursor-pointer md:top-0" id="hidden"><img src="../../public/assets/icons/show.png" alt="hidden.png"></span>
                <span class="absolute w-8 h-8 -top-1 hidden cursor-pointer md:top-0" id="show"><img src="../../public/assets/icons/view.png" alt="show.png"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="flex justify-between mt-5">
          <div class="flex items-center gap-2 md:gap-3">
            <input class="accent-purple-900 h-3 w-3 md:h-4 md:w-4" type="checkbox" name="remember_me" id="inputCheckbox">
            <span class="font-roboto text-gray-500 text-sm md:text-md">Remember me</span>
          </div>
          <div>
            <a class="font-roboto text-purple-900 underline text-sm md:text-md" href="forgot_password.view.php">Forgot Password?</a>
          </div>
        </div>

        <div class="mt-10">
          <input class="w-full bg-purple-900 text-white font-semibold text-sm py-3 rounded-xl cursor-pointer hover:opacity-75 md:text-lg" type="submit" name="signin-btn" value="Sign In" id="signin-btn">
        </div>

        <div class="mt-5">
          <p class="text-center text-sm font-light md:text-md" >Don't have an account? <a class="text-purple-900 font-semibold underline" href="register.view.php">Create one for teacher evaluation.</a></p>
        </div>
      </form>
    </div>

    <?php include '../../public/assets/includes/footer.php'; ?>

    <script src="../../public/assets/js/auth/login.js" type="module"></script>
</body>
</html>