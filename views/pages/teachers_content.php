<div class="flex justify-between items-center">
  <h1 class="text-xl font-semibold"><span><?php echo strtoupper($department); ?></span> Teachers</h1>
  <button value="" class="add-btn cursor-pointer bg-[#16213E] py-2 px-3 rounded-md text-white text-sm lg:text-md">
    <span><i class="fas fa-plus"></i> Add Teacher</span>
  </button>
</div>

<!-- Card info container -->
<div class="mt-2 p-5 overflow-x-auto">

  <div class="flex gap-4 min-w-max">

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#16213E] px-5 rounded-md min-w-[500] hover:-translate-y-2 transition-all duration-300">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#ffff"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/></svg>
      </div>
      <div>
        <p class="text-white">Total Teachers</p>
        <p class="text-white lg:text-lg font-semibold">500</p>
      </div>
    </div>

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#FF9F0D] px-5 rounded-md hover:-translate-y-2 transition-all duration-300">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="white"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M286 368C384.5 368 464.3 447.8 464.3 546.3C464.3 562.7 451 576 434.6 576L78 576C61.6 576 48.3 562.7 48.3 546.3C48.3 447.8 128.1 368 226.6 368L286 368zM585.7 169.9C593.5 159.2 608.5 156.8 619.2 164.6C629.9 172.4 632.3 187.4 624.5 198.1L522.1 338.9C517.9 344.6 511.4 348.3 504.4 348.7C497.4 349.1 490.4 346.5 485.5 341.4L439.1 293.4C429.9 283.9 430.1 268.7 439.7 259.5C449.2 250.3 464.4 250.6 473.6 260.1L500.1 287.5L585.7 169.8zM256.3 312C190 312 136.3 258.3 136.3 192C136.3 125.7 190 72 256.3 72C322.6 72 376.3 125.7 376.3 192C376.3 258.3 322.6 312 256.3 312z"/></svg>
      </div>
      <div>
        <p class="text-white">Active Teachers</p>
        <p class="text-white lg:text-lg font-semibold">300</p>
      </div>
    </div>

    <div class="flex flex-shrink-0 gap-4 w-72 sm:w-80 lg:flex-1 items-center bg-[#E36565] p-5 rounded-md min-w-[500] hover:-translate-y-2 transition-all duration-300">
      <div>
        <img class="w-10 h-10" src="../../public/assets/icons/database.png" alt="inactive.png" loading="lazy">
      </div>
      <div>
        <p class="text-white">Inactive Teachers</p>
        <p class="text-white lg:text-lg font-semibold">200</p>
      </div>
    </div>

  </div>

</div>

<!-- Teacher List Table--->

<div class="p-5 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] rounded-md mt-7 ">

  <div id="tableWrapper" class="overflow-x-auto w-full" data-department="<?php echo htmlspecialchars($department); ?>">
    <table id="teachersTable" class="min-w-full text-left text-sm sm:text-base" >

      <div class="flex flex-col w-full sm:flex-row sm:justify-between items-center mb-4 gap-2">
        <div class="flex items-center gap-2">
          <label class="text-gray-700 text-sm font-medium">Filter Status:</label>
          <select id="statusFilter" class="border rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer">
            <option value="All">All</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
          </select>
        </div>
      </div>

      <thead class="bg-gray-200">
        <tr>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Photo</th>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Teacher ID</th>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Teacher Name</th>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Department</th>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Status</th>
          <th class="px-4 py-2 font-normal" style="font-family: roboto, 'sans-serif';">Actions</th>
        </tr>
      </thead>

      <tbody>
        <!-- LoadData Fucntion fills this section -->
      </tbody>

    </table>
  </div>

</div>