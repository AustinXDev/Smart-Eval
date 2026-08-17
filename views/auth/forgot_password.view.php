<?php 

require_once __DIR__ . '/../../app/init.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

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
      
      <!-- Forgot Password Wrapper -->
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
      ">
        
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
        
        <!--  right panel -->
        <div class="flex justify-center items-center slide-left" id="right-panel">

          <form id="forgot-password-form" method="POST">

            <!-- Greetings Wrapper -->
            <div class="mb-7 border-l-[3px] border-[#5e17eb] pl-4">

              <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">ACCOUNT RECOVERY</p>
              <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Forgot your password?</h1>
              <p class="text-xs text-gray-400 max-w-sm leading-relaxed">
                Enter the email address associated with your SMART-EVAL account and we'll send you a link to reset your password.
              </p>

            </div>

            <div class="my-8 text-sm text-gray-600 bg-[#5e17eb]/5 border-l-[5px] border-[#5e17eb] py-5 px-5 rounded-md max-w-md md:text-md">
              <p>If your given email is correct, a password reset link will be sent to your email inbox.</p>
            </div>

            <!-- Student ID -->
              <div>

                <label class="block text-[10px] font-semibold text-[#5e17eb] uppercase    tracking-widest mb-2">
                Email Address
                </label>

                <div class="relative">

                  <div>

                    <span class="absolute left-4 top-2 md:left-4 md:top-2">
                      <i class="absolute top-1 left-2 fa-solid fa-user text-[#5e17eb]/90 text-lg md:text-xl"></i>
                    </span>

                    <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="text" name="student_email" id="inputEmail" placeholder="example@gmail.com" required>

                  </div>

                </div>

              </div>

            <div class="my-8">
              <button class="w-full bg-[#5e17eb] text-white font-semibold text-sm py-3 rounded-2xl cursor-pointer hover:opacity-75 md:text-md" type="submit" id="reset-button">Reset Password</button>
            </div>

            <div class="mt-5">
              <p class="text-center text-sm text-gray-500" >Wait, I remember now! <a class="text-purple-700 font-semibold hover:text-[#5e17eb] transition-colors" href="<?= BASE_URL ?>login">Go back to login</a></p>
            </div>

          </form>

        </div>

      </div>

    </div>

  <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script>

  <script src="<?= BASE_URL ?>assets/js/auth/forgot-password/main.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>