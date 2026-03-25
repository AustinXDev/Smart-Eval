<h1 class="text-xl font-semibold"><span><?php echo strtoupper($department); ?></span> Dashboard</h1>

<!-- Card Container -->
<div id="card-container" class="px-2 mt-5 md:grid md:grid-cols-2 md:gap-5 overflow-y-hidden overflow-x-auto flex gap-4 lg:px-5 py-2">
  <div class="flex items-center gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#16213E] hover:text-white transition-all duration-300">
    <div id='icon' class="bg-[#16213E] p-2 rounded-md">
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
    <div id='total' class="text-2xl font-bold">
      500
    </div>
  </div>

  <div class="flex items-center gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#B76A18] hover:text-white transition-all duration-300">
    <div id='icon' class="bg-[#B76A18] p-2 rounded-md ">
      <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" stroke="" fill="#ffff"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/></svg>
    </div>
    <div id='card-name' class="flex-2 text-xl font-semibold">
      Total Teachers
    </div>
    <div id='total' class="text-2xl font-bold">
      80
    </div>
  </div>

  <div class="flex items-center gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-[#16213E] hover:text-white transition-all duration-300 group">
    <div id='icon' class="bg-[#16213E] p-2 rounded-md self-start">
      <div>
        <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="#ffff"><!--!Font Awesome Free v7.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2026 Fonticons, Inc.--><path d="M224 64C206.3 64 192 78.3 192 96L192 128L160 128C124.7 128 96 156.7 96 192L96 240L544 240L544 192C544 156.7 515.3 128 480 128L448 128L448 96C448 78.3 433.7 64 416 64C398.3 64 384 78.3 384 96L384 128L256 128L256 96C256 78.3 241.7 64 224 64zM96 288L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 288L96 288z"/></svg>
      </div>
    </div>
    <div id='card-content' class="flex flex-col">
      <div id="card-name" class="lg:text-xl font-semibold mb-2">Active Evaluation Period</div>
      <div class="flex flex-col gap-1">
        <h1 id="evaluation-title" class="text-md lg:text-2xl font-bold">2026-2027 - 1st Semester</h1>
        <span id="start-date" class="text-sm text-gray-500 group-hover:text-white">January 1, 2026</span>
        <span id="end-date" class="text-sm text-gray-500 group-hover:text-white">April 1,2026</span>
      </div>
    </div>
  </div>

  <div class="flex gap-3 px-6 py-5 rounded-sm flex-shrink-0 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-2 hover:bg-green-700 hover:text-white transition-all duration-300 group">
    <div id="icon" class="bg-green-700 p-2 rounded-md self-start">
      <svg class="w-10 h-10" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#ffff" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
      </svg>
    </div>
    <div class="flex-1">
      <h1 class="mb-2 lg:text-xl font-semibold">Evaluation Completion Rate</h1>
      <div id="progress-bar" class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
        <div class="bg-[#076A04] h-2 rounded-full w-0 transition-all duration-1000" style="width: 75%;"></div>
      </div>
      <div id="percentage" class="text-right mt-2">80% Completed</div>
    </div>
  </div>
</div>

<!-- Ranking + Overall Score Distribution --->
<div class="p-0 lg:p-5 min-h-screen mt-5">

  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    <div class="lg:col-span-1 bg-white rounded-lg [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] p-5 hover:-translate-y-2 transition-all duration-300 hover:bg-gray-100">
      <!-- Ranking Table -->

      <div class="mb-4"> <!-- Header -->
        <h1 class="text-md lg:text-lg">Teacher Performance Ranking</h1>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full min-w-[400px] text-sm text-left border-1 rounded">
          
          <colgroup>
            <col style="width: 10%;">
            <col style="width: 50%">
            <col style="width: 15%">
            <col style="width: 25%">
          </colgroup>

          <thead class="bg-[#16213E] text-white text-sm lg:text-md ">
            <tr>
              <th class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Rank</th>
              <th class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Name</th>
              <th class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Department</th>
              <th class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Score</th>
            </tr>
          </thead>

          <tbody class="divide-y">
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3 " style="font-family: roboto, 'sans-serif';">1</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Juan Dela Cruz</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">College</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">4.9</td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">2</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Maria Gonzales</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">College</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">4.5</td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">3</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Juan Tamad</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">SHS</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">4.2</td>
            </tr class="hover:bg-gray-50">
            <tr>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">4</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Anna Santos</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">College</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">4.1</td>
            </tr>
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">5</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">Joana San Miguel</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">SHS</td>
              <td class="px-4 py-3" style="font-family: roboto, 'sans-serif';">3.9</td>
            </tr>
          </tbody>
        </table>

        <div class="flex flex-col sm:flex-row mt-3 shadow-lg p-2 px-5 border rounded-md items-start sm:items-center gap-2">
            <div id="name" class="flex-1">
              <p class="font-semibold">Juan Dela Cruz</p>
              <p class="text-sm text-gray-500">(Highest rating)</p>
            </div>

            <div id="average" class="flex-1 flex justify-start sm:justify-end items-center gap-2">
              <p>Avg. Score:</p>
              <span id="score" class="text-green-900 font-semibold">4.9</span>
            </div>
        </div>
      </div>
    </div>


    <div class="bg-white rounded-lg p-5 pb-12 
            [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] 
            h-60 sm:h-64 md:h-80 hover:-translate-y-2 transition-all duration-300">

        <div class="mb-5">
          <h1 class="text-md lg:text-lg">Overall Score Distribution</h1>
        </div>
        <canvas id="scoreChart" class="w-full h-full"></canvas>
    </div>
  </div>

</div>

<?php
// Example: fetch data from database
$labels = ['5', '4', '3', '2','1'];
$values = ['90', '5', '2', '3', '0'];
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="../../public/assets/js/charts/chart-config.js"></script>

<script>
  const ctx = document.getElementById('scoreChart').getContext('2d');

  const labels = <?= json_encode($labels) ?>;
  const values = <?= json_encode($values) ?>;

  createScoreDoughnutChart(ctx, labels, values);
</script>