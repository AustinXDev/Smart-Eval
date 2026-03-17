<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartEval Register</title>

  <?php include_once '../../public/assets/includes/head.php'; ?> <!-- Include head.php for common head elements -->

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

</head>
<body class="bg-[url('../../public/assets/images/BG-login.png')] bg-cover bg-center h-screen">
  
  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <?php include '../../public/assets/includes/auth-floating-icons.php'; ?> <!-- Floating Icons -->

  <div class="h-screen flex justify-center items-center">  <!-- Wrapper -->
    <div class="animate-fade-slide w-lg bg-white rounded-lg shadow-lg/20 overflow-hidden px-6 pb-12 mx-5 pt-2 md:px-12 md:pb-15 md:w-xl"> <!-- Login Wrapper -->
      <form id="signUpForm" method="POST"> 
        <div class="flex justify-center flex-col items-center gap-3 border-b-2 border-gray-200 -mx-2 md:-mx-10 pb-2 md:flex-row"> <!-- Login Header Container -->
          <div> <!-- Logo wrapper -->
            <img class="h-20 w-20 md:h-20 md:w-20 drop-shadow-xl" src="../../public/assets/images/aite-logo.png" alt="aite-logo.png">
          </div>
          <div class="select-none"> <!-- System name wrapper -->
            <h1 class="font-roboto text-2xl font-bold text-center text-purple-900 uppercase  md:text-4xl md:text-left md:font-extrabold text-shadow-md text-shadow-gray-300">Smart<span class="text-green-600">Eval</span></h1>
            <span class="text-gray-400 text-center text-sm font-regular w-5 md:w-full">Your feedback helps improve teaching quality</span>
          </div>
        </div>

        <div class="my-6 md:my-8"> <!-- Greetings Wrapper -->
          <h1 class="font-roboto text-2xl text-purple-900 font-bold md:text-3xl">New here, AITEans!</h1>
          <p class="font-roboto text-gray-500 text-sm md:text-md">Create an account to start your evaluation.</p>
        </div>

        <div class="flex flex-col gap-10"> <!-- Inputs Wrapper -->
          
          <div class="relative ">
            <span class="absolute left-5 top-4 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/user.png" alt="user-icon.png">
            </span>
            <div class="h-full w-0.5 bg-gray-300 absolute left-14"></div>
            <input class="bg-gray-100 w-full py-4 pl-17 text-md shadow-md rounded-xl focus:border-purple-900 focus:outline-2 focus:outline-purple-900" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>
          </div>
          
          <div class="relative">
            <span class="absolute left-5 top-4 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/key.png" alt="password-icon.png">
            </span>
            <div class="h-full w-0.5 bg-gray-300 absolute left-14"></div>
            <div></div>
            <input class="bg-gray-100 w-full py-4 pl-17 text-md shadow-md rounded-xl focus:border-purple-900 focus:outline-2 focus:outline-purple-900" type="password" name="password" id="inputPassword" placeholder="Password" required>
            <div class="absolute right-12 top-4">
              <div class="relative">
                <span class="absolute w-8 h-8 cursor-pointer" id="hidden"><img src="../../public/assets/icons/show.png" alt="hidden.png"></span>
                <span class="absolute w-8 h-8 hidden cursor-pointer" id="show"><img src="../../public/assets/icons/view.png" alt="show.png"></span>
              </div>
            </div>
          </div>

          <div class="relative">
            <span class="absolute left-5 top-4 md:left-4 md:top-3.5">
              <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/approve.png" alt="password-icon.png">
            </span>
            <div class="h-full w-0.5 bg-gray-300 absolute left-14"></div>
            <div></div>
            <input class="bg-gray-100 w-full py-4 pl-17 text-md shadow-md rounded-xl focus:border-purple-900 focus:outline-2 focus:outline-purple-900" type="password" name="confirm-password" id="inputConfirmPassword" placeholder="Confirm Password" required>
            <div class="absolute right-12 top-4">
              <div class="relative">
                <span class="absolute w-8 h-8 cursor-pointer" id="hideConfirm"><img src="../../public/assets/icons/show.png" alt="hidden.png"></span>
                <span class="absolute w-8 h-8 hidden cursor-pointer" id="showConfirm"><img src="../../public/assets/icons/view.png" alt="show.png"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-8">
          <input class="w-full bg-purple-900 text-white font-semibold text-lg py-3 rounded-xl cursor-pointer hover:opacity-75" type="submit" name="signup-btn" value="Sign Up">
        </div>

        <div class="mt-5">
          <p class="font-roboto text-center text-md font-light" >Already have an account? <a class="text-purple-900 font-semibold underline" href="login.view.php">Sign in.</a></p>
        </div>
      </form>
    </div>
  </div>

  <script src="../../public/assets/js/auth/register.js" type="module"></script>
</body>
</html>