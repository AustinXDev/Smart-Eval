<h1 class="text-xl font-semibold"><span><?php echo strtoupper($department); ?></span> Dashboard</h1>

<!-- Card Container -->
<div id="card-container" class="px-2 mt-5 md:grid md:grid-cols-2 md:gap-5 overflow-y-hidden overflow-x-auto flex gap-4 lg:px-5 py-2">
  <div class="group flex items-center gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#16213E] hover:text-white transition-all duration-300">
    <div id='icon' class="bg-[#16213E] group-hover:bg-gradient-to-br group-hover:from-blue-500 group-hover:to-indigo-500 p-2 rounded-md transition-colors duration-300">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffff" class="size-6">
          <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd" />
          <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z" />
        </svg>

      </div>
    </div>
    <div id='card-name' class="flex-2 text-xl font-semibold">
      Total Students
    </div>
    <div id='totalStudents' class="text-2xl font-bold">
      <!-- JS fill this line -->
    </div>
  </div>

  <div class="group flex items-center gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#B76A18] hover:text-white transition-all duration-300">
    <div id='icon' class="bg-[#B76A18] group-hover:bg-gradient-to-br group-hover:from-orange-500 group-hover:to-red-600 p-2 rounded-md transition-colors duration-300">
      <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" stroke="" fill="#ffff"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/></svg>
    </div>
    <div id='card-name' class="flex-2 text-xl font-semibold">
      Total Teachers
    </div>
    <div id='totalTeachers' class="text-2xl font-bold">
      <!-- JS fill this line -->
    </div>
  </div>

  <div class="group flex items-center gap-6 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#16213E] hover:text-white transition-all duration-300 group">
    <div id='icon' class="bg-[#16213E] group-hover:bg-gradient-to-br group-hover:from-blue-500 group-hover:to-indigo-500 p-2 rounded-md transition-colors duration-300">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#ffff"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M224 64C206.3 64 192 78.3 192 96L192 128L160 128C124.7 128 96 156.7 96 192L96 240L544 240L544 192C544 156.7 515.3 128 480 128L448 128L448 96C448 78.3 433.7 64 416 64C398.3 64 384 78.3 384 96L384 128L256 128L256 96C256 78.3 241.7 64 224 64zM96 288L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 288L96 288z"/></svg>
      </div>
    </div>
    <div id='card-content' class="flex flex-col">
      <div id="card-name" class="lg:text-xl font-semibold mb-2"></div>
      <div class="flex flex-col gap-1">
        <div class="text-md lg:text-2xl font-bold">
          <span id="academic_year"></span> -
          <span id="semester"></span>
        </div>
        <div class="flex gap-4">
          <div>
            <span class="text-green-400">Start Date: </span><span id="start-date" class="text-sm text-gray-500 group-hover:text-white"><!-- JS fill this line --></span>
          </div>
          <div>
            <span class="text-red-400">End Date: </span><span id="end-date" class="text-sm text-gray-500 group-hover:text-white"><!-- JS fill this line --></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="group flex gap-3 px-6 py-5 rounded-sm flex-shrink-0 
            [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] 
            hover:-translate-y-2 hover:bg-green-700 hover:text-white transition-all duration-300">

    <!-- Icon -->
    <div id="icon" class="bg-green-700 group-hover:bg-gradient-to-br group-hover:from-green-500 group-hover:to-green-800 
                          p-2 rounded-md transition-all duration-300 self-start flex items-center justify-center">
      <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffff" viewBox="0 0 24 24" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
      </svg>
    </div>

    <!-- Card content -->
    <div class="flex-1">
      <h1 class="mb-2 lg:text-xl font-semibold">Evaluation Completion Rate</h1>
      <div id="progress-bar" class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div id="progress-fill" class="bg-[#076A04] h-2 rounded-full w-0 transition-all duration-1000"></div>
      </div>
      <div id="percentage" class="text-right mt-2"></div>
    </div>
  </div>
</div>

