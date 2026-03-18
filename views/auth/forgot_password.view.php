<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <?php include_once '../../public/assets/includes/head.php'; ?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">
</head>

<body class="bg-[url('../../public/assets/images/BG-login.png')] bg-cover bg-center h-screen w-screen">

  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <div class="h-screen flex justify-center items-center"> <!-- Wrapper -->
    <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg"> <!-- Forgot Password Wrapper -->
      <form id="forgot-password-form" method="POST">
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
          <h1 class="font-roboto text-2xl text-purple-900 font-bold md:text-3xl">Reset Your Password</h1>
          <p class="font-roboto text-gray-500 text-sm md:text-md">Enter your registered Student ID to recover your account.
          </p>
        </div>

        <div class="my-8 text-sm text-gray-600 bg-gray-700/5 border-l-[5px] border-orange-600 py-5 px-5 rounded-md md:text-md">
          <p>A password reset link will be sent to your email inbox.</p>
        </div>

        <div class="relative">
          <div>
            <span class="absolute left-5 top-3 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/user.png" alt="user-icon.png">
            </span>
            <div class="h-full w-0.5 bg-gray-300 absolute left-14"></div>
            <input class="bg-gray-100 w-full font-roboto py-4 pl-17 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-lg" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>
          </div>
        </div>

        <div class="mt-10">
          <button class="w-full bg-purple-900 text-white font-semibold text-sm py-3 rounded-xl cursor-pointer hover:opacity-75 md:text-lg" type="submit" id="reset-button">Reset Password</button>
        </div>

        <div class="mt-5">
          <p class="text-center text-sm font-light md:text-md" >Wait, I remember now! <a class="text-purple-900 font-semibold underline" href="login.view.php">Go back to login</a></p>
        </div>

      </form>
    </div>
  </div>

  <script src="../../public/assets/js/auth/forgot_password.js" type="module"></script> <!-- Link to forgot password.js -->
</body>
</html>