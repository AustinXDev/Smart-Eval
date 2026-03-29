<div class="p-4 lg:p-6 space-y-6">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <h1 class="text-xl lg:text-2xl font-semibold text-gray-800">
      Evaluation Periods
    </h1>

    <button
      class="createPeriodBtn bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md shadow hover:opacity-90 transition">
      + Create New Period
    </button>
  </div>

  <!-- SUMMARY CARDS -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

  <!-- COLLEGE CARD -->
  <div class="bg-[#1A1F36] text-white rounded-lg p-5 shadow hover:scale-[1.02] transition space-y-3">

    <div class="flex justify-between items-center">
        <div>
          <p class="text-sm opacity-70">College Active Period</p>
          <h2 id="collegeYear" class="text-lg font-bold">--</h2>
          <p id="collegeSem" class="text-sm opacity-70">--</p>
        </div>

        <span id="collegeStatus"
          class="bg-gray-500 px-3 py-1 rounded-full text-xs flex items-center gap-2">
          <span class="w-2 h-2 bg-white rounded-full"></span>
          No Active
        </span>
    </div>

      <!-- COLLEGE PROGRESS -->
    <div>
      <p id="collegeProgressText" class="text-sm mb-1">0% Completed</p>

      <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
        <div id="collegeProgressBar"
          class="bg-green-400 h-full rounded-full transition-all duration-500"
          style="width:0%">
        </div>
      </div>

      <p id="collegeProgressCount" class="text-xs mt-1 opacity-80">
        0 / 0 Students
      </p>
    </div>

  </div>

  <!-- SHS CARD -->
  <div class="bg-[#0F3460] text-white rounded-lg p-5 shadow hover:scale-[1.02] transition space-y-3">

    <div class="flex justify-between items-center">
      <div>
        <p class="text-sm opacity-70">SHS Active Period</p>
        <h2 id="shsYear" class="text-lg font-bold">--</h2>
        <p id="shsSem" class="text-sm opacity-70">--</p>
      </div>

      <span id="shsStatus"
        class="bg-gray-500 px-3 py-1 rounded-full text-xs flex items-center gap-2">
        <span class="w-2 h-2 bg-white rounded-full"></span>
        No Active
      </span>
    </div>

    <!-- SHS PROGRESS -->
    <div>
      <p id="shsProgressText" class="text-sm mb-1">0% Completed</p>

      <div class="w-full bg-white/20 rounded-full h-2 overflow-hidden">
        <div id="shsProgressBar"
          class="bg-yellow-400 h-full rounded-full transition-all duration-500"
          style="width:0%">
        </div>
      </div>

      <p id="shsProgressCount" class="text-xs mt-1 opacity-80">
        0 / 0 Students
      </p>
    </div>

  </div>

</div>

  <!-- TABLE -->
  <div class="bg-white rounded-lg shadow p-4">

    <!-- FILTER -->
    <div class="flex flex-col md:flex-row justify-between gap-3 mb-4">

      <div class="flex items-center gap-2">
        <label for="statusFilter">Filter Status: </label>
        <select id="statusFilter"
          class="border rounded px-3 py-1 text-sm focus:ring focus:ring-purple-200">
          <option value="All">Show All</option>
          <option value="Active">Active</option>
          <option value="Archived">Archived</option>
          <option value="Upcoming">Upcoming</option>
        </select>
      </div>

    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
      <table id="evaluationTable" class="w-full text-sm text-left">

        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2">Academic Year</th>
            <th class="px-4 py-2">Semester</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Restriction</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>

        <tbody id="evaluationTableBody" class="divide-y">
          <!-- JS will render rows here -->
        </tbody>

      </table>
    </div>

  </div>

</div>