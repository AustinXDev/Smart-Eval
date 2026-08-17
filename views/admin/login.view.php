<?php 

require_once __DIR__ . '/../../app/init.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Login</title>
  
  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'?>

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

      <!-- Login Wrapper -->
    <div class="
      animate-fade-slide 
      h-full 
      w-full 
      bg-white 
      shadow-xl 
      shadow-[#8c52ff]/15 
      overflow-hidden 
      lg:mx-10 
      lg:p-0
      lg:max-w-screen-xl 
      lg:h-[650px] 
      lg:rounded-lg
      lg:grid
      lg:grid-cols-2
      lg:gap-6
    " id="login-wrapper"> 

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
                src="<?= BASE_URL ?>assets/images/school-img.jpeg"
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
                  <span class="text-white font-medium">Full control, one dashboard. </span>
                  <span class="text-purple-200">Manage users, roles, and evaluations across the system. Sign in to continue.</span>
              </p>

            </div>

          </div>

        <!-- right panel -->
          <div class="flex justify-center items-center slide-left" id="right-panel">  

            <form 
              class="p-6 lg:p-0"
              id="login-form" 
              method="POST
            "> 

              <!-- Greetings Wrapper -->
              <div class="mb-6 lg:my-6 border-l-[3px] border-[#5e17eb] pl-4">
                  <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">WELCOME BACK, ADMIN</p>
                  <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Sign in to your dashboard</h1>
                  <p class="text-xs text-gray-400 leading-relaxed max-w-md">
                      Manage users, evaluations, and system settings for the Teacher Evaluation System.
                  </p>
              </div>

              <!-- Inputs Wrapper -->
              <div class="flex flex-col gap-7 md:gap-10"> 

                <!-- Admin Username Input -->
                  <div>

                    <label class="block text-[10px] font-semibold text-[#6010ff]  uppercase tracking-widest mb-2">
                      Admin Username
                    </label>

                    <div class="relative ">

                      <span class="absolute left-4 top-2 md:left-4 md:top-2">
                        <i class="absolute top-1 left-2 fa-solid fa-user text-[#6010ff]  text-lg md:text-xl"></i>
                      </span>

                      <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="text" name="admin_username" id="inputAdminUserName" placeholder="Admin Username" maxlength="50" required>

                    </div>

                  </div>

                <!-- Admin Password Input -->
                  <div>

                    <label class="block text-[10px] font-semibold text-[#6010ff]  uppercase tracking-widest mb-2">
                      Admin Password
                    </label>

                    <div class="relative">

                      <span class="absolute left-4 top-2 md:left-4 md:top-2">
                        <i class="absolute top-1 left-2 fas fa-key text-[#6010ff]  text-lg md:text-xl"></i>
                      </span>

                      
                      <input class="bg-gray-100 w-full font-roboto py-3 px-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="password" id="inputAdminPassword" placeholder="Password" maxlength="15" required>

                      <div class="absolute right-12 top-4">

                          <div class="relative">

                              <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hidden">
                                  <i class="fa-solid fa-eye text-[#6c757d]"></i>
                              </span>

                              <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="show">
                                  <i class="fa-solid fa-eye-slash text-[#6c757d]"></i>
                              </span>

                          </div>

                  </div>

              </div>

              <div class="flex justify-between mt-5">

              <div class="flex items-center gap-2 md:gap-3">

                <input class="accent-purple-700 h-3.5 w-3.5" type="checkbox" name="remember_me" id="inputCheckbox">
                <span class="font-roboto text-gray-500 text-sm">Remember me</span>

              </div>

              <div>
                <a class="font-roboto text-[#6010ff]  text-sm font-medium hover:text-purple-800 transition-colors"
                href="<?= BASE_URL ?>admin-forgot-password">Forgot Password?</a>
              </div>

              </div>

              <div class="my-6">
              <input class=" w-full md:text-md
                            bg-[#6010ff]  text-white font-semibold text-sm
                            py-3 rounded-xl cursor-pointer
                            shadow-lg
                            transition-all duration-200
                            hover:shadow-2xl hover:-translate-y-1 hover:opacity-90
                            active:shadow-inner active:translate-y-0
                            focus:outline-none" 
                      type="submit" 
                      name="signin-btn" 
                      value="Sign in to Admin Dashboard"
                      id="singin-button"
              >
              </div>

              <!-- Security note -->
              <div class="flex items-center justify-center gap-2 mt-6 pt-5 border-t border-purple-50">

              <svg class="w-3 h-3 text-purple-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/>
              </svg>

              <span class="text-[10px] text-[#6010ff]/50 tracking-wide">Secured & encrypted — AITE Smart-Eval System</span>

              </div>

            </form>

          </div>

      </div>

    </div>

    <script> 
      window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
      window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
    </script> 
    
    <script type="module" src="<?= BASE_URL ?>assets/js/admin/auth/Admin_Login/main.js" ></script>
    <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>