<!-- Ranking + Overall Score Distribution --->
<div class="p-0 lg:p-5 mt-5">

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="lg:col-span-2 bg-white rounded-lg [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] p-5 hover:-translate-y-2 transition-all duration-300 hover:bg-gray-100  ">
      <!-- Ranking Table -->

      <div class="mb-4"> <!-- Header -->
        <h1 class="text-md lg:text-lg">Teacher Performance Ranking</h1>
      </div>

      <div class="overflow-x-auto">
        <div class="w-full overflow-x-auto rounded-xl border border-gray-200">
          <table class="w-full min-w-[400px] text-sm text-left">

          <colgroup>
            <col style="width: 10%;">
            <col style="width: 40%">
            <col style="width: 15%">
            <col style="width: 15%">
            <col style="width: 15%">
          </colgroup>

          <thead>
            <tr class="bg-[#16213E] text-white">
              <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">Rank</th>
              <th class="px-4 py-3 font-medium tracking-wide whitespace-nowrap">Name</th>
              <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">Evaluators</th>
              <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">Evaluated</th>
              <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">Score</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100" id="tbody-ranking">
            <!-- JS fill this line  -->
          </tbody>

          </table>
        </div>

        <div class="py-4">
          <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-wrap items-center gap-3">

          <div id="top_initials" class="w-11 h-11 rounded-full bg-purple-100 flex items-center justify-center text-sm font-medium text-purple-800 shrink-0">
            JD
          </div>

          <div class="flex-1 min-w-[120px]">
            <p class="m-0 text-[15px] font-medium text-gray-900" id="highest-teacher-name">Juan Dela Cruz</p>
            <span class="inline-block mt-1 text-[11px] font-medium bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
              Highest rating
            </span>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <span class="text-[13px] text-gray-500">Avg. score</span>
            <div class="flex items-center gap-1 bg-green-100 rounded-lg px-2.5 py-1">
              <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.3l-3.7 2 .7-4.1-3-2.9 4.2-.7z" fill="#166534"/></svg>
              <span id="avg-score" class="text-[15px] font-medium text-green-900">4.9</span>
            </div>
          </div>

          </div>
        </div>
      </div>
    </div>

    <!-- Doughnut Chart -->
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm hover:-translate-y-1 transition-all duration-300">

      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-sm font-semibold text-gray-800 tracking-wide">Score Distribution</h1>
          <p class="text-xs text-gray-400 mt-0.5">Total students per rating</p>
        </div>
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-[#16213E]"></span>
          <span class="text-xs text-gray-400">Responses</span>
        </div>
      </div>

      <div class="w-full h-px bg-gray-100 mb-4"></div>

      <!-- Rating legend pills -->
      <div class="flex items-center gap-2 mb-4 flex-wrap">
        <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">5 — Excellent</span>
        <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">4 — Good</span>
        <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-0.5 rounded-full">3 — Average</span>
        <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 text-xs font-medium px-2 py-0.5 rounded-full">2 — Poor</span>
        <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 text-xs font-medium px-2 py-0.5 rounded-full">1 — Very Poor</span>
      </div>

      <div class="relative w-full h-64">
        <canvas id="scoreChart"></canvas>
      </div>

    </div>

    <!-- PIE Chart -->
    <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm hover:-translate-y-1 transition-all duration-300">

      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-sm font-semibold text-gray-800 tracking-wide">Evaluation Participation</h1>
          <p class="text-xs text-gray-400 mt-0.5">Completed vs pending per department</p>
        </div>
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-[#16213E]"></span>
            <span class="text-xs text-gray-400">Completed</span>
          </div>
          <div class="flex items-center gap-1.5">
            <span class="w-2 h-2 rounded-full bg-gray-200"></span>
            <span class="text-xs text-gray-400">Pending</span>
          </div>
        </div>
      </div>

      <div class="w-full h-px bg-gray-100 mb-4"></div>

      <div class="flex items-center gap-2 mb-4 flex-wrap">
        <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">Completed</span>
        <span class="inline-flex items-center gap-1 bg-gray-50 text-[#f87171] text-xs font-medium px-2 py-0.5 rounded-full">Pending</span>
      </div>

      <div class="relative w-full h-64">
        <canvas id="participationChart"></canvas>
      </div>

    </div>

    <!-- Bar Chart -->
    <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-gray-100 shadow-sm hover:-translate-y-1 transition-all duration-300">

      <div class="flex items-center justify-between mb-3">
        <div>
          <h1 class="text-sm font-semibold text-gray-800 tracking-wide">
            Total Evaluated per Program
          </h1>

          <p class="text-xs text-gray-400 mt-0.5">
            Number of students who completed evaluations by program
          </p>
        </div>
      </div>

      <div class="w-full h-px bg-gray-100 mb-4"></div>

      <div class="relative w-full h-64">
        <canvas id="programChart"></canvas>
      </div>

    </div>
  </div>
</div>

  <!-- Student Participation Card Container-->
  <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 
            [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] 
            rounded-sm mt-7">
    <div class="flex flex-col gap-5 border-r-1 border-gray-200">
      <div class="flex items-center gap-3 border-b-1 border-gray-200 pb-3">
        <div class="bg-[#1A1A2E] p-3 rounded-sm">
          <svg class="w-10 h-10" data-slot="icon" fill="none" stroke-width="1.5" stroke="#ffff" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"></path>
          </svg>
        </div>
        <p>Students Who Evaluated</p>
      </div>
      <div class="flex pr-5 items-center">
        <div class="flex-1">
          <span id="total" class="text-lg font-semibold lg:text-xl">200</span>
          <span id="label">Total</span>
        </div>
        <span id="arrow">
          <svg class="w-7 h-7" data-slot="icon" fill="none" stroke-width="1.5" stroke="green" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"></path>
          </svg>
        </span>
      </div>
    </div>

    <div class="flex flex-col gap-5 border-r-1 border-gray-200">
      <div class="flex items-center gap-3 border-b-1 border-gray-200 pb-3">
        <div class="bg-[#1A1A2E] p-3 rounded-sm">
          <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" class="size-6">
          <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.625 6c-.54 0-.828.419-.936.634a1.96 1.96 0 0 0-.189.866c0 .298.059.605.189.866.108.215.395.634.936.634.54 0 .828-.419.936-.634.13-.26.189-.568.189-.866 0-.298-.059-.605-.189-.866-.108-.215-.395-.634-.936-.634Zm4.314.634c.108-.215.395-.634.936-.634.54 0 .828.419.936.634.13.26.189.568.189.866 0 .298-.059.605-.189.866-.108.215-.395.634-.936.634-.54 0-.828-.419-.936-.634a1.96 1.96 0 0 1-.189-.866c0-.298.059-.605.189-.866Zm-4.34 7.964a.75.75 0 0 1-1.061-1.06 5.236 5.236 0 0 1 3.73-1.538 5.236 5.236 0 0 1 3.695 1.538.75.75 0 1 1-1.061 1.06 3.736 3.736 0 0 0-2.639-1.098 3.736 3.736 0 0 0-2.664 1.098Z" clip-rule="evenodd" />
        </svg>
      </div>
        <p>Students Who Evaluated</p>
    </div>
    <div class="flex pr-5 items-center">
      <div class="flex-1">
        <span id="total" class="text-lg font-semibold lg:text-xl">200</span>
        <span id="label">Total</span>
      </div>
      <span id="arrow">
        <svg class="w-7 h-7" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="red" class="size-6">
          <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v16.19l6.22-6.22a.75.75 0 1 1 1.06 1.06l-7.5 7.5a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 1 1 1.06-1.06l6.22 6.22V3a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd" />
        </svg>
      </span>
    </div>
  </div>

  <div class="flex flex-col gap-5 border-r-1 border-gray-200">
    <div class="flex items-center gap-3 border-b-1 border-gray-200 pb-3">
      <div class="bg-[#1A1A2E] p-3 rounded-sm">
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffff" class="size-6">
          <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd" />
          <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd" />
        </svg>
      </div>
      <p>Students Who Evaluated</p>
    </div>
    <div class="flex pr-5 items-center">
      <div class="flex-1">
        <span id="total" class="text-lg font-semibold lg:text-xl">200</span>
        <span id="label">Total</span>
      </div>
      <span id="arrow">
        <svg class="w-7 h-7" data-slot="icon" fill="none" stroke-width="1.5" stroke="green" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"></path>
        </svg>
      </span>
    </div>
  </div>

</div>