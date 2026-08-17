<!-- View Student Modal -->
<div id="viewStudentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden p-2">

  <div class="bg-gray-100 rounded-xl shadow-lg w-full max-w-3xl max-h-[95vh] overflow-y-auto">

    <!-- Header -->
    <div class="bg-[#0F172A] px-4 sm:px-6 py-4 flex justify-between items-start border-b-2 border-purple-500">
      <div>
        <h2 class="text-white text-base sm:text-lg font-semibold">Student Information</h2>
        <p class="text-gray-300 text-xs">View student profile details</p>
      </div>
      <button data-close-modal="viewStudentModal" class="text-white text-2xl">&times;</button>
    </div>

    <!-- Content -->
    <div class="p-4 sm:p-6">

      <!-- Profile Section -->
      <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4 sm:gap-5 mb-6 text-center sm:text-left">

        <!-- Avatar -->
        <div class="relative w-20 h-20 sm:w-28 sm:h-28 flex items-center justify-center">

          <div class="absolute inset-0 rounded-full bg-purple-500 blur-xl opacity-30"></div>

          <div class="absolute inset-0 rounded-full p-[3px] bg-gradient-to-tr from-purple-500 via-indigo-500 to-purple-700">
            <div class="w-full h-full rounded-full bg-white"></div>
          </div>

          <div id="studentAvatar" class="relative w-16 h-16 sm:w-24 sm:h-24 rounded-full bg-gradient-to-br from-purple-500 via-indigo-500 to-purple-700 flex items-center justify-center text-white text-xl sm:text-3xl font-bold">
            JD
          </div>
        </div>

        <!-- Info -->
        <div>
          <h2 id="studentName" class="text-lg sm:text-2xl font-semibold text-gray-800"></h2>
          <p id="studentId" class="text-gray-500 text-sm sm:text-base"></p>

          <span id="studentStatus"
            class="inline-block mt-2 px-3 py-1 text-xs sm:text-sm bg-green-600 text-white rounded-md">
          </span>
        </div>
      </div>

      <!-- Student Details -->
      <div class="bg-white rounded-lg shadow p-4 sm:p-5 mb-5">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Student Details</h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">

          <div>
            <p class="text-gray-500">Student ID</p>
            <p id="studentIdDetail" class="font-medium"></p>
          </div>

          <div>
            <p class="text-gray-500">Department</p>
            <p id="studentDepartment" class="font-medium"></p>
          </div>

          <div>
            <p class="text-gray-500">Year Level</p>
            <p id="studentYearLevel" class="font-medium"></p>
          </div>

          <div>
            <p class="text-gray-500">Program / Course</p>
            <span id="studentProgram"
              class="inline-block px-2 py-1 bg-blue-600 text-white rounded text-xs"></span>
          </div>

          <div class="sm:col-span-2">
            <p class="text-gray-500">Email Address</p>
            <p id="studentEmail" class="text-blue-600 break-all"></p>
          </div>

          <div>
            <p class="text-gray-500">Status</p>
            <span id="studentStatusDetail"
              class="inline-block px-2 py-1 bg-green-600 text-white rounded text-xs"></span>
          </div>

        </div>
      </div>

      <!-- Account Management -->
      <div class="bg-white rounded-lg shadow p-4 sm:p-5">
        <h3 class="font-semibold text-gray-800 mb-3 text-sm sm:text-base">Account Management</h3>

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

          <div>
            <p class="font-medium text-gray-700 text-sm">Reset Password</p>
            <p class="text-xs text-gray-500">Reset the student’s system password.</p>
          </div>

          <button
            id="resetPasswordBtn"
            class="w-full sm:w-auto flex justify-center items-center gap-2 px-4 py-2 bg-orange-400 hover:bg-orange-500 text-white rounded shadow text-sm">
            🔑 Reset Password
          </button>

        </div>
      </div>

    </div>
  </div>
</div>