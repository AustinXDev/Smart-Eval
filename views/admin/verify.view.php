<?php 
require_once __DIR__ . '/../../app/helpers/session.php';
require_once __DIR__ . '/../../app/middleware/require_2FA_auth.php';
require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/helpers/functions.php';

$adminID = get2FAAdminId();

$stmt = $pdo->prepare("SELECT email FROM admins WHERE admin_id = ?");
$stmt->execute([$adminID]);
$result = $stmt->fetch();

$email = $result['email'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verify Account</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">
</head>
<body class="min-h-screen w-screen overflow-hidden relative flex items-center justify-center" style="background: linear-gradient(135deg, #1E0F4E 0%, #2D1B69 40%, #4C1D95 70%, #6D28D9 100%);">

  <!-- Background orbs for depth -->
    <div class="absolute top-[-80px] left-[-80px] w-72 h-72 rounded-full opacity-20" style="background:#A78BFA;"></div>
    <div class="absolute bottom-[-60px] right-[-60px] w-56 h-56 rounded-full opacity-15" style="background:#C4B5FD;"></div>
    <div class="absolute top-1/2 right-[8%] w-28 h-28 rounded-full opacity-10" style="background:#EDE9FE;"></div>

  <!-- Toast Notification -->
    <?php include '../../public/assets/includes/toast.php'; ?> 
  
  <!-- Wrapper -->
    <div class="overflow-hidden h-screen flex justify-center items-center relative"> 
      
      <!-- Login Wrapper -->
        <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg">

          <form id="login-form" method="POST"> 

            <!-- Verify Header Container -->
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
                <h1 class="font-roboto text-xl font-bold text-[#1E0F4E] mb-1">Verify Your Identity</h1>
                <p class="font-roboto text-gray-500 text-sm">We’ve  sent 6-digit code to your email.
                <span><?php echo anonymousEmail($email); ?></span></p>
              </div>

            <!-- INPUTS -->
            <div class="flex justify-center items-center gap-3">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
                <input class="otp-input w-12 h-12 text-center text-xl font-bold border-2 border-gray-300 rounded-lg focus:border-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-900 bg-gray-100 shadow-md md:w-14 md:h-14" 
                      type="text" maxlength="1" inputmode="numeric" pattern="[0-9]*">
            </div>


            <div class="mt-8 text-center">
              <p class="text-gray-500 text-sm">
                Didn't get a code? 
                <span id="countdown-text">Reset in <span id="timer">45</span>s</span>
                <button id="resend-btn" class="font-roboto text-purple-600 text-sm font-medium hover:text-purple-800 transition-colors">Resend Code</button>
              </p>
            </div>
          </form>

        </div>

    </div>

    <script src="../../public/assets/js/admin/auth/admin_verify.js" type="module"></script>
</body>
</html>