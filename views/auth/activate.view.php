<?php

require_once __DIR__ . '/../../app/init.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activate Account</title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="/Smart-Eval/public/assets/css/custom.css">

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

              <p class="max-w-[360px] text-sm leading-relaxed"> <span class="text-white font-medium">Set a password to activate your account. </span> <span class="text-purple-200">Once confirmed, you can sign in and start submitting or managing teacher evaluations.</span> </p>

            </div>

          </div>

          <!-- right panel -->
          <div class="flex justify-center items-center slide-left" id="right-panel">

            <form id="activation-form" method="post">


              <!-- Greetings Wrapper -->
              <div class="my-4 border-l-[3px] border-[#5e17eb] pl-4">

                <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">ACCOUNT ACTIVATION</p>
                <h1 class="text-2xl font-semibold text-gray-900 mb-1.5">Set your password</h1>
                <p class="text-xs text-gray-400 leading-relaxed">
                  Choose a password to activate your account and finish setting up access.
                </p>

              </div>

              <div class="my-4 text-sm text-gray-600 bg-gray-50 border border-gray-200 py-4 px-5 rounded-md md:text-md">
                <p class="text-xs font-semibold text-gray-500 mb-2 tracking-wide">PASSWORD REQUIREMENTS</p>
                <ul class="flex flex-col gap-1.5" id="password-requirements">
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

                      <label class="block text-[10px] font-semibold text-[#5e17eb] uppercase tracking-widest mb-2">
                        Password
                      </label>

                      <div class="relative">

                          <span class="absolute left-4 top-2 md:left-4 md:top-2">
                            <i class="absolute top-1 left-2 fas fa-key text-[#5e17eb] text-lg md:text-xl"></i>
                          </span>

                          <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="password" id="inputNewPassword" placeholder="Enter new password" required>

                          <div class="absolute right-12 top-4">

                            <div class="relative">

                              <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hidden"><img src="/Smart-Eval/public/assets/icons/show.png" alt="hidden.png"></span>
                              <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="show"><img src="/Smart-Eval/public/assets/icons/view.png" alt="show.png"></span>

                            </div>
                          
                          </div>

                      </div>
                    </div>

                  <!-- Confirm Password -->
                    <div>

                      <label class="block text-[10px] font-semibold text-[#5e17eb] uppercase tracking-widest mb-2">
                        Confirm Password
                      </label>

                      <div class="relative">

                        <span class="absolute left-4 top-2 md:left-4 md:top-2">
                          <i class="absolute top-1 left-2 fa fa-lock text-[#5e17eb] text-lg md:text-xl"></i>
                        </span>

                        <input class="bg-gray-100 w-full font-roboto py-3 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-3 md:text-md" type="password" name="re-enter-password" id="inputConfirmPassword" placeholder="Confirm Password" required>

                        <div class="absolute right-12 top-4">

                          <div class="relative">
                            <span class="absolute -top-1 w-6 h-6 cursor-pointer" id="hideConfirm"><img src="/Smart-Eval/public/assets/icons/show.png" alt="hidden.png"></span>
                            <span class="absolute w-6 h-6 -top-1 hidden cursor-pointer" id="showConfirm"><img src="/Smart-Eval/public/assets/icons/view.png" alt="show.png"></span>
                          </div>

                        </div>

                      </div>

                    </div>
                </div>

                <div class="mt-6 flex items-center gap-2 text-sm text-gray-600"> 

                  <input type="checkbox" name="eula" id="eula">

                  <label for="eula" class="text-sm text-gray-600">

                    I agree to the
                    <button type="button" id="openTermsBtn" class="text-[#6010ff] hover:underline">Terms of Service</button>

                    and

                    <button type="button" id="openPrivacyBtn" class="text-[#6010ff] hover:underline">Privacy Policy</button>

                  </label>

                </div>

              <div class="mt-6">
                <input class="w-full bg-[#6010ff] text-white font-semibold text-sm py-3.5 rounded-2xl cursor-pointer hover:opacity-75" type="submit" name="activate-btn" value="Activate Account" id="activate-btn">
              </div>
            </form>
          
          </div>

        </div>

    
        <!-- EULA -->

        <div id="seTermsOverlay" class="fixed inset-0 z-[999] hidden items-center justify-center bg-[#12072F]/60 backdrop-blur-sm p-4">
 
          <div id="seTermsCard"
            class="bg-white w-full max-w-lg rounded-lg shadow-2xl shadow-[#8c52ff]/20 transform scale-95 opacity-0 transition-all duration-200 ease-out flex flex-col max-h-[85vh]">
        
            <!-- Header -->
            <div class="flex items-start justify-between px-7 pt-7 pb-4 border-b border-gray-100">

              <div>
                <p class="text-xs tracking-wide text-[#5e17eb] font-semibold mb-1.5">SMART-EVAL LEGAL</p>
                <h2 id="seTermsTitle" class="text-lg font-semibold text-gray-900">Terms of Service</h2>
              </div>

              <button id="seTermsClose" type="button" class="text-gray-300 hover:text-gray-500 transition-colors mt-1">
                <i class="fa-solid fa-xmark text-lg"></i>
              </button>

            </div>
        
            <!-- Tabs -->
            <div class="flex gap-1 px-7 pt-4">

              <button type="button" id="seTabTerms"
                class="se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors">
                Terms of Service
              </button>

              <button type="button" id="seTabPrivacy"
                class="se-tab text-xs font-semibold px-3.5 py-2 rounded-full transition-colors">
                Privacy Policy
              </button>

            </div>
        
            <!-- Body -->
            <div id="seTermsBody" class="px-7 py-5 overflow-y-auto text-sm text-gray-600 leading-relaxed flex-1">
        
              <div id="seContentTerms" class="flex flex-col gap-3">
                <p class="text-[11px] text-gray-400 uppercase tracking-widest font-semibold">Last updated: January 2026</p>
                <p>Replace this block with your actual Terms of Service copy. Suggested sections:</p>
                <p><span class="font-semibold text-gray-800">1. Acceptance of Terms</span> — what using Smart-Eval means the user agrees to.</p>
                <p><span class="font-semibold text-gray-800">2. Account Responsibilities</span> — accurate info, credential security, permitted use.</p>
                <p><span class="font-semibold text-gray-800">3. Evaluation Data</span> — how submitted evaluations are stored and used.</p>
                <p><span class="font-semibold text-gray-800">4. Acceptable Use</span> — what's prohibited on the platform.</p>
                <p><span class="font-semibold text-gray-800">5. Termination</span> — conditions under which access may be revoked.</p>
              </div>
        
              <div id="seContentPrivacy" class="hidden flex-col gap-3">
                <p class="text-[11px] text-gray-400 uppercase tracking-widest font-semibold">Last updated: January 2026</p>
                <p>Replace this block with your actual Privacy Policy copy. Suggested sections:</p>
                <p><span class="font-semibold text-gray-800">1. Information We Collect</span> — student ID, email, evaluation responses.</p>
                <p><span class="font-semibold text-gray-800">2. How We Use It</span> — processing evaluations, account activation, communication.</p>
                <p><span class="font-semibold text-gray-800">3. Data Sharing</span> — who within AITE has access (faculty, administrators).</p>
                <p><span class="font-semibold text-gray-800">4. Data Retention</span> — how long records are kept.</p>
                <p><span class="font-semibold text-gray-800">5. Your Rights</span> — how a student can request access or deletion.</p>
              </div>
        
            </div>
        
            <!-- Footer -->
            <div class="px-7 py-5 border-t border-gray-100 flex items-center gap-3">

              <button id="seTermsDecline" type="button"
                class="flex-1 text-gray-500 font-semibold text-sm py-3 rounded-2xl cursor-pointer hover:bg-gray-50 transition-colors">
                Decline
              </button>

              <button id="seTermsAgree" type="button"
                class="flex-1 bg-[#5e17eb] text-white font-semibold text-sm py-3 rounded-2xl cursor-pointer hover:opacity-75 transition-opacity">
                I Agree
              </button>

            </div>
        
          </div>
        
        </div>

    </div> 

  <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script>

  <script src="<?= BASE_URL ?>assets/js/auth/activation/main.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>