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

  <?php include __DIR__ . '/../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <!-- Content Wrapper -->
    <div class="overflow-hidden w-screen h-dvh mx-auto flex justify-center items-center relative lg:mx-w-screen-2xl">

     <!-- Create New Password Wrapper -->
        <div class="
          animate-fade-slide 
          h-full 
          w-full 
          bg-white 
          shadow-xl 
          shadow-[#8c52ff]/15 
          overflow-hidden 
          p-6
          lg:p-0
          lg:mx-10 
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
                <img src="<?= BASE_URL ?>assets/images/aite-logo.png" alt="AITE" class="w-5 h-5 object-contain" />
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
                <span class="text-white font-medium">Secure teacher evaluation, simplified. </span>
                <span class="text-purple-200">Built for students, faculty, and administrators. Sign in to continue.</span>
              </p>

            </div>

          </div>

          <!-- right panel -->
          <div class="flex justify-center items-center slide-left" id="right-panel">

            <form id="forgot-password-form" method="post">


              <!-- Greetings Wrapper -->
              <div class="my-4 border-l-[3px] border-[#5e17eb] pl-4">

                <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">ACCOUNT RECOVERY</p>
                <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Create a new password</h1>
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

                  <li class="flex items-center gap-2 text-gray-400" data-rule="length">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    At least 8 characters long
                  </li>
                  <li class="flex items-center gap-2 text-gray-400" data-rule="uppercase">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Contains at least one uppercase letter
                  </li>
                  <li class="flex items-center gap-2 text-gray-400" data-rule="number">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    At least one number
                  </li>
                  <li class="flex items-center gap-2 text-gray-400" data-rule="symbol">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    At least one special character
                  </li>
                </ul>
              </div>

              <!-- Inputs Wrapper -->
                <div class="flex flex-col gap-7 md:gap-6"> 

                  <!-- Password -->
                    <div>

                      <label class="block text-[10px] font-semibold text-purple-700 uppercase tracking-widest mb-2">
                        New Password
                      </label>

                      <div class="relative">

                          <span class="absolute left-4 top-2 md:left-4 md:top-2">
                            <i class="absolute top-1 left-2 fas fa-key text-purple-900 text-lg md:text-xl"></i>
                          </span>

                          <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="password" id="inputNewPassword" placeholder="Enter new password" required>

                          <div class="absolute right-12 top-4">

                            <div class="relative">

                              <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hidden"><img src="<?= BASE_URL ?>assets/icons/show.png" alt="hidden.png"></span>
                              <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="show"><img src="<?= BASE_URL ?>assets/icons/view.png" alt="show.png"></span>

                            </div>
                          
                          </div>

                      </div>
                    </div>

                  <!-- Confirm Password -->
                    <div>

                      <label class="block text-[10px] font-semibold text-purple-700 uppercase tracking-widest mb-2">
                        Confirm New Password
                      </label>

                      <div class="relative">

                        <span class="absolute left-4 top-2 md:left-4 md:top-2">
                          <i class="absolute top-1 left-2 fa fa-lock text-purple-900 text-lg md:text-xl"></i>
                        </span>

                        <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="re-enter-password" id="inputConfirmPassword" placeholder="Confirm Password" required>

                        <div class="absolute right-12 top-4">

                          <div class="relative">
                            <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hideConfirm"><img src="<?= BASE_URL ?>assets/icons/show.png" alt="hidden.png"></span>
                            <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="showConfirm"><img src="<?= BASE_URL ?>assets/icons/view.png" alt="show.png"></span>
                          </div>

                        </div>

                      </div>

                    </div>
                </div>

              <div class="mt-10">
                <input class="w-full bg-[#6010ff] text-white font-semibold text-sm py-3.5 rounded-2xl cursor-pointer hover:opacity-75" type="submit" name="reset-btn" value="Reset Password" id="reset-btn">
              </div>
            </form>
          
          </div>

        </div>

    </div> 

  <script>
    window.BASE_URL = "<?= BASE_URL ?>";
  </script>

  <script src="<?= BASE_URL ?>assets/js/auth/reset-password/main.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>