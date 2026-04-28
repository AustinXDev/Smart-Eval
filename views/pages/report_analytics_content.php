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
<div class="grid grid-cols-1 lg:grid-cols-2 gap-1.5">

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

  <!-- Radar + Question Gap — 1 col mobile, 2 col desktop -->
  <div class="col-span-2">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6 w-full">

      <!-- Categorical Radar Chart -->
      <div class="section-card h-full w-full flex flex-col">

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

        <div id="categoryContainer" style="width:100%;height:300px;" class="flex justify-center items-center">
          <canvas id="radarChartCanvas" style="display:block; width:100%; height:100%;"></canvas>
        </div>

        <div class="grid grid-cols-2 gap-3 mt-4">

          <div class="rounded-xl p-3 border border-green-100" style="background:#ECFDF5;">
            <div class="flex items-center gap-1.5 mb-2">
              <div class="w-5 h-5 rounded-full bg-green-100 border border-green-200 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M8 12V4M4 8l4-4 4 4"/>
                </svg>
              </div>
              <p class="text-xs font-medium text-green-700">Highest Category</p>
            </div>
            <p id="highestCategory" class="text-sm font-semibold text-green-900 truncate">—</p>
            <div class="flex items-center justify-between mt-1.5">
              <p id="highestScore" class="text-xl font-bold" style="color:#065F46;">—</p>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-green-100 text-green-700">/ 5.00</span>
            </div>
          </div>

          <div class="rounded-xl p-3 border border-red-100" style="background:#FEF2F2;">
            <div class="flex items-center gap-1.5 mb-2">
              <div class="w-5 h-5 rounded-full bg-red-100 border border-red-200 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-red-500" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M8 4v8M4 8l4 4 4-4"/>
                </svg>
              </div>
              <p class="text-xs font-medium text-red-600">Lowest Category</p>
            </div>
            <p id="lowestCategory" class="text-sm font-semibold text-red-900 truncate">—</p>
            <div class="flex items-center justify-between mt-1.5">
              <p id="lowestScore" class="text-xl font-bold" style="color:#991B1B;">—</p>
              <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-red-100 text-red-600">/ 5.00</span>
            </div>
          </div>

        </div>
      </div>

      <!-- Question Gap -->
      <div class="section-card h-full w-full flex flex-col">
        <div class="flex items-center justify-between mb-3">
          <div>
            <h1 class="text-sm font-semibold text-gray-800 tracking-wide">Question Gap Analysis</h1>
            <p class="text-xs text-gray-400 mt-0.5">highest vs. lowest rated evaluation questions</p>
          </div>
          <span class="badge whitespace-nowrap" style="background:#FCEBEB;color:#A32D2D;">Talking Points</span>
        </div>

        <div class="w-full h-px bg-gray-100 mb-4"></div>

        <div id="parentContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-4 w-full h-full">
          <!-- Highest Question -->
          <div>
            <div class="flex items-center gap-2 mb-3">
              <div class="w-5 h-5 rounded-full bg-green-100 border border-green-200 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-green-600" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <path d="M8 12V4M4 8l4-4 4 4"/>
                </svg>
              </div>
              <p class="text-xs font-semibold text-green-700 uppercase tracking-wide">Strongest Questions</p>
            </div>

            <div id="highestQuestions" class="flex flex-col gap-2">

              <!-- Repeated per question — populated by JS -->
              <div class="flex items-start gap-2.5 p-2.5 rounded-lg border border-green-100" style="background:#F0FDF4;">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-500 text-white text-xs font-bold flex items-center justify-center mt-0.5">1</span>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">The teacher explains lessons clearly and understandably.</p>
                  <div class="flex items-center gap-2 mt-1.5">
                    <div class="flex-1 bg-green-100 rounded-full h-1.5 overflow-hidden">
                      <div class="h-full rounded-full bg-green-500" style="width:92%;"></div>
                    </div>
                    <span class="text-xs font-bold text-green-700 flex-shrink-0">4.60</span>
                  </div>
                </div>
              </div>

              <div class="flex items-start gap-2.5 p-2.5 rounded-lg border border-green-100" style="background:#F0FDF4;">
                <span class="flex-shrink-0 w-5 h-5 rounded-full bg-green-400 text-white text-xs font-bold flex items-center justify-center mt-0.5">2</span>
                <div class="flex-1 min-w-0">
                  <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">The teacher encourages student participation in class.</p>
                  <div class="flex items-center gap-2 mt-1.5">
                    <div class="flex-1 bg-green-100 rounded-full h-1.5 overflow-hidden">
                      <div class="h-full rounded-full bg-green-400" style="width:86%;"></div>
                    </div>
                    <span class="text-xs font-bold text-green-600 flex-shrink-0">4.30</span>
                  </div>
                </div>
              </div>

            </div>
          </div>

          <!-- Lowest Question -->
          <div>

            <div class="flex items-center gap-2 mb-3">
              <div class="w-5 h-5 rounded-full bg-red-100 border border-red-200 flex items-center justify-center flex-shrink-0">
                <svg class="w-3 h-3 text-red-500" fill="none" viewBox="0 0 16 16" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                  <path d="M8 4v8M4 8l4 4 4-4"/>
                </svg>
              </div>
              <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Needs Improvement</p>
            </div>

            <div id="lowestQuestions" class="flex flex-col gap-2">

            <div class="flex items-start gap-2.5 p-2.5 rounded-lg border border-red-100" style="background:#FEF2F2;">
              <span class="flex-shrink-0 w-5 h-5 rounded-full bg-red-500 text-white text-xs font-bold flex items-center justify-center mt-0.5">1</span>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">The teacher provides timely and constructive feedback.</p>
                <div class="flex items-center gap-2 mt-1.5">
                  <div class="flex-1 bg-red-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-red-500" style="width:48%;"></div>
                  </div>
                  <span class="text-xs font-bold text-red-600 flex-shrink-0">2.40</span>
                </div>
              </div>
            </div>

            <div class="flex items-start gap-2.5 p-2.5 rounded-lg border border-red-100" style="background:#FEF2F2;">
              <span class="flex-shrink-0 w-5 h-5 rounded-full bg-red-400 text-white text-xs font-bold flex items-center justify-center mt-0.5">2</span>
              <div class="flex-1 min-w-0">
                <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">The teacher uses varied and engaging teaching strategies.</p>
                <div class="flex items-center gap-2 mt-1.5">
                  <div class="flex-1 bg-red-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-full rounded-full bg-red-400" style="width:54%;"></div>
                  </div>
                  <span class="text-xs font-bold text-red-500 flex-shrink-0">2.70</span>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

    </div>
  </div>

  <!-- Tables -->
  <div class="section-card col-span-2 mt-6">

    <div class="flex items-center justify-between mb-3">

      <div>
        <h1 class="text-sm font-semibold text-gray-800 tracking-wide">Teacher evaluation</h1>
        <p class="text-xs text-gray-400 mt-0.5">Manage and monitor teacher performance, comments, and evaluation status.</p>
      </div>

    </div>

    <div class="w-full h-px bg-gray-100 mb-4"></div>

    <div class="card">

      <!-- Tabs -->
      <div class="tabs overflow-x-auto">
        <button class="tab-btn active" data-target="panel-ranking">
          Ranking
          <span class="badge badge-blue" id="cnt-ranking">0</span>
        </button>
        <button class="tab-btn" data-target="panel-not-evaluated">
          Not evaluated
          <span class="badge badge-gray" id="cnt-not-evaluated">0</span>
        </button>
        <button class="tab-btn" data-target="panel-abandoned">
          Abandoned
          <span class="badge badge-red" id="cnt-abandoned">0</span>
        </button>
      </div>

      <!-- Panel: Ranking -->
        <div id="panel-ranking" class="tab-panel active overflow-x-auto">
          <div class="toolbar">
            <div class="toolbar-search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="search-ranking" placeholder="Search teacher…" />
            </div>
            <button class="btn-export" id="btn-export-ranking">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10v3h10v-3M8 2v7M5 6l3 3 3-3"/>
              </svg>
              Export to Excel
            </button>
          </div>
          <div class="dt-table-wrap">
            <table id="tbl-ranking" class="w-full"></table>
          </div>
        </div>

      <!-- Panel: Not Evaluated -->
        <div id="panel-not-evaluated" class="tab-panel hidden  overflow-x-auto">
          <div class="toolbar">
            <div class="toolbar-search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="search-not-evaluated" placeholder="Search student…" />
            </div>
            <button class="btn-notify-all" id="btn-notify-all">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                <path d="m1 4 7 5 7-5"/>
              </svg>
              Notify All
            </button>
          </div>
          <div class="dt-table-wrap">
            <table id="tbl-not-evaluated" class="w-full"></table>
          </div>
        </div>

      <!-- Panel: Abandoned -->
        <div id="panel-abandoned" class="tab-panel hidden overflow-x-auto">
          <div class="toolbar">
            <div class="toolbar-search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
              </svg>
              <input type="text" id="search-abandoned" placeholder="Search student…" />
            </div>
          </div>
          <div class="dt-table-wrap">
            <table id="tbl-abandoned" class="w-full"></table>
          </div>
        </div>
    </div>
  </div>

</div>
