<!-- Add Student Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
  
  <div class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-2xl mx-2 overflow-hidden">

    <!-- Header -->
    <div class="bg-[#0F172A] px-6 py-4 border-b-2 border-purple-500">
      <h2 class="text-white text-lg font-semibold">Add New Student</h2>
      <p class="text-gray-400 text-xs">Authorize a student to access the evaluation system</p>
    </div>

    <!-- Form -->
    <form id="addStudentForm" class="p-6 flex flex-col gap-6" method="POST">

      <!-- BASIC INFO -->
      <div class="bg-white p-5 rounded-lg shadow">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <i class="fas fa-user text-purple-600"></i>
          Basic Information
        </h3>

        <div class="flex flex-col gap-4">

          <div>
            <label class="text-gray-600 text-sm">Student ID <span class="text-red-500">*</span></label>
            <input type="text" name="student_id" placeholder="e.g., 00-0000"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none transition"
              required>
          </div>

          <div>
            <label class="text-gray-600 text-sm">Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" placeholder="First name, Middle initials, Last name"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none transition"
              required>
          </div>

          <div>
            <label class="text-gray-600 text-sm">Email Address <span class="text-red-500">*</span></label>
            <input type="email" name="email" placeholder="example@gmail.com"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none transition"
              required>
          </div>

        </div>
      </div>

      <!-- ACADEMIC -->
      <div class="bg-white p-5 rounded-lg shadow">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
          <i class="fas fa-graduation-cap text-purple-600"></i>
          Academic Placement
        </h3>

        <div class="flex flex-col md:flex-row gap-4">

          <div class="w-full">
            <label class="text-gray-600 text-sm">
              <?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>
              <span class="text-red-500">*</span>
            </label>

            <select name="year"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none"
              required>
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
          </div>

          <div class="w-full">
            <label class="text-gray-600 text-sm">
              <?php echo ($department === 'college') ? 'Course' : 'Program'; ?>
              <span class="text-red-500">*</span>
            </label>

            <select name="program" id="programSelect"
              class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none"
              required>
              <option disabled selected>Select Program</option>
              <!-- JS fill -->
            </select>
          </div>

        </div>
      </div>

      <!-- ACTIONS -->
      <div class="flex justify-end gap-3">

        <button type="button" data-close-modal="addStudentModal"
          class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition">
          Cancel
        </button>

        <button type="submit"
          class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md shadow transition flex items-center gap-2">
          <i class="fas fa-user-plus"></i>
          Add Student
        </button>

      </div>

    </form>
  </div>
</div>