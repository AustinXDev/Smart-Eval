<?php 

require_once __DIR__ . '/../../app/init.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Password</title>

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

    <!-- Content Wrapper -->
      <div 
        class="
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
        ">
      
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
        <div 
          class="hidden lg:flex overflow-hidden relative slide-right" 
          id="left-panel"
        >

          <!-- Overlay -->
          <div class="absolute inset-0">

            <img  
              class="absolute inset-0 w-full h-full object-cover opacity-50 select-none scale-125" 
              src="<?= BASE_URL ?>assets/images/school-img.jpeg" 
              alt="school-img" 
              loading="lazy" 
            > 

            <div 
              class="absolute inset-0 bg-gradient-to-t from-[#0B1120]/90 via-[#1E1B4B]/75 to-[#4338CA]/25 backdrop-blur-[1px]">
            </div>

            <img 
              class="absolute -bottom-[50px] w-full z-10 select-none" 
              src="<?= BASE_URL ?>assets/images/overlay.png" 
              alt="overlay" 
              loading="lazy"
              >
            </img>

            <img 
              class="absolute right-3 bottom-3 z-20 w-12 h-12 select-none sm:w-14 sm:h-14 md:w-16 md:h-16 lg:w-20 lg:h-20 xl:w-24 xl:h-24"
              src="<?= BASE_URL ?>assets/images/aite-logo.png"
              alt="AITE Logo"
              loading="lazy"
            >

          </div>

          <div class="z-10 flex items-center gap-2.5 absolute top-8 left-10 bg-white/12 backdrop-blur-md border border-white/25 rounded-full pl-1.5 pr-4 py-1.5">

            <div class="w-7 h-7 rounded-full bg-white flex items-center justify-center overflow-hidden shrink-0">

              <img 
                src="<?= BASE_URL ?>assets/images/aite-logo.png" 
                alt="AITE" 
                class="w-5 h-5 object-contain" 
              />

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

              <p class="text-xs tracking-widest text-purple-200 font-medium mb-1.5">
                TEACHER EVALUATION SYSTEM
              </p>

              <h1 class="text-5xl font-medium text-white tracking-tight">
                Smart-Eval
              </h1>

            </div>

            <p class="max-w-[360px] text-sm leading-relaxed">

              <span class="text-white font-medium">
                Secure teacher evaluation, simplified. 
              </span>
              
              <span class="text-purple-200">
                Built for students, faculty, and administrators. Sign in to continue.
              </span>

            </p>

          </div>

        </div>

        <div 
          class="flex justify-center items-center slide-left" 
          id="right-panel"
        >

          <!-- Create Password Header Container -->
            <form 
              class = "p-6 lg:p-0"
              id="forgot-password-form" 
              method="post"
            >

              <!-- Greetings Wrapper -->
              <div class="mb-4 lg:my-4 border-l-[3px] border-[#5e17eb] pl-4">

                <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">
                  ACCOUNT RECOVERY
                </p>

                <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">
                  Create a new password
                </h1>

                <p class="text-xs text-gray-400 leading-relaxed">
                  Your new password must be different from previously used passwords.
                </p>

              </div>

              <div class="my-4 text-sm text-gray-600 bg-gray-50 border border-gray-200 py-4 px-5 rounded-md md:text-md">

                <p class="text-xs font-semibold text-gray-500 mb-2 tracking-wide">
                  PASSWORD REQUIREMENTS
                </p>

                <ul 
                  class="flex flex-col gap-1.5" 
                  id="password-requirements"
                >

                  <li 
                    class="flex items-center gap-2 text-gray-400" 
                    data-rule="length"
                  >
                    <svg 
                      class="w-4 h-4 flex-shrink-0"
                      fill="none" 
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >

                      <path 
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2" 
                        d="M5 13l4 4L19 7"
                      />

                    </svg>

                    At least 8 characters long

                  </li>

                  <li 
                    class="flex items-center gap-2 text-gray-400"
                    data-rule="uppercase"
                  >

                    <svg 
                      class="w-4 h-4 flex-shrink-0" 
                      fill="none" 
                      viewBox="0 0 24 24" stroke="currentColor"
                    >

                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M5 13l4 4L19 7"
                      />

                    </svg>

                    Contains at least one uppercase letter

                  </li>

                  <li 
                    class="flex items-center gap-2 text-gray-400" 
                    data-rule="number"
                  >

                    <svg 
                      class="w-4 h-4 flex-shrink-0" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >

                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M5 13l4 4L19 7"
                      />

                    </svg>

                    At least one number

                  </li>

                  <li 
                    class="flex items-center gap-2 text-gray-400" 
                    data-rule="symbol"
                  >
                    <svg 
                      class="w-4 h-4 flex-shrink-0" 
                      fill="none" 
                      viewBox="0 0 24 24" 
                      stroke="currentColor"
                    >

                      <path 
                        stroke-linecap="round" 
                        stroke-linejoin="round" 
                        stroke-width="2" 
                        d="M5 13l4 4L19 7"
                      />

                    </svg>

                    At least one special character

                  </li>

                </ul>
                
              </div>

              <!-- Inputs Wrapper -->
                <div class="flex flex-col gap-7 md:gap-8">
                  
                  <!-- New password Input -->
                    <div>

                      <label class="block text-[10px] font-semibold text-purple-700 uppercase    tracking-widest mb-2">
                      New Password
                      </label>

                      <div class="relative">

                          <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>

                          <input 
                            class="
                              w-full h-12
                              bg-white
                              border border-gray-200
                              rounded-xl
                              pl-11 pr-4
                              text-sm text-gray-800
                              placeholder:text-gray-400
                              transition-all duration-200
                              outline-none
                              hover:border-gray-300
                              focus:border-purple-600
                              focus:ring-4 focus:ring-purple-600/10" 
                            type="password" 
                            name="password" 
                            id="inputNewPassword" 
                            placeholder="Enter new password" 
                            required
                          >

                          <div class="absolute right-12 top-4 lg:top-3">

                            <div class="relative">

                              <span 
                                class="absolute -top-1 w-6 h-6 cursor-pointer md:top-0" 
                                id="hidden"
                              >

                                <i class="fa-solid fa-eye text-[#6c757d]"></i>

                              </span>

                              <span 
                                class="absolute w-6 h-6 -top-1 hidden cursor-pointer md:top-0" 
                                id="show"
                              >

                                 <i class="fa-solid fa-eye-slash text-[#6c757d]"></i>

                              </span>

                            </div>

                          </div>

                      </div>

                    </div>

                  <!-- Confirm Password Input -->
                    <div>

                      <label class="block text-[10px] font-semibold text-purple-700 uppercase    tracking-widest mb-2">
                      Confirm Password
                      </label>

                      <div class="relative">

                          <i class="fa-solid fa-key absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>

                          <input 
                            class="
                              w-full h-12
                              bg-white
                              border border-gray-200
                              rounded-xl
                              pl-11 pr-4
                              text-sm text-gray-800
                              placeholder:text-gray-400
                              transition-all duration-200
                              outline-none
                              hover:border-gray-300
                              focus:border-purple-600
                              focus:ring-4 focus:ring-purple-600/10" 
                            type="password" 
                            name="confirm_password" id="inputConfirmPassword" placeholder="Re-enter Password" 
                            required
                          >

                          <div class="absolute right-12 top-4 lg:top-3">

                            <div class="relative">

                              <span 
                                class="absolute -top-1 w-6 h-6 cursor-pointer md:top-0" id="hideConfirm">

                                  <i class="fa-solid fa-eye text-[#6c757d]"></i>

                              </span>

                              <span 
                                class="absolute w-6 h-6 -top-1 hidden cursor-pointer md:top-0" id="showConfirm"
                              >

                                 <i class="fa-solid fa-eye-slash text-[#6c757d]"></i>

                              </span>

                            </div>
                          </div>

                      </div>

                    </div>

                </div>

              <div class="mt-10">

                <input 
                  class="
                    w-full h-12
                    px-5
                    bg-[#5e17eb]
                    text-white text-sm font-semibold
                    rounded-xl
                    cursor-pointer
                
                    transition-all duration-200 ease-out
                
                    hover:bg-[#5014cc]
                    hover:shadow-lg hover:shadow-purple-600/20
                    hover:-translate-y-0.5
                
                    active:translate-y-0
                    active:shadow-sm
                
                    focus:outline-none
                    focus:ring-4 focus:ring-purple-600/15
                
                    disabled:opacity-60
                    disabled:cursor-not-allowed
                  " 
                  type="submit" 
                  name="reset-btn" 
                  value="Reset Password" 
                  id="reset-btn"
                >

              </div>

            </form>

        </div>

    </div> 

  </div>

  <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script> 

  <script src="<?= BASE_URL ?>assets/js/admin/auth/reset-password/main.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>