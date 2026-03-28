<div class="flex justify-between items-center">
  <h1 class="text-xl font-semibold"><span><?php echo strtoupper($department); ?></span> Students</h1>

  <div class="flex gap-2">
    <button value="" class="add-btn flex items-center cursor-pointer bg-[#16213E] py-2 px-3 rounded-md text-white text-sm lg:text-md hover:opacity-50 transition-all duration-200">
      <i class="fas fa-plus lg:mr-2"></i><span class="hidden md:block">Add Student</span>
    </button>

    <button value="" class="csv-btn flex items-center cursor-pointer bg-green-600 py-2 px-3 rounded-md text-white text-sm lg:text-md hover:opacity-50 transition-all duration-200">
      <i class="fas fa-file-csv lg:mr-2"></i><span class="hidden md:block">Import CSV</span>
    </button>
  </div>

</div>

<!-- Card info container -->
<div class="mt-7 lg:mt-2 lg:p-5 overflow-x-auto" data-department="<?php echo $department; ?>" id="card-container">

  <div class="flex gap-4 min-w-max">

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#16213E] px-5 rounded-md min-w-[500] 
            hover:-translate-y-2 hover:scale-105 hover:shadow-lg hover:bg-gradient-to-r hover:from-[#1A1A2E] hover:to-[#0D0D1A] 
            transition-all duration-300 cursor-pointer">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="white"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M32 256C32 220.7 60.7 192 96 192L160 192L287.9 76.9C306.2 60.5 333.9 60.5 352.1 76.9L480 192L544 192C579.3 192 608 220.7 608 256L608 512C608 547.3 579.3 576 544 576L96 576C60.7 576 32 547.3 32 512L32 256zM256 440L256 528L384 528L384 440C384 417.9 366.1 400 344 400L296 400C273.9 400 256 417.9 256 440zM144 448C152.8 448 160 440.8 160 432L160 400C160 391.2 152.8 384 144 384L112 384C103.2 384 96 391.2 96 400L96 432C96 440.8 103.2 448 112 448L144 448zM160 304L160 272C160 263.2 152.8 256 144 256L112 256C103.2 256 96 263.2 96 272L96 304C96 312.8 103.2 320 112 320L144 320C152.8 320 160 312.8 160 304zM528 448C536.8 448 544 440.8 544 432L544 400C544 391.2 536.8 384 528 384L496 384C487.2 384 480 391.2 480 400L480 432C480 440.8 487.2 448 496 448L528 448zM544 304L544 272C544 263.2 536.8 256 528 256L496 256C487.2 256 480 263.2 480 272L480 304C480 312.8 487.2 320 496 320L528 320C536.8 320 544 312.8 544 304zM320 320C355.3 320 384 291.3 384 256C384 220.7 355.3 192 320 192C284.7 192 256 220.7 256 256C256 291.3 284.7 320 320 320z"/></svg>
      </div>
      <div>
        <p class="text-white">Total Students</p>
        <p class="text-white lg:text-lg font-semibold" id="total-students"></p>
      </div>
    </div>

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#FF9F0D] px-5 rounded-md
            hover:-translate-y-2 hover:scale-105 hover:shadow-lg transition-all duration-300 cursor-pointer flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#FF9F0D] px-5 rounded-md
            hover:-translate-y-2 hover:scale-105 hover:shadow-lg transition-all duration-300 cursor-pointer">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="white"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M286 368C384.5 368 464.3 447.8 464.3 546.3C464.3 562.7 451 576 434.6 576L78 576C61.6 576 48.3 562.7 48.3 546.3C48.3 447.8 128.1 368 226.6 368L286 368zM585.7 169.9C593.5 159.2 608.5 156.8 619.2 164.6C629.9 172.4 632.3 187.4 624.5 198.1L522.1 338.9C517.9 344.6 511.4 348.3 504.4 348.7C497.4 349.1 490.4 346.5 485.5 341.4L439.1 293.4C429.9 283.9 430.1 268.7 439.7 259.5C449.2 250.3 464.4 250.6 473.6 260.1L500.1 287.5L585.7 169.8zM256.3 312C190 312 136.3 258.3 136.3 192C136.3 125.7 190 72 256.3 72C322.6 72 376.3 125.7 376.3 192C376.3 258.3 322.6 312 256.3 312z"/></svg>
      </div>
      <div>
        <p class="text-white">Active Students</p>
        <p class="text-white lg:text-lg font-semibold" id="total-active"></p>
      </div>
    </div>

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#E36565] p-5 rounded-md min-w-[500]
          hover:-translate-y-2 hover:scale-105 hover:shadow-lg transition-all duration-300 cursor-pointer">
      <div>
        <img class="w-10 h-10" src="../../public/assets/icons/database.png" alt="inactive.png" loading="lazy">
      </div>
      <div>
        <p class="text-white">Inactive Students</p>
        <p class="text-white lg:text-lg font-semibold" id="total-inactive"></p>
      </div>
    </div>

  </div>

</div>

<!-- Student List Table--->

<div class="p-5 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] rounded-md mt-7 ">

  <div id="tableWrapper" class="overflow-x-auto w-full" data-department="<?php echo htmlspecialchars($department); ?>">
    <table id="studentsTable" class="w-full text-left text-sm sm:text-base" >

      <div class="flex w-full flex-col gap-4 md:flex-row md:gap-8  mb-4  mt-2">
        <div class="flex items-center gap-2">
          <label class="text-gray-900 text-sm whitespace-nowrap">Filter Status:</label>
          <select id="statusFilter" class="border border-gray-200 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer min-w-30">
            <option value="All">All</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
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