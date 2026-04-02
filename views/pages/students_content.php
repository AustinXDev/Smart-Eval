
<!-- Header --->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

  <h1 class="text-lg sm:text-xl font-semibold text-gray-800">
    <span class="text-[#16213E]"><?php echo strtoupper($department); ?></span> Students
  </h1>

  <div class="flex flex-wrap gap-2">

    <button class="add-btn flex items-center gap-2 bg-[#16213E] px-4 py-2 rounded-md text-white text-sm hover:opacity-90 transition">
      <i class="fas fa-plus"></i>
      <span>Add Student</span>
    </button>

    <button class="csv-btn flex items-center gap-2 bg-green-600 px-4 py-2 rounded-md text-white text-sm hover:opacity-90 transition">
      <i class="fas fa-file-csv"></i>
      <span>Import CSV</span>
    </button>

  </div>
</div>

<!-- Card info container -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">

  <!-- Total -->
  <div class="bg-[#16213E] text-white p-5 rounded-lg flex items-center gap-4 
              shadow-sm transform transition-all duration-300 ease-out
              hover:-translate-y-1 hover:scale-[1.03] hover:shadow-xl
              cursor-pointer">
    <i class="fas fa-users text-2xl transition-transform duration-300 group-hover:scale-110"></i>
    <div>
      <p class="text-sm opacity-80">Total Students</p>
      <p class="text-xl font-semibold" id="total-students"></p>
    </div>
  </div>

  <!-- Active -->
  <div class="bg-green-500 text-white p-5 rounded-lg flex items-center gap-4 shadow-sm transform 
              transition-all duration-300 ease-out
              hover:-translate-y-1 hover:scale-[1.03] hover:shadow-xl
              cursor-pointer">
    <i class="fas fa-user-check text-2xl transition-transform duration-300 group-hover:scale-110"></i>
    <div>
      <p class="text-sm opacity-80">Active Students</p>
      <p class="text-xl font-semibold" id="total-active"></p>
    </div>
  </div>

  <!-- Inactive -->
  <div class="bg-red-400 text-white p-5 rounded-lg flex items-center gap-4 shadow-sm transform 
              transition-all duration-300 ease-out
              hover:-translate-y-1 hover:scale-[1.03] hover:shadow-xl
              cursor-pointer">
    <i class="fas fa-user-times text-2xl"></i>
    <div>
      <p class="text-sm opacity-80">Inactive Students</p>
      <p class="text-xl font-semibold" id="total-inactive"></p>
    </div>
  </div>

</div>

<!-- Student List Table--->

<div class="p-5 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] rounded-md mt-7 ">

  <div id="tableWrapper" class="overflow-x-auto w-full" data-department="<?php echo htmlspecialchars($department); ?>">
    <table id="studentsTable" class="w-full text-left text-sm sm:text-base" >

      <div class="flex w-full flex-col gap-4 md:flex-row md:gap-8  mb-4  mt-2">
        <div class="flex items-center gap-2">
          <label class="text-gray-900 text-sm whitespace-nowrap"><?php echo ($department === 'college') ? 'Filter Course:': 'Filter Program:';?></label>
          <select id="courseFilter" class="border border-gray-200 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer min-w-30">
            <option value="All">All </option>
          </select>
        </div>
      </div>

      <thead class="bg-gray-50 border-b-2 border-gray-200">
        <tr>
          <th class="px-4 py-2 font-normal w-20 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Student ID</th>
          <th class="px-4 py-2 font-normal w-80 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Student Name</th>
          <th class="px-4 py-2 font-normal w-30 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Department</th>
          <th class="px-4 py-2 font-normal w-30 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Course</th>
          <th class="px-4 py-2 font-normal w-25 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Status</th>
          <th class="px-4 py-2 font-normal w-40 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Actions</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <!-- LoadData Fucntion fills this section -->
      </tbody>

    </table>
  </div>

</div>