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
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">

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
        grid-cols-1
        lg:mx-10 
        lg:p-0
        lg:max-w-screen-xl 
        lg:h-[650px] 
        lg:rounded-lg
        lg:grid
        lg:grid-cols-2
        lg:gap-6
      " id="forgot-password-wrapper"> 

        <!-- =========================================================
            SMART-EVAL — MOBILE HEADER
            Visible only below the lg breakpoint
            ========================================================= -->

        <div
            class="flex lg:hidden
                  relative w-full h-[200px]
                  overflow-hidden
                  rounded-b-[28px]
                  border-b border-white/10
                  shadow-lg shadow-[#312E81]/20
                  bg-[#0B1120]
                  animate-[mobileHeaderIn_0.6s_ease-out]"
        >

            <!-- Background School Image -->
            <img
                src="/Smart-Eval/public/assets/images/school-img.jpeg"
                alt="Southern Luzon State University"
                loading="lazy"
                class="absolute inset-0
                      w-full h-full
                      object-cover
                      scale-[1.08]
                      opacity-[0.60]
                      select-none
                      pointer-events-none"
            >

            <!-- Main Gradient Overlay -->
            <div
                class="absolute inset-0
                      bg-gradient-to-t
                      from-[#0B1120]/85
                      via-[#1E1B4B]/55
                      to-[#4338CA]/20
                      backdrop-blur-[2px]
                      pointer-events-none"
            ></div>

            <!-- Additional Bottom Depth -->
            <div
                class="absolute inset-x-0 bottom-0
                      h-[55%]
                      bg-gradient-to-t
                      from-[#0B1120]/30
                      via-transparent
                      to-transparent
                      pointer-events-none"
            ></div>

            <div
                class="absolute
                      left-6 bottom-6
                      flex items-center
                      gap-4
                      text-white"
            >

                <!-- Logo / Shield Placeholder -->
                <div
                    class="flex items-center justify-center
                          w-12 h-12
                          shrink-0
                          p-2
                          rounded-xl
                          bg-white/10
                          border border-white/20
                          backdrop-blur-md
                          shadow-sm"
                >
                    <img
                    src="<?= BASE_URL ?>assets/images/aite-logo.png"
                    alt="AITE Logo"
                    loading="lazy">

                </div>

                <!-- Divider -->
                <div
                    class="w-px h-10
                          bg-white/25"
                ></div>

                <!-- Brand Text -->
                <div class="leading-none">

                    <h1
                        class="text-[22px]
                              font-semibold
                              tracking-tight
                              text-white"
                    >
                        Smart-Eval
                    </h1>

                    <p
                        class="mt-1.5
                              text-[12px]
                              font-medium
                              tracking-wide
                              text-white/70"
                    >
                        AITE Evaluation System
                    </p>

                </div>

            </div>

            <!-- =====================================================
                SUBTLE DECORATIVE DOT PATTERN
                ===================================================== -->
            <div
                class="absolute
                      right-6 bottom-7
                      grid grid-cols-4
                      gap-2
                      opacity-25
                      pointer-events-none"
            >
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>

                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>

                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>

                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
                <span class="w-1 h-1 rounded-full bg-white"></span>
            </div>

        </div>

        <!-- left panel -->
        <div class="hidden lg:flex overflow-hidden relative slide-right" id="left-panel">

          <!-- Overlay -->
          <div class="absolute inset-0">

            <img  class="absolute inset-0 w-full h-full object-cover opacity-50 select-none scale-125" src="<?= BASE_URL ?>assets/images/school-img.jpeg" alt="school-img" loading="lazy" > 

            <div class="absolute inset-0 bg-gradient-to-t from-[#0B1120]/90 via-[#1E1B4B]/75 to-[#4338CA]/25 backdrop-blur-[1px]"></div>

            <img class="absolute -bottom-[50px] w-full z-10 select-none" src="<?= BASE_URL ?>assets/images/overlay.png" alt="overlay" loading="lazy"></img>

            <img class="absolute right-3 bottom-3 z-20 w-12 h-12 select-none sm:w-14 sm:h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24"
              src="<?= BASE_URL ?>assets/images/aite-logo.png"
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
                <span class="text-white font-medium">Full control, one dashboard. </span>
                <span class="text-purple-200">Manage users, roles, and evaluations across the system. Sign in to continue.</span>
            </p>

          </div>

        </div>
        
        <div class="flex justify-center items-center slide-left" id="right-panel">

          <form class="p-6 lg:p-0" id="forgot-password-form" method="POST">
              
              <div class="my-6 border-l-[3px] border-[#5e17eb] pl-4">
                  <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">WELCOME BACK, ADMIN</p>
                  <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Sign in to your dashboard</h1>
                  <p class="text-xs text-gray-400 leading-relaxed max-w-md">
                      Manage users, evaluations, and system settings for the Teacher Evaluation System.
                  </p>
              </div>

              <div class="my-8 text-sm text-gray-600 bg-gray-700/5 border-l-[5px] border-[#6010ff] py-5 px-5 rounded-md md:text-md">
                <p>A password reset link will be sent to your registered admin email.</p>
              </div>

              <!-- Admin Username Input -->
                <div>

                  <label class="block text-[10px] font-semibold text-[#6010ff] uppercase    tracking-widest mb-2">
                    Admin Username
                  </label>

                  <div class="relative">
                    
                    <div>

                      <span class="absolute left-4 top-2 md:left-4 md:top-2">
                        <i class="absolute top-1 left-2 fa-solid fa-user text-[#6010ff]  text-lg md:text-xl"></i>
                      </span>

                      <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="text" name="identifier" id="inputAdmin" placeholder="Admin Username" maxlength="50" required>

                    </div>

                  </div>
                </div>

              <div class="my-8">

                <button
                class="
                  w-full h-12
                  px-5
                  bg-gradient-to-r from-[#5e17eb] to-[#701cff]
                  text-white text-sm font-semibold
                  rounded-xl
                  shadow-md shadow-purple-900/15
                  cursor-pointer
                  transition-all duration-200 ease-out

                  hover:from-[#5010d6] hover:to-[#6415e8]
                  hover:shadow-lg hover:shadow-purple-900/20
                  hover:-translate-y-0.5

                  active:translate-y-0
                  active:shadow-sm

                  focus:outline-none
                  focus:ring-4 focus:ring-purple-600/15

                  disabled:opacity-60
                  disabled:cursor-not-allowed
                "
                type="submit"
                id="reset-button"
              >
                <span class="flex items-center justify-center gap-2">
                  <i class="fa-solid fa-paper-plane text-xs"></i>
                  <span>Send Reset Link</span>
                </span>
              </button>
              </div>

              <div class="mt-5">
                <p class="text-center text-sm text-gray-500" >Wait, I remember now! <a class="text-[#6010ff]/75 font-semibold hover:text-purple-900 transition-colors" href="<?= BASE_URL ?>admin-login">Go back to admin login</a></p>
              </div>

          </form>

        </div>

      </div>
    </div>

    <script> 
      window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
      window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
    </script> 
  
  <script src="<?= BASE_URL ?>assets/js/admin/auth/forgot-password/main.js" type="module"></script> <!-- Link to forgot password.js -->
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>