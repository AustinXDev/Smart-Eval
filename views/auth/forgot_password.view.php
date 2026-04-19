<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <?php include_once '../../public/assets/includes/head.php'; ?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

  <link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  />

</head>

<body class="min-h-screen w-screen overflow-hidden relative flex items-center justify-center" style="background: linear-gradient(135deg, #1E0F4E 0%, #2D1B69 40%, #4C1D95 70%, #6D28D9 100%);">

  <!-- Background orbs for depth -->
  <div class="absolute top-[-80px] left-[-80px] w-72 h-72 rounded-full opacity-20" style="background:#A78BFA;"></div>
  <div class="absolute bottom-[-60px] right-[-60px] w-56 h-56 rounded-full opacity-15" style="background:#C4B5FD;"></div>
  <div class="absolute top-1/2 right-[8%] w-28 h-28 rounded-full opacity-10" style="background:#EDE9FE;"></div>

  <!-- Toast Notification -->
  <?php include '../../public/assets/includes/toast.php'; ?> 

   <!-- Wrapper -->
    <div class="h-screen flex justify-center items-center">
      
      <!-- Forgot Password Wrapper -->
        <div class="animate-fade-slide h-full w-full bg-white  shadow-lg/20 overflow-hidden px-6 pb-12 pt-2 md:h-auto md:w-auto  md:rounded-lg md:px-12 md:pb-15 md:w-lg"> 

          <form id="forgot-password-form" method="POST">

            <!-- forgot-password Header Container -->
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
                <h1 class="font-roboto text-2xl font-bold text-[#1E0F4E] mb-1">Reset Your Password</h1>
                <p class="font-roboto text-gray-400 text-sm">Enter your registered Student ID to recover your account.
                </p>
              </div>

            <div class="my-8 text-sm text-gray-600 bg-gray-700/5 border-l-[5px] border-orange-600 py-5 px-5 rounded-md md:text-md">
              <p>A password reset link will be sent to your email inbox.</p>
            </div>

            <!-- Student ID -->
              <div>

                <label class="block text-[10px] font-semibold text-purple-700 uppercase    tracking-widest mb-2">
                Student ID
                </label>

                <div class="relative">

                  <div>

                    <span class="absolute left-4 top-2 md:left-4 md:top-3">
                      <i class="absolute top-1 left-2 fa-solid fa-user text-purple-900 text-lg md:text-xl"></i>
                    </span>

                    <input class="bg-gray-100 w-full font-roboto py-3 pl-17 text-sm shadow-md rounded-sm focus:border-purple-900 focus:outline-2 focus:outline-purple-900 md:py-4 md:text-md" type="text" name="student_id" id="inputStudentID" placeholder="Student ID Number (ex. 00-0000)" required>

                  </div>

                </div>

              </div>

            <div class="my-8">
              <button class="w-full bg-purple-900 text-white font-semibold text-sm py-3 rounded-xl cursor-pointer hover:opacity-75 md:text-md" type="submit" id="reset-button">Reset Password</button>
            </div>

            <div class="mt-5">
              <p class="text-center text-sm text-gray-500" >Wait, I remember now! <a class="text-purple-700 font-semibold hover:text-purple-900 transition-colors" href="login.view.php">Go back to login</a></p>
            </div>

          </form>
        </div>
    </div>

  <script src="../../public/assets/js/auth/forgot_password.js" type="module"></script> <!-- Link to forgot password.js -->
</body>
</html>