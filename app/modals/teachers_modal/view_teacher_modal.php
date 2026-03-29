<!-- View Teacher Modal -->
<div id="viewDetails" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
  <div class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-3xl mx-2 overflow-hidden">

    <!-- Header -->
    <div class="bg-[#0F172A] px-6 py-4 flex items-center justify-between border-b-2 border-purple-500">
      <div>
        <h2 class="text-white text-lg font-semibold">Teacher Information</h2>
        <p class="text-gray-400 text-xs">View teacher profile details</p>
      </div>
      <button data-close-modal="viewDetails" class="text-white text-xl hover:text-red-400 transition">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <!-- Content -->
    <div class="p-6">

      <!-- Profile Section -->
      <div class="flex items-center gap-5 mb-6">

        <!-- Avatar -->
        <div class="relative w-24 h-24 flex items-center justify-center">
          <div class="absolute inset-0 rounded-full bg-purple-500 blur-xl opacity-30"></div>
          <div class="absolute inset-0 rounded-full p-[3px] bg-gradient-to-tr from-purple-500 via-indigo-500 to-purple-700">
            <div class="w-full h-full rounded-full bg-white"></div>
          </div>

          <!-- Image OR Initial -->
          <img src="../../public/uploads/teachers/default_teacher.png"
               class="relative w-20 h-20 rounded-full object-cover"
               alt="photo">
        </div>

        <!-- Info -->
        <div>
          <h1 id="name" class="text-2xl font-semibold text-gray-800">Juan Dela Cruz</h1>
          <p class="text-gray-500 text-sm">Employee ID: <span id="id">S10005</span></p>
          <p id="department" class="text-sm text-purple-600 font-medium"></p>
        </div>
      </div>

      <!-- Teacher Details Card -->
      <div class="bg-white rounded-lg shadow p-5 mb-6">
        <div class="flex items-center gap-2 mb-3">
          <i class="fas fa-user text-purple-600"></i>
          <h3 class="font-semibold text-gray-800">Teacher Details</h3>
        </div>

        <div class="bg-purple-600 text-white px-5 py-4 rounded-lg flex items-center gap-3">
          <i class="fas fa-envelope text-lg"></i>
          <div>
            <p class="text-sm opacity-80">Email</p>
            <p id="email" class="font-medium">example@gmail.com</p>
          </div>
        </div>
      </div>

      <!-- Handle Section -->
      <div class="bg-white rounded-lg shadow p-5">

        <div class="flex items-center gap-2 mb-4">
          <i class="fas fa-graduation-cap text-purple-600"></i>
          <h3 class="font-semibold text-gray-800">Handle Level & Program</h3>
        </div>

        <!-- Form -->
        <form id="addHandleForm" class="flex flex-col gap-3 mb-5" method="POST">
          <input type="hidden" name="teacher_id" id="handle_teacher_id">
          <input type="hidden" name="department" value="<?php echo htmlspecialchars($department); ?>">

          <div class="flex flex-col md:flex-row gap-3">

            <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none" name="level" required>
              <?php if ($department === 'college') {?>
                <option disabled selected>Select Year Level</option>
                <option value="1">1st Year</option>
                <option value="2">2nd Year</option>
                <option value="3">3rd Year</option>
                <option value="4">4th Year</option>
              <?php } else {?>
                <option disabled selected>Select Grade Level</option>
                <option value="11">Grade 11</option>
                <option value="12">Grade 12</option>
              <?php } ?>
            </select>

            <select class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none" name="program">
              <option disabled selected>Select Program</option>
              <option value="1">BSIT</option>
              <option value="2">BSOA</option>
              <option value="3">ABM</option>
            </select>

            <button class="flex items-center justify-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md shadow transition">
              <i class="fas fa-plus"></i>
              Add
            </button>

          </div>
        </form>

        <!-- Table -->
        <div id="handleTable" class="overflow-y-auto max-h-[220px] border border-gray-200 rounded-lg">

          <table class="min-w-full text-sm">
            <thead class="bg-gray-100 sticky top-0">
              <tr>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Level</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Program</th>
                <th class="text-left px-4 py-3 font-medium text-gray-600">Action</th>
              </tr>
            </thead>

            <tbody class="divide-y">
              <!-- JS Content -->
            </tbody>

          </table>
        </div>

      </div>

    </div>
  </div>
</div>