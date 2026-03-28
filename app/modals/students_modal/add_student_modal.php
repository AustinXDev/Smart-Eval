<!-- Add Teacher Modal -->
<div id="addStudentModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl mx-2 sm:mx-0">
    
    <!-- Header -->
    <div class="bg-[#1A1A2E] -mx-6 px-6 py-4">
      <h2 class="text-lg font-semibold text-white m-0 ml-5">Add New Student</h2>
      <p class="text-white text-xs m-0 ml-5">Fill in the details below to authorize a student to access the evaluation system</p>
    </div>

    <!-- Form -->
    <form id="addStudentForm" class="flex flex-col gap-3 p-6" method="POST">

      <div>
        <h1 class="mb-2 font-semibold" style="font-family: 'Roboto', sans-serif;">Basic Information</h1>
        <div id="input-wrapper" class="w-full flex flex-col items-centers gap-4">
          <div id="input-container" class="w-full">
            <label for="studentId" class="text-gray-600 text-sm">Student ID <span class="text-red-500">*</span></label>
            <input type="text" name="student_id" id="studentId" placeholder="e.g., 00-0000" class="w-full border border-gray-300 rounded px-3 py-2 placeholder-gray-400 placeholder:font-light" required>
          </div>
          <div id="input-container" class="w-full">
            <label for="studentName" class="text-gray-600 text-sm">Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="full_name" id="studentName" placeholder="e.g., First name, Middle initials, Last name" class="w-full border border-gray-300 rounded px-3 py-2 placeholder-gray-400 placeholder:font-light" required>
          </div>
          <div id="input-container" class="w-full">
            <label for="studentEmail" class="text-gray-600 text-sm">Email Address <span class="text-red-500">*</span></label>
            <input type="text" name="email" id="studentEmail" placeholder="e.g., example@gmail.com" class="w-full border border-gray-300 rounded px-3 py-2 placeholder-gray-400 placeholder:font-light" required>
          </div>  
        </div>
      </div>

      <div class="mt-5">
        <h1 class="font-semibold mb-2" style="font-family: 'Roboto', sans-serif;">Academic Placement</h1>

        <div class="flex flex-row gap-5 items-center">
          <div id="input-wrapper" class="w-full">
            <label for="year_level" class="text-gray-600 text-sm"><?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?> <span class="text-red-500">*</span></label>
            <Select name="year" id="year_level" placeholder="" class="w-full border border-gray-300 rounded px-3 py-2" required>
              <?php if ($department === 'college') { ?>
                <option value="" disabled>Select Year Level</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              <?php } else {?>
                <option value="" disabled>Select Grade Level</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
              <?php } ?>
            </Select>
          </div>

          <div id="input-container" class="w-full">
            <label for="programSelect" class="text-gray-600 text-sm"><?php echo ($department === 'college') ? 'Course' : 'Program'; ?> <span class="text-red-500">*</span></label>
            <Select name="program" id="programSelect" placeholder="" class="w-full border border-gray-300 rounded px-3 py-2" required>
              <!-- JS fill this line -->
            </Select>
          </div>  
        </div>
      </div>
      
      <div class="flex justify-end gap-3 mt-4">
        <button type="button" data-close-modal="addStudentModal" class="px-4 py-2 bg-gray-300 rounded cursor-pointer" ">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded cursor-pointer">Add Student</button>
      </div>
    </form>

  </div>
</div>