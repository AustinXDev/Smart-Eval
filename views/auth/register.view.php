<?php 
require_once __DIR__ . '/../../app/init.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SmartEval Register</title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?> <!-- Include head.php for common head elements -->

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/Smart-Eval/public/assets/css/custom.css">

  <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

</head>

<body class="bg-[#F8F6F0]">

  <!-- Toast Notification -->
  <?php include __DIR__ . '/../../public/assets/includes/toast.php'; ?> 

  <!-- Wrapper -->
  <div class="overflow-hidden mx-w-screen-2xl h-dvh mx-auto flex justify-center items-center relative"> 

    <!-- Login Wrapper -->
      <div class="
        animate-fade-slide 
        h-full 
        w-full 
        bg-white 
        shadow-xl 
        shadow-[#8c52ff]/15 
        overflow-hidden 
        p-6
        lg:mx-10 
        lg:p-0
        lg:max-w-screen-xl 
        lg:h-[650px] 
        lg:rounded-lg
        lg:grid
        lg:grid-cols-2
        lg:gap-6
      " id="register-wrapper"> 

        <!-- left panel -->
        <div class="hidden lg:flex overflow-hidden relative slide-right" id="left-panel">

          <!-- Overlay -->
          <div class="absolute inset-0">

            <img  class="absolute inset-0 w-full h-full object-cover opacity-50 select-none scale-125" src="/Smart-Eval/public/assets/images/school-img.jpeg" alt="school-img" loading="lazy" > 

            <div class="absolute inset-0 bg-gradient-to-t from-[#0B1120]/90 via-[#1E1B4B]/75 to-[#4338CA]/25 backdrop-blur-[1px]"></div>

            <img class="absolute -bottom-[50px] w-full z-10 select-none" src="/Smart-Eval/public/assets/images/overlay.png" alt="overlay" loading="lazy"></img>

            <img class="absolute right-3 bottom-3 z-20 w-12 h-12 select-none sm:w-14 sm:h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24"
              src="/Smart-Eval/public/assets/images/aite-logo.png"
              alt="AITE Logo"
              loading="lazy">

          </div>

          <div class="z-10 flex items-center gap-2.5 absolute top-8 left-10 bg-white/12 backdrop-blur-md border border-white/25 rounded-full pl-1.5 pr-4 py-1.5">

            <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center overflow-hidden shrink-0">
              <img src="/Smart-Eval/public/assets/images/aite-logo.png" alt="AITE" class="w-5 h-5 object-contain" />
            </div>

            <p class="text-xs font-semibold text-white leading-tight">
              Asian Institute of Technology<br>and Education
            </p>

          </div>

          <div class="z-10 absolute left-10 bottom-25 flex flex-col gap-6 animate-[fadeInUp_0.8s_ease-out]">

            <div class="flex items-center gap-4">
            
              <div class="flex relative items-center text-sm gap-2 bg-white/20 backdrop-blur-md px-4 py-1 rounded-2xl text-white shine overflow-hidden">

                <i class="fa-solid fa-graduation-cap"></i>
                <span>Students</span>

              </div>

              <div class="flex items-center text-sm gap-2 bg-white/20 backdrop-blur-md px-4 py-1 rounded-2xl text-white shine overflow-hidden">

                <i class="fa-solid fa-user"></i>
                <span>Administrators</span>

              </div>

            </div>

            <div>

              <div class="w-9 h-[3px] bg-purple-300 rounded-full mb-3"></div>
              <p class="text-xs tracking-widest text-purple-200 font-medium mb-1.5">TEACHER EVALUATION SYSTEM</p>
              <h1 class="text-5xl font-medium text-white tracking-tight">Smart-Eval</h1>

            </div>

            <p class="max-w-[360px] text-sm leading-relaxed">
              <span class="text-white font-medium">Join Smart-Eval, get started in minutes. </span>
              <span class="text-purple-200">Create an account to begin submitting or managing teacher evaluations.</span>
            </p>

          </div>

        </div>

        <!-- right panel -->
        <div class="flex justify-center items-center slide-left" id="right-panel">

          <form id="signUpForm" method="POST"> 

            <!-- Greetings Wrapper -->
              <div class="mb-7 border-l-[3px] border-[#5e17eb] pl-4">

               <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">STUDENT REGISTRATION</p>
              <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Create your account</h1>
              <p class="text-xs text-gray-400 leading-relaxed max-w-md">
                Enter your Student ID and email below. We'll send you an activation link to set your password and complete your account setup.
              </p>

              </div>

            <!-- Inputs Wrapper -->
            <div class="flex flex-col gap-6 md:gap-6"> 

              <!-- Student ID -->
                <div>

                  <label class="block text-[10px] font-semibold text-[#5e17eb] uppercase tracking-widest mb-2">
                  Student ID
                  </label>

                  <div class="relative ">

                    <span class="absolute left-4 top-2 md:left-4 md:top-2">
                      <i class="absolute top-1 left-2 fa-solid fa-user text-[#5e17eb]/90 text-lg md:text-xl"></i>
                    </span>

                    <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>

                  </div>
                </div>
              
              <!-- Email  -->
                <div>

                  <label class="block text-[10px] font-semibold text-[#5e17eb] uppercase tracking-widest mb-2">
                    Email Address
                  </label>

                  <div class="relative">

                    <span class="absolute left-4 top-2 md:left-4 md:top-2">
                      <i class="absolute top-1 left-2 fas fa-envelope text-[#5e17eb]/90 text-lg md:text-xl"></i>
                    </span>

                    <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="email" name="email" id="inputEmail" placeholder="example@gmail.com" required>

                  </div>

                </div>

            </div>

            <div class="my-8">
              <input class="w-full bg-[#5e17eb] text-white font-semibold text-sm py-3 rounded-2xl cursor-pointer hover:opacity-75" type="submit" name="signup-btn" value="Sign Up" id="signup-btn">
            </div>

            <!-- Divider -->
            <div class="flex items-center gap-3 mb-5">
              <div class="flex-1 h-px bg-purple-50"></div>
              <span class="text-xs text-[#5e17eb]/50">
                Already have an account?
              </span>
              <div class="flex-1 h-px bg-purple-50"></div>
            </div>

            <p class="text-center text-sm text-gray-500">
              Already registered?
              <button type="button" class="text-[#5e17eb] font-semibold hover:text-purple-900 transition-colors" id="login-link"
                href="<?=  BASE_URL ?>login">
                Sign in to continue evaluation.
              </button>
            </p>

            <!-- Security note -->
            <div class="flex items-center justify-center gap-2 mt-6 pt-5 border-t border-purple-50">

              <svg class="w-3 h-3 text-[#5e17eb]/50" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
              </svg>
              <span class="text-[10px] text-[#5e17eb]/50 tracking-wide">Secured & encrypted — AITE Smart-Eval System</span>

            </div>

          </form>

        </div>

      </div>

  </div>

  <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script>

  <script src="<?= BASE_URL ?>assets/js/auth/register/main.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>