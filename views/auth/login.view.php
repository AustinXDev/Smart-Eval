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

  <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

</head>

<body class="min-h-screen w-screen overflow-hidden relative flex items-center justify-center" style="background: linear-gradient(135deg, #1E0F4E 0%, #2D1B69 40%, #4C1D95 70%, #6D28D9 100%);">

  <!-- Background orbs for depth -->
  <div class="absolute top-[-80px] left-[-80px] w-72 h-72 rounded-full opacity-20" style="background:#A78BFA;"></div>
  <div class="absolute bottom-[-60px] right-[-60px] w-56 h-56 rounded-full opacity-15" style="background:#C4B5FD;"></div>
  <div class="absolute top-1/2 right-[8%] w-28 h-28 rounded-full opacity-10" style="background:#EDE9FE;"></div>

  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <!-- Wrapper -->
  <div class="overflow-hidden h-screen flex justify-center items-center relative">  
    
    <!-- Login Wrapper -->
    <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-6 md:w-lg"> 

      <form id="login-form" method="POST"> 

        <!-- Login Header Container -->
          <div class="flex-col shadow-[0_1px_5px_rgba(0,0,0,0.1)] flex justify-center items-center md:gap-3 border-b-2 pt-2 -mx-6 -my-2 border-gray-200 -mx-2 md:shadow-[0] md:-mx-10 pb-2"> 
            
            <!-- Logo wrapper -->
            <div> 
              <img class="h-12 w-12 md:h-18 md:w-18 drop-shadow-xl" src="../../public/assets/images/aite-logo.png" alt="aite-logo.png">
            </div>

            <!-- System name wrapper -->
            <div class="select-none flex flex-col justify-center items-center">

              <span class="text-lg font-bold text-purple-900 tracking-wider uppercase">
                Smart-<span class="text-green-600">Eval</span>
              </span>
              <span class="font-roboto text-gray-400 text-center text-xs font-regular w-5  md:text-sm w-full">Your feedback helps improve teaching quality</span>

            </div>

          </div>

        <!-- Greetings Wrapper -->
          <div class="my-7"> 
            <h1 class="font-roboto text-2xl font-bold text-[#1E0F4E] mb-1">Hi, AITEans!</h1>
            <p class="font-roboto text-gray-400 text-sm">Sign in to get started with your evaluation.</p>
          </div>

        <!-- Inputs Wrapper -->
          <div class="flex flex-col gap-7 md:gap-6"> 

          <!-- Student ID -->
            <div>

              <label class="block text-[10px] font-semibold text-purple-700 uppercase    tracking-widest mb-2">
                Student ID
              </label>

              <div class="relative">

                <span class="absolute left-4 top-2 md:left-4 md:top-2">
                  <i class="absolute top-1 left-2 fa-solid fa-user text-purple-900 text-lg md:text-xl"></i>
                </span>

                <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>

              </div>

            </div>

          <!-- Password -->
            <div>

              <label class="block text-[10px] font-semibold text-purple-700 uppercase tracking-widest mb-2">
                Password
              </label>

              <div class="relative">

                <span class="absolute left-4 top-2 md:left-4 md:top-2">
                  <i class="absolute top-1 left-2 fas fa-key text-purple-900 text-lg md:text-xl"></i>
                </span>

                <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="password" id="inputPassword" placeholder="Enter your password" required>

                <div class="absolute right-12 top-4">

                  <div class="relative">

                    <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hidden">
                      <img src="../../public/assets/icons/show.png" alt="hidden.png">
                    </span>

                    <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="show">
                      <img src="../../public/assets/icons/view.png" alt="show.png">
                    </span>

                  </div>

                </div>

              </div>

            </div>

          </div>

        <div class="flex justify-between mt-5">
          
          <div class="flex items-center gap-2 md:gap-3">

            <input class="accent-purple-700 h-3.5 w-3.5" type="checkbox" name="remember_me" id="inputCheckbox">
            <span class="font-roboto text-gray-400 text-sm">Remember me</span>

          </div>

          <div>
            <a class="font-roboto text-purple-600 text-sm font-medium hover:text-purple-800 transition-colors"
            href="forgot_password.view.php">Forgot Password?</a>
          </div>

        </div>

        <div class="my-6">

          <input class="w-full bg-purple-900 text-white font-semibold text-sm py-3.5 rounded-xl cursor-pointer hover:opacity-75" type="submit" name="signin-btn" value="Sign In" id="signin-btn">

        </div>

        <!-- Divider -->
        <div class="flex items-center gap-3 mb-5">
          <div class="flex-1 h-px bg-purple-50"></div>
          <span class="text-xs text-purple-400">New to Smart-Eval?</span>
          <div class="flex-1 h-px bg-purple-50"></div>
        </div>

        <!-- Sign up -->
        <p class="text-center text-sm text-gray-500">
          Don't have an account?
          <a class="text-purple-700 font-semibold hover:text-purple-900 transition-colors"
            href="register.view.php">Create one for teacher evaluation.</a>
        </p>

        <!-- Security note -->
        <div class="flex items-center justify-center gap-2 mt-6 pt-5 border-t border-purple-50">

          <svg class="w-3 h-3 text-purple-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
          </svg>
          <span class="text-[10px] text-purple-400 tracking-wide">Secured & encrypted — AITE Smart-Eval System</span>

        </div>

      </form>

    </div>

    <script src="../../public/assets/js/auth/login.js" type="module"></script>
</body>
</html>