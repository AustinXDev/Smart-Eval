<!-- Edit Student Modal -->
<div id="editStudentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden p-2">

  <div class="bg-gray-100 w-full max-w-4xl rounded-xl shadow-xl overflow-hidden animate-[fadeIn_.2s_ease] max-h-[95vh] overflow-y-auto">

    <!-- Header -->
    <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-start border-b-2 border-purple-500">
      <div>
        <h2 class="text-white text-lg font-semibold">Edit Student Information</h2>
        <p class="text-gray-400 text-xs">Make changes to the student’s profile</p>
      </div>
      <button data-close-modal="editStudentModal" class="text-white text-2xl hover:scale-110 transition">&times;</button>
    </div>

    <!-- Content -->
    <div class="p-6">

      <!-- PROFILE -->
      <div class="flex flex-col sm:flex-row items-center gap-5 mb-6">

        <!-- Avatar -->
        <div class="relative group">
          <div class="absolute inset-0 bg-purple-500 blur-xl opacity-30 rounded-full"></div>

          <div id="editStudentAvatar" class="w-24 h-24 rounded-full bg-gradient-to-br from-purple-500 via-indigo-500 to-purple-700 flex items-center justify-center text-white text-3xl font-bold shadow-lg ring-4 ring-white group-hover:scale-105 transition">
            JD
          </div>
        </div>

        <!-- Info -->
        <div class="text-center sm:text-left">
          <h2 id="editStudentName" class="text-2xl font-semibold text-gray-800">Juan Dela Cruz</h2>
          <p id="editStudentId" class="text-gray-500">Student ID: S10005</p>

          <span id="editStudentStatus" class="inline-flex items-center gap-1 mt-2 px-3 py-1 text-xs bg-green-600 text-white rounded-full shadow">
            ✔ Active
          </span>
        </div>
      </div>

      <!-- FORM -->
      <form method="POST" id="editStudentForm">
        <div class="bg-white rounded-xl shadow p-5">

          <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            📋 Student Information
          </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
              <input type="hidden" name="old_student_id" id="oldStudentIdInput">

              <!-- Student ID -->
              <div class="relative">
                <input id="editStudentIDInput" type="text" name="student_id" 
                  class="peer w-full border rounded-md px-3 pt-5 pb-2 text-sm bg-gray-100 focus:outline-none">
                <label class="absolute left-3 top-2 text-xs text-gray-500">Student ID *</label>
              </div>

              <!-- Year Level -->
              <div class="relative">
                <select name="year_level" id="editStudentYearLevel" class="peer w-full border rounded-md px-3 pt-5 pb-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                  <?php if ($department === 'college') { ?>
                    <option disabled selected>Select Year Level</option>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                  <?php } else { ?>
                    <option disabled selected>Select Grade Level</option>
                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>
                  <?php } ?>
                </select>
                <label class="absolute left-3 top-2 text-xs text-gray-500"><?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>
                  <span class="text-red-500">*</span></label>
              </div>

              <!-- Full Name -->
              <div class="relative">
                <input id="editStudentNameInput" type="text" name="full_name"
                  class="peer w-full border rounded-md px-3 pt-5 pb-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                <label class="absolute left-3 top-2 text-xs text-gray-500">Full Name *</label>
              </div>

              <!-- Email -->
              <div class="relative">
                <input id="editStudentEmailInput" type="email" name="email" value="example@gmail.com"
                  class="peer w-full border rounded-md px-3 pt-5 pb-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                <label class="absolute left-3 top-2 text-xs text-gray-500">Email Address *</label>
              </div>

              <!-- Program -->
              <div class="relative sm:col-span-2">
                <select id="editProgramSelect" name="program_id" class="peer w-full border rounded-md px-3 pt-5 pb-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                  <!-- Option value will be populated dynamically with JS -->
                </select>
                <label class="absolute left-3 top-2 text-xs text-gray-500"><?php echo ($department === 'college') ? 'Course' : 'Program'; ?>
              <span class="text-red-500">*</span></label>
              </div>

            </div>
        </div>

          <!-- ACTIONS -->
        <div class="flex justify-end mt-6">
          <button id="saveBtn"
            class="group relative px-6 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-lg shadow hover:scale-105 transition flex items-center gap-2">

            <span>Save Changes</span>

            <!-- Loader (hidden default) -->
            <span class="hidden group-[.loading]:inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>

          </button>
        </div>
      </form>
      

    </div>
  </div>
</div>