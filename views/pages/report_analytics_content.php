<!-- Header -->
<div class="pb-4 border-b border-gray-200">
  <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

    <div class="flex flex-col gap-2.5">

      <!-- Breadcrumb -->
      <div class="flex items-center gap-2 text-sm text-gray-500 flex-wrap">
        <span>Report & Analytics</span>
        <span>/</span>
        <span class="font-medium text-gray-800 bg-gray-100 border border-gray-200 rounded-md px-2.5 py-0.5">
          <?php echo isset($_GET['dept']) ? ucfirst($_GET['dept']) : 'Department'; ?>
        </span>
      </div>

      <!-- Period + status -->
      <div class="flex items-start gap-2 flex-wrap text-sm text-gray-500">
        <div class="flex items-center gap-1.5 shrink-0">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1.2">
            <rect x="1" y="3" width="14" height="12" rx="2"/><path d="M1 7h14M5 1v3M11 1v3" stroke-linecap="round"/>
          </svg>
          <span class="shrink-0">Current view: </span>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
          <span id="evaluationPeriod" class="font-medium text-gray-800 bg-gray-100 border border-gray-200 rounded-md px-2.5 py-0.5 text-xs sm:text-sm">
            Evaluation Period
          </span>

          <span class="text-gray-300">/</span>

          <span id="semester" class="font-medium text-gray-800 bg-gray-100 border border-gray-200 rounded-md px-2.5 py-0.5 text-xs sm:text-sm">
            Semester
          </span>

          <!-- Live badge -->
          <div id="status" class="flex items-center gap-1.5 text-xs font-medium text-green-700 bg-green-50 border border-green-300 rounded-md px-2.5 py-0.5">
            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
            Live
          </div>
      </div>

  </div>

</div>

    <!-- Right: actions — full width on mobile, auto on sm+ -->
    <div class="flex items-center gap-2 sm:mt-0.5">

      <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-sm font-medium px-3.5 py-2 sm:py-1.5 rounded-lg border border-indigo-200 bg-white text-indigo-700 hover:bg-indigo-50 hover:border-indigo-300 active:bg-indigo-100 transition-colors cursor-pointer"
        id="viewHistoryBtn">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="1" width="10" height="14" rx="1.5"/>
          <circle cx="6" cy="5.5" r="0.5" fill="currentColor" stroke="none"/>
          <circle cx="6" cy="8.5" r="0.5" fill="currentColor" stroke="none"/>
          <circle cx="6" cy="11.5" r="0.5" fill="currentColor" stroke="none"/>
          <line x1="9" y1="5.5" x2="11" y2="5.5"/>
          <line x1="9" y1="8.5" x2="11" y2="8.5"/>
          <line x1="9" y1="11.5" x2="11" y2="11.5"/>
        </svg>
        <span class="truncate">View history</span>
      </button>

      <button class="flex-1 sm:flex-none flex items-center justify-center gap-1.5 text-sm font-medium px-3.5 py-2 sm:py-1.5 rounded-lg border border-indigo-700 bg-indigo-600 text-white hover:bg-indigo-700 hover:border-indigo-800 active:bg-indigo-800 transition-colors cursor-pointer"
        id="exportPdfBtn">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 10v3a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1v-3M8 2v8M5 7l3 3 3-3"/>
        </svg>
        <span class="truncate">Export to PDF</span>
      </button>

    </div>

  </div>
</div>

<!-- Content -->
<div class="grid grid-cols-1 lg:grid-cols-2">

  <!-- Participation funnel -->
  <div class="section-card my-5 col-span-2">

    <div class="section-header flex items-center justify-between">
      <div class="flex flex-col gap-1">
        <p class="section-title text-sm font-semibold text-gray-800 tracking-wide">Participation Funnel</p>
        <p class="section-subtitle text-xs text-gray-400 mt-0.5">Drop-off analysis across evaluation stages</p>
      </div>
      <span class="badge" style="background:#EEEDFE;color:#3C3489;">Funnel</span>

    </div>

    <div class="w-full h-px bg-gray-300 mb-4 mt-4"></div>

    <!-- Funnerl Bars -->
    <div id="funnel-container" style="max-width: 1000px; margin: 0 auto; padding: 8px 0;">

      <div class="funnel-row">
        <span class="funnel-label">Total Students</span>
        <div class="funnel-track">
          <div class="funnel-fill" id="funnel-fill-TotalEnrolled" style="width:0%;background:#3C3489;"><span id="totalEnrolled" class="mr-1"></span> enrolled</div>
        </div>
        <span class="funnel-count" id="totalStudents"></span>
      </div>
  
      <div class="funnel-row">
        <span class="funnel-label" style="color:#D85A30;">Unresponsive</span>
        <div class="funnel-track">
          <div class="funnel-fill whitespace-nowrap" id="funnel-fill-Unresponsive" style="width:0%;background:#F0997B;">Never started</div>
        </div>
        <span class="funnel-count" style="color:#D85A30;" id="totalNeverStarted"></span>
      </div>
  
      <div class="funnel-row">
        <span class="funnel-label" style="color:#BA7517;">In-Progress</span>
        <div class="funnel-track">
          <div class="funnel-fill" id="funnel-fill-InProgress" style="width:0%;background:#EF9F27;">Abandoned</div>
        </div>
        <span class="funnel-count" style="color:#BA7517;" id="totalAbandoned"></span>
      </div>
 
      <div class="funnel-row" style="margin-bottom:0;">
        <span class="funnel-label" style="color:#3B6D11;">Completed</span>
        <div class="funnel-track">
          <div class="funnel-fill whitespace-nowrap" id="funnel-fill-Completed" style="width:0%;background:#639922;">Fully submitted</div>
        </div>
        <span class="funnel-count" style="color:#3B6D11;" id="totalCompleted"></span>
      </div>

    </div>

    <div class="w-full h-px bg-gray-300 mb-4 mt-4"></div>

    <!-- cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">

      <div style="background:#EEEDFE;border-radius:10px;padding:12px 14px;">
      <p class="label-xs" style="color:#534AB7;">Completion Rate</p>
      <p style="font-size:22px;font-weight:700;color:#3C3489;margin-top:4px;" id="completionRate"></p>
      </div>

      <div style="background:#FAEEDA;border-radius:10px;padding:12px 14px;">
        <p class="label-xs" style="color:#854F0B;">Abandoned</p>
        <p style="font-size:22px;font-weight:700;color:#633806;margin-top:4px;" id="abandonedRate"></p>
      </div>

      <div style="background:#FAECE7;border-radius:10px;padding:12px 14px;">
        <p class="label-xs" style="color:#993C1D;">Never Started</p>
        <p style="font-size:22px;font-weight:700;color:#71271300;color:#712713;color:#993C1D;margin-top:4px;" id="neverStartedRate"></p>
      </div>

    </div>

  </div>

  <!-- Live Chart for period trend -->
  <div class="section-card col-span-2">

    <!-- Section header -->
    <div class="section-header flex items-center justify-between">
      <div class="flex flex-col gap-1">
        <p class="section-title text-sm font-semibold text-gray-800 tracking-wide">Longitudinal Trend</p>
        <p class="section-subtitle text-xs text-gray-400 mt-0.5">Mean score over 5 semester</p>
      </div>
      <span class="badge" style="background:#E6F1FB;color:#185FA5;">Trend</span>

    </div>

    <div class="w-full h-px bg-gray-300 mb-4 mt-4"></div>

    <div style="display:flex;gap:14px;margin-bottom:14px;">
      <div style="display:flex;align-items:center;gap:6px;"><span class="dot" style="background:#534AB7;"></span><span style="font-size:11px;color:#6B6860;">Mean Score</span></div>
    </div>

    <div id="trendChart" style="width:100%;height:300px; border: 1px solid #e8e4dc;">
      <canvas id="trendChartCanvas" style="display:block; width:100%; height:100%;"></canvas>
    </div>

    <div class="flex gap-2 mt-2.5">

      <div id="meanParentContainer" class="flex-1 flex flex-col justify-between rounded-xl p-3" style="background:#EDFAF1;">
        <p class="mean-title label-xs" style="color:#334155;">Mean Score</p>
        <div class="flex items-center gap-1.5">
          <p id="meanScore" class="text-lg font-bold mt-1" style="color:#3C3489;"> </p>
          <sub class="adjectiveRating"></sub>
        </div>
        <p class="mean-sublabel text-xs mt-1" style="color:#64748B;">Overall Rating</p> 
      </div>

      <div class="flex-1 flex flex-col justify-between rounded-xl p-3" style="background:#EEEDFE;">
        <p class="label-xs" style="color:#534AB7;">Score Trend</p>
        <p id="trendGrowth" class="text-lg font-bold mt-1" style="color:#3C3489;"></p>
        <p class="text-xs mt-1" style="color:#7F77DD;">vs. last semester</p>
      </div>

    </div>

  </div>

  <!-- Participation Chart -->
  <div class="section-card col-span-2 mt-6">

    <div class="section-header flex items-center justify-between">
      <div>
        <p class="section-title text-sm font-semibold text-gray-800 tracking-wide">Year-Level Participation</p>
        <p class="section-subtitle text-xs text-gray-400 mt-0.5">Evaluation completion rate broken down by student year level</p>
      </div>
      <span class="badge whitespace-nowrap" style="background:#FAEEDA;color:#854F0B;">Grouped Bar</span>
    </div>

    <div class="w-full h-px bg-gray-300 mb-4 mt-4"></div>

    <div id="participationContainer" style="width:100%;height:300px; border: 1px solid #e8e4dc;">
      <canvas id="participationChart" style="display:block; width:100%; height:100%;"></canvas>
    </div>

  </div>

  <!-- Categorical Radar Chart -->

  <div class="section-card col-span-1 mt-6">

    <div class="flex items-center justify-between mb-3">
      <div>
        <h1 class="text-sm font-semibold text-gray-800 tracking-wide">Performance by Category</h1>
        <p class="text-xs text-gray-400 mt-0.5">Score per evaluation criterion</p>
      </div>
      <div class="flex items-center gap-1.5">
        <span class="w-2 h-2 rounded-full bg-[#534AB7]"></span>
        <span class="text-xs text-gray-400">Score</span>
      </div>
    </div>

    <div class="w-full h-px bg-gray-100 mb-4"></div>

    <div class="relative w-full h-72">
      <canvas id="radarChart"></canvas>
    </div>

  </div>

</div>
