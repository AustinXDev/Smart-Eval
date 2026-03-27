<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Create New Password</title>

  <?php include_once '../../public/assets/includes/head.php'; ?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">
</head>
<body class="bg-[url('../../public/assets/images/BG-login.png')] bg-cover bg-center h-screen w-screen">

  <?php include '../../public/assets/includes/toast.php'; ?> <!-- Toast Notification -->
  
  <?php include '../../public/assets/includes/auth-floating-icons.php'; ?> <!-- Floating Icons -->

  <!-- Content Wrapper -->
  <div class="h-screen flex justify-center items-center">

    <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg"> <!-- Create Password Wrapper -->
      <form id="forgot-password-form" method="post">
        <div class="shadow-[0_1px_5px_rgba(0,0,0,0.1)] flex justify-center items-center gap-3 border-b-2 pt-2 -mx-6 -my-2 border-gray-200 -mx-2 md:shadow-[0] md:-mx-10 pb-2 md:flex-row"> <!-- Create Password Header Container -->
          <div> <!-- Logo wrapper -->
            <img class="h-12 w-12 md:h-20 md:w-20 drop-shadow-xl" src="../../public/assets/images/aite-logo.png" alt="aite-logo.png">
          </div>
          <div class="select-none leading-tight">
            <h1 class="font-roboto m-0 text-2xl font-bold text-purple-900 uppercase md:text-4xl md:text-left md:font-extrabold">
              Smart<span class="text-green-600">Eval</span>
            </h1>
            <span class="font-roboto text-gray-400 text-xs md:text-sm block">
              Your feedback helps improve teaching quality
            </span>
          </div>
        </div>

        <div class="mt-10 my-8 my-5 md:my-6 md:my-8"> <!-- Greetings Wrapper -->
          <h1 class="font-roboto text-xl text-purple-900 font-bold md:text-3xl">Create New Admin Password</h1>
          <p class="font-roboto text-gray-500 text-xs md:text-md">Please enter a new password for your admin account. Make sure it is secure and not used before.</p>
        </div>

        <div class="my-8 text-sm text-gray-600 bg-gray-700/5 border-l-[5px] border-orange-600 py-5 px-5 rounded-md md:text-md">
          <ul class="flex flex-col gap-2 mx-2">
            <li class="list-disc">At least 8 characters long</li>
            <li class="list-disc">Contains at least one uppercase letter</li>
            <li class="list-disc">At least one number</li>
            <li class="list-disc">At least one special character</li>
          </ul>
        </div>

        <div class="flex flex-col gap-7 md:gap-10"> <!-- Inputs Wrapper -->

          <div class="relative">
              <span class="absolute left-5 top-3 md:left-4 md:top-3.5">
                <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/key.png" alt="password-icon.png">
              </span>
              <div></div>
              <input class="bg-gray-100 w-full font-roboto py-4 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-lg" type="password" name="password" id="inputNewPassword" placeholder="Enter new password" required>
              <div class="absolute right-12 top-4">
                <div class="relative">
                  <span class="absolute -top-1 w-8 h-8 cursor-pointer md:top-0" id="hidden"><img src="../../public/assets/icons/show.png" alt="hidden.png"></span>
                  <span class="absolute w-8 h-8 -top-1 hidden cursor-pointer md:top-0" id="show"><img src="../../public/assets/icons/view.png" alt="show.png"></span>
                </div>
              </div>
          </div>

          <div class="relative">
              <span class="absolute left-5 top-3.5 md:left-4 md:top-3.5">
                <img class="w-6 h-6 md:w-8 md:h-8" src="../../public/assets/icons/approve.png" alt="password-icon.png">
              </span>
              <div></div>
              <input class="bg-gray-100 w-full font-roboto py-4 pl-15 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-lg" type="password" name="confirm_password" id="inputConfirmPassword" placeholder="Confirm Password" required>
              <div class="absolute right-12 top-4">
                <div class="relative">
                  <span class="absolute -top-1 w-8 h-8 cursor-pointer md:top-0" id="hideConfirm"><img src="../../public/assets/icons/show.png" alt="hidden.png"></span>
                  <span class="absolute w-8 h-8 -top-1 hidden cursor-pointer md:top-0" id="showConfirm"><img src="../../public/assets/icons/view.png" alt="show.png"></span>
                </div>
              </div>
            </div>

        </div>

        <div class="mt-10">
          <input class="w-full bg-purple-900 text-white font-semibold text-sm py-3 rounded-xl cursor-pointer hover:opacity-75 md:text-lg" type="submit" name="reset-btn" value="Reset Password" id="reset-btn">
        </div>
      </form>

    </div>

  </div> 

  <script src="../../public/assets/js/admin/auth/reset_password.js" type="module"></script>

</body>
</html>