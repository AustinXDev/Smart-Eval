<?php require '../../app/middleware/require_auth.php'; ?>
<?php require_once __DIR__ . '/../../app/middleware/student_enrollment_guard.php'; ?>

<?php 
$role      = 'student';
$student = getStudent();
$nav       = $navigation[$role] ?? [];
$currentUrl = $_SERVER['REQUEST_URI'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Enrollment Type - Teacher Evaluation</title>

    <?php include_once __DIR__ . '/../../public/assets/includes/head.php' ?>

    <link rel="stylesheet" href="/Smart-Eval/public/assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="flex justify-center items-start min-h-screen bg-gray-50 py-4">

    <!-- Main Content -->
    <main class="p-4 w-full max-w-5xl">
        <div class="mx-auto">

            <!-- Header Section -->
            <div class="text-center mb-8 md:mb-12 px-4 md:px-0">
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">Choose Your Enrollment Type</h1>
                <p class="text-gray-600 text-sm md:text-base">Select how you would like to evaluate your teachers</p>
            </div>

            <!-- Student Info Banner -->
            <div class="bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-lg shadow-lg p-4 md:p-6 mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-4 md:gap-0">
                <div>
                    <h3 class="text-xs md:text-sm uppercase tracking-wide opacity-90 mb-1">Welcome back,</h3>
                    <p class="text-lg md:text-xl font-semibold"><?php echo $student['full_name']; ?></p>
                </div>
                <span id="program" data-program-id="<?php echo $student['program_id']?>" class="bg-white bg-opacity-20 px-3 py-1 md:px-4 md:py-2 rounded-full text-xs md:text-sm font-semibold backdrop-blur text-blue-700">
                    <i class="fas fa-book mr-1 md:mr-2"></i><span id="programName"></span>
                </span>
            </div>

            <form id="enrollmentForm" method="POST">
              <input type="hidden" name="enrollment_type" id="selectedTypeInput" value="">
              <!-- Options Grid -->
              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 mb-8 px-2 md:px-0">
                  
                  <!-- Regular Student Option -->
                  <div class="option-card bg-white border-2 border-gray-200 rounded-xl p-6 sm:p-8 cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all duration-300 flex flex-col">
                      <div class="h-12 w-12 md:h-14 md:w-14 bg-blue-100 rounded-lg flex items-center justify-center mb-4 md:mb-6 text-2xl text-blue-500">
                          <i class="fas fa-check-circle"></i>
                      </div>
                      
                      <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Regular Student</h3>
                      <p class="text-gray-600 text-xs md:text-sm mb-4 md:mb-6">For students with assigned teachers</p>
                      
                      <ul class="space-y-2 md:space-y-3">
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-green-500 mr-2 md:mr-3 font-bold"></i>
                              View your assigned teachers
                          </li>
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-green-500 mr-2 md:mr-3 font-bold"></i>
                              Auto-loaded from your program
                          </li>
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-green-500 mr-2 md:mr-3 font-bold"></i>
                              Quick evaluation process
                          </li>
                      </ul>
                  </div>

                  <!-- Irregular Student Option -->
                  <div class="option-card bg-white border-2 border-gray-200 rounded-xl p-6 sm:p-8 cursor-pointer hover:shadow-lg hover:border-gray-300 transition-all duration-300 flex flex-col">
                      <div class="h-12 w-12 md:h-14 md:w-14 bg-amber-100 rounded-lg flex items-center justify-center mb-4 md:mb-6 text-2xl text-amber-600">
                          <i class="fas fa-pen-square"></i>
                      </div>
                      
                      <h3 class="text-lg md:text-xl font-semibold text-gray-900 mb-2">Irregular Student</h3>
                      <p class="text-gray-600 text-xs md:text-sm mb-4 md:mb-6">For students selecting their own teachers</p>
                      
                      <ul class="space-y-2 md:space-y-3">
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-amber-600 mr-2 md:mr-3 font-bold"></i>
                              Choose teachers to evaluate
                          </li>
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-amber-600 mr-2 md:mr-3 font-bold"></i>
                              Use checkboxes to select
                          </li>
                          <li class="flex items-center text-gray-700 text-xs md:text-sm">
                              <i class="fas fa-check text-amber-600 mr-2 md:mr-3 font-bold"></i>
                              Flexible evaluation list
                          </li>
                      </ul>
                  </div>

              </div>

              <div class="text-center mt-6 mb-6">
                <button type="submit"
                  id="proceedBtn"
                  class="relative overflow-hidden bg-blue-500 text-white px-8 py-3 rounded-xl font-semibold
                        transition-all duration-300 disabled:bg-gray-300 disabled:cursor-not-allowed
                        hover:bg-blue-600 hover:scale-105 active:scale-95
                        shadow-md hover:shadow-xl flex items-center justify-center gap-2 mx-auto" disabled>

                  <span id="btnText">Proceed</span>

                  <!-- Spinner -->
                  <svg id="btnLoader" class="hidden w-5 h-5 animate-spin"
                      xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"></circle>
                    <path class="opacity-75" fill="white"
                      d="M4 12a8 8 0 018-8v8H4z">
                    </path>
                  </svg>
                </button>
              </div>

            </form>
   
           <!-- Info Box -->
            <div class="bg-amber-50 border-l-4 border-amber-500 rounded p-4 md:p-6 mb-8 text-sm md:text-base">
                <p class="text-gray-700 leading-relaxed">
                    <strong class="text-gray-900">Need help?</strong> Regular students automatically get their assigned teachers based on their program. Irregular students can manually select the teachers they want to evaluate from the available list.
                </p>
            </div>

        </div>
    </main>

  <script src="../../public/assets/js/evaluation/select_enrollment.js" type="module"></script>
</body>
</html>