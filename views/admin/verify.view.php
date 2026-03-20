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
<body class="bg-[url('../../public/assets/images/BG-login.png')] bg-cover bg-center h-screen w-screen">

  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->

  <?php include '../../public/assets/includes/auth-floating-icons.php'; ?> <!-- Floating Icons -->

  <div class="overflow-hidden h-screen flex justify-center items-center relative">  <!-- Wrapper -->
    <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg"> <!-- Login Wrapper -->
      <form id="login-form" method="POST"> 
        <div class="shadow-[0_1px_5px_rgba(0,0,0,0.1)] flex justify-center items-center gap-3 border-b-2 pt-2 -mx-6 -my-2 border-gray-200 -mx-2 md:shadow-[0] md:-mx-10 pb-2 md:flex-row"> <!-- Login Header Container -->
          <div> <!-- Logo wrapper -->
            <img class="h-12 w-12 md:h-20 md:w-20 drop-shadow-xl" src="../../public/assets/images/aite-logo.png" alt="aite-logo.png">
          </div>
          <div class="select-none"> <!-- System name wrapper -->
            <h1 class="font-roboto text-2xl font-bold text-purple-900 uppercase  md:text-4xl md:text-left md:font-extrabold text-shadow-md text-shadow-gray-300">Smart<span class="text-green-600">Eval</span></h1>
            <span class="font-roboto text-gray-400 text-center text-xs font-regular w-5  md:text-sm md:w-full">Your feedback helps improve teaching quality</span>
          </div>
        </div>

        <div class="mt-10 my-8 my-5 md:my-6 md:my-8"> <!-- Greetings Wrapper -->
          <h1 class="font-roboto text-xl text-purple-900 font-bold md:text-2xl">Verify Your Identity</h1>
          <p class="font-roboto text-gray-500 text-xs md:text-md">We’ve  sent 6-digit code to your email.
          <span><?php echo anonymousEmail($email); ?></span></p>
        </div>

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
            <button id="resend-btn" class="hidden text-purple-900 font-semibold underline cursor-pointer">Resend Code</button>
          </p>
        </div>
      </form>
    </div>

    <?php include '../../public/assets/includes/footer.php'; ?>

    <script src="../../public/assets/js/admin/admin_verify.js" type="module"></script>
</body>
</html>