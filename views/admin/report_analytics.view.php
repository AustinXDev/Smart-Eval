<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\Middleware\AdminAuthMiddleware;
use App\Repositories\AdminRepo\AdminRepository;
use App\Services\Admin\AdminContext;

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/nav.php';

AdminAuthMiddleware::handle();

$adminRepository = new AdminRepository($pdo);

$adminContext = new AdminContext(
    $adminRepository
);

$controller = new DashboardController(
    $adminContext,
    $navigation
);

$data = $controller->index();

$department = $data['department'] ?? '';
$admin      = $data['admin'];
$role       = $data['role'];
$navigation = $data['navigation'] ?? [];

$currentUrl = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$pageTitle = "Reports & Analytics";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reports & Analytics <?php echo strtoupper($department); ?></title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/reportAnalytics.css">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dataTable.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- jQuery (DataTables dependency) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <!-- DataTables core -->
  <link  rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css" />
  <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>

</head>
<body class="bg-slate-50 pb-xl">


  
  <!-- Wrapper -->
  <div class="pb-lg max-w-screen-2xl min-h-dvh mx-auto flex relative"> 

    <!-- header -->
    <?php require __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full pt-20 pb-8 px-2 lg:px-10 lg:ml-70">
        <div class="mx-auto max-w-[1500px]">
    
            <!-- ===== PAGE HEADER ===== -->
            <header class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#6010FF]/[0.07] via-white to-white shadow-sm">
              <div class="flex flex-col gap-6 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">
            
                <div class="min-w-0 flex-1">
                  <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                    <span class="font-medium">Reports &amp; Analytics</span>
                    <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                    <span class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]">
                      <?php
                        echo htmlspecialchars(
                            isset($_GET['dept']) ? ucfirst($_GET['dept']) : 'Department',
                            ENT_QUOTES,
                            'UTF-8'
                        );
?>
                    </span>
                  </nav>
            
                  <div class="space-y-1.5">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Department Performance</h1>
                    <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                      Monitor evaluation participation, performance trends, category scores, and teacher evaluation results.
                    </p>
                  </div>
            
                  <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2">
                    <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                      <i class="far fa-calendar text-[#6010FF]" aria-hidden="true"></i>
                      Current View
                    </span>
                    <span class="hidden h-4 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
                    <span id="evaluationPeriod" class="text-xs font-semibold text-slate-800">Evaluation Period</span>
                    <span class="text-xs text-slate-300" aria-hidden="true">/</span>
                    <span id="semester" class="text-xs font-semibold text-slate-800">Semester</span>
                    <div id="status" class="inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>
                      Live
                    </div>
                  </div>
                </div>
            
                <div class="flex w-full shrink-0 flex-col gap-2 sm:w-auto sm:flex-row xl:self-center">
                  <button type="button" id="viewHistoryBtn"
                    class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition duration-200 hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98] sm:w-auto">
                    <i class="fas fa-history text-xs" aria-hidden="true"></i>
                    View History
                  </button>
                  <button type="button" id="exportPdfBtn"
                    class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition duration-200 hover:shadow-lg hover:shadow-[#6010FF]/30 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98] sm:w-auto">
                    <i class="fas fa-file-pdf text-xs" aria-hidden="true"></i>
                    Export to PDF
                  </button>
                </div>
            
              </div>
            </header>
            
            
            <div class="grid grid-cols-1 gap-6">
            
            <!-- ===== ANALYTICS OVERVIEW ===== -->
            <section class="w-full min-w-0" id="analyticsOverview">
            
              <div class="mb-4 flex items-start gap-3">
                <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                <div>
                  <h2 class="text-base font-semibold tracking-tight text-slate-900">Evaluation Overview</h2>
                  <p class="mt-0.5 text-xs text-slate-500">Key performance indicators for the selected evaluation period</p>
                </div>
              </div>
            
              <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            
                <!-- Teachers Evaluated -->
                <article class="group relative flex min-h-[160px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#6010FF]/[0.06] via-white to-white p-5 shadow-sm transition duration-200 hover:border-[#6010FF]/30 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                  <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-[#6010FF] to-[#6010FF]/10"></span>
                  <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15"><i class="fas fa-chalkboard-teacher text-sm"></i></span>
                    <i class="fas fa-arrow-up-right text-xs text-slate-300 transition-colors group-hover:text-[#6010FF]"></i>
                  </div>
                  <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Teachers Evaluated</p>
                    <h3 id="teachersEvaluated" class="mt-1 text-3xl font-bold tabular-nums tracking-tight text-slate-900">—</h3>
                    <p class="mt-1 text-xs text-slate-500">Faculty with submitted ratings</p>
                  </div>
                </article>
            
                <!-- Total Submissions -->
                <article class="group relative flex min-h-[160px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-blue-50/80 via-white to-white p-5 shadow-sm transition duration-200 hover:border-blue-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                  <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-blue-500 to-blue-500/10"></span>
                  <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600 ring-1 ring-blue-100"><i class="fas fa-clipboard-check text-sm"></i></span>
                    <i class="fas fa-arrow-up-right text-xs text-slate-300 transition-colors group-hover:text-blue-500"></i>
                  </div>
                  <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Total Submissions</p>
                    <h3 id="totalSubmissions" class="mt-1 text-3xl font-bold tabular-nums tracking-tight text-slate-900">—</h3>
                    <p class="mt-1 text-xs text-slate-500">Successfully submitted evaluations</p>
                  </div>
                </article>
            
                <!-- Overall Average -->
                <article class="group relative flex min-h-[160px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-emerald-50/80 via-white to-white p-5 shadow-sm transition duration-200 hover:border-emerald-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                  <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-emerald-500 to-emerald-500/10"></span>
                  <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 ring-1 ring-emerald-100"><i class="fas fa-chart-bar text-sm"></i></span>
                    <i class="fas fa-arrow-up-right text-xs text-slate-300 transition-colors group-hover:text-emerald-500"></i>
                  </div>
                  <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Overall Average</p>
                    <div class="mt-1 flex items-baseline gap-2">
                      <h3 id="overallAverage" class="text-3xl font-bold tabular-nums tracking-tight text-slate-900">—</h3>
                      <span class="text-xs font-medium text-slate-500">/ 5.00</span>
                    </div>
                    <p class="mt-1 text-xs text-slate-500">Mean faculty evaluation score</p>
                  </div>
                </article>
            
                <!-- Overall Rating -->
                <article class="group relative flex min-h-[160px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-amber-50/80 via-white to-white p-5 shadow-sm transition duration-200 hover:border-amber-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                  <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-amber-500 to-amber-500/10"></span>
                  <div class="flex items-start justify-between">
                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100"><i class="fas fa-award text-sm"></i></span>
                    <i class="fas fa-arrow-up-right text-xs text-slate-300 transition-colors group-hover:text-amber-500"></i>
                  </div>
                  <div class="mt-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Overall Rating</p>
                    <h3 id="overallRating" class="mt-1 text-2xl font-bold tracking-tight text-slate-900">—</h3>
                    <p class="mt-1 text-xs text-slate-500">Performance interpretation</p>
                  </div>
                </article>
            
              </div>
            </section>
            
            
            <!-- ===== PARTICIPATION FUNNEL ===== -->
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
              <div class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                    <i class="fa-solid fa-filter text-sm" aria-hidden="true"></i>
                  </div>
                  <div class="min-w-0">
                    <h2 class="text-sm font-semibold tracking-tight text-slate-900">Participation Funnel</h2>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Drop-off analysis across evaluation stages</p>
                  </div>
                </div>
                <span class="inline-flex w-fit items-center rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-1 text-xs font-semibold text-[#6010FF]">Funnel</span>
              </div>
            
              <div class="p-5 sm:p-6">
            
                <div id="funnel-container" class="mx-auto w-full max-w-5xl space-y-4">
            
                  <div class="funnel-row">
                    <span class="funnel-label" style="color:#6010FF;">Total Students</span>
                    <div class="funnel-track">
                      <div class="funnel-fill" id="funnel-fill-TotalEnrolled" style="width:0%;background:linear-gradient(90deg,#6010FF,#8B5CF6);">
                        <span id="totalEnrolled" class="mr-1"></span>
                        enrolled
                      </div>
                    </div>
                    <span class="funnel-count" style="color:#6010FF;" id="totalStudents"></span>
                  </div>
            
                  <div class="funnel-row">
                    <span class="funnel-label" style="color:#B91C1C;">Unresponsive</span>
                    <div class="funnel-track">
                      <div class="funnel-fill whitespace-nowrap" id="funnel-fill-Unresponsive" style="width:0%;background:linear-gradient(90deg,#DC2626,#EF4444);">Never started</div>
                    </div>
                    <span class="funnel-count" style="color:#B91C1C;" id="totalNeverStarted"></span>
                  </div>
            
                  <div class="funnel-row">
                    <span class="funnel-label" style="color:#B45309;">In-Progress</span>
                    <div class="funnel-track">
                      <div class="funnel-fill whitespace-nowrap" id="funnel-fill-InProgress" style="width:0%;background:linear-gradient(90deg,#D97706,#F59E0B);">In-progress</div>
                    </div>
                    <span class="funnel-count" style="color:#B45309;" id="totalAbandoned"></span>
                  </div>
            
                  <div class="funnel-row" style="margin-bottom:0;">
                    <span class="funnel-label" style="color:#047857;">Completed</span>
                    <div class="funnel-track">
                      <div class="funnel-fill whitespace-nowrap" id="funnel-fill-Completed" style="width:0%;background:linear-gradient(90deg,#059669,#10B981);">Fully submitted</div>
                    </div>
                    <span class="funnel-count" style="color:#047857;" id="totalCompleted"></span>
                  </div>
            
                </div>
            
                <!-- Metrics -->
                <div class="mt-6 grid grid-cols-1 gap-3 border-t border-slate-100 pt-5 sm:grid-cols-3">
            
                  <!-- Completion Rate -->
                  <div class="group relative overflow-hidden rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-emerald-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                    <div class="flex items-center justify-between gap-3">
                      <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                          <i class="fa-solid fa-circle-check text-sm" aria-hidden="true"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Completion Rate</p>
                      </div>
                      <i class="fas fa-chart-pie text-xs text-emerald-400"></i>
                    </div>
                    <div class="mt-4 flex items-end justify-between gap-4">
                      <div>
                        <p id="completionRate" class="text-2xl font-bold leading-none tabular-nums tracking-tight text-slate-900"></p>
                        <p class="mt-2 text-xs text-slate-500">Students completed evaluation</p>
                      </div>
                      <div class="hidden rounded-full border border-emerald-100 bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700 sm:block">Completed</div>
                    </div>
                  </div>
            
                  <!-- Incomplete -->
                  <div class="group relative overflow-hidden rounded-xl border border-amber-100 bg-gradient-to-br from-amber-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-amber-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                          <i class="fa-solid fa-hourglass-half text-sm" aria-hidden="true"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700">Incomplete</p>
                      </div>
                      <i class="fas fa-arrow-up-right text-xs text-amber-400 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
                    </div>
                    <div class="mt-4">
                      <p id="abandonedRate" class="text-2xl font-bold tabular-nums tracking-tight text-slate-900"></p>
                      <p class="mt-1 text-xs text-slate-500">Evaluations not completed</p>
                    </div>
                  </div>
            
                  <!-- Unresponsive -->
                  <div class="group relative overflow-hidden rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-rose-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                    <div class="flex items-center justify-between">
                      <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                          <i class="fa-solid fa-circle-minus text-sm" aria-hidden="true"></i>
                        </div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Unresponsive</p>
                      </div>
                      <i class="fas fa-arrow-up-right text-xs text-rose-400 transition-transform duration-200 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"></i>
                    </div>
                    <div class="mt-4">
                      <p id="neverStartedRate" class="text-2xl font-bold tabular-nums tracking-tight text-slate-900"></p>
                      <p class="mt-1 text-xs text-slate-500">Evaluations not started</p>
                    </div>
                  </div>
            
                </div>
              </div>
            </section>
            
            
            <!-- ===== TEACHER RANKING ===== -->
            <section id="teacher-ranking" aria-labelledby="ranking-title" class="w-full min-w-0">
            
              <div class="mb-4 flex items-start gap-3">
                <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                <div>
                  <h2 class="text-base font-semibold tracking-tight text-slate-900">Teacher Performance Result</h2>
                  <p class="mt-0.5 text-xs text-slate-500">View teachers' overall ratings and performance based on student evaluations.</p>
                </div>
              </div>
            
              <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
                <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-amber-50/70 to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                  <div class="flex min-w-0 items-start gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-600 ring-1 ring-amber-200/60">
                      <i class="fa-solid fa-trophy text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                      <div class="flex flex-wrap items-center gap-2.5">
                        <h2 id="ranking-title" class="text-sm font-semibold tracking-tight text-slate-900">Teacher Ranking</h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6010FF]">
                          <span id="cnt-ranking" class="tabular-nums">0</span>
                          <span>teachers</span>
                        </span>
                      </div>
                      <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Teacher performance based on evaluation results for the selected period.</p>
                    </div>
                  </div>
            
                  <button id="btn-export-ranking" type="button"
                    class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 text-sm font-semibold text-emerald-700 shadow-sm transition duration-200 hover:border-emerald-300 hover:bg-emerald-100 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 focus-visible:ring-offset-2 active:scale-[0.98]">
                    <i class="fas fa-file-excel text-sm" aria-hidden="true"></i>
                    <span>Export to Excel</span>
                  </button>
                </div>
            
                <div class="flex flex-col gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                  <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500">
                      <i class="fas fa-chart-simple text-[11px]" aria-hidden="true"></i>
                    </span>
                    <p class="text-xs font-medium text-slate-600">Compare scores, participation, and evaluation results.</p>
                  </div>
            
                  <div class="relative w-full sm:w-64 lg:w-72">
                    <label for="search-ranking" class="sr-only">Search teachers</label>
                    <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                    <input type="search" id="search-ranking" placeholder="Search teachers..."
                      class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-4 text-sm text-slate-700 shadow-sm outline-none placeholder:text-slate-400 transition duration-200 hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15" />
                  </div>
                </div>
            
                <div class="w-full min-w-0 overflow-x-auto" role="region" aria-label="Teacher ranking results" tabindex="0">
                  <table id="tbl-ranking" aria-labelledby="ranking-title" class="w-full min-w-[850px] border-collapse text-left text-sm"></table>
                </div>
            
              </div>
            </section>
            
            
            <!-- ===== CATEGORY + QUESTION GAP ===== -->
            <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">
            
              <!-- PERFORMANCE BY CATEGORY -->
              <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
                <div class="flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-blue-50/70 to-white px-5 py-4">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 ring-1 ring-blue-200/60">
                      <i class="fa-solid fa-chart-pie text-sm" aria-hidden="true"></i>
                    </div>
                    <div>
                      <h2 class="text-sm font-semibold tracking-tight text-slate-900">Performance by Category</h2>
                      <p class="mt-0.5 text-xs text-slate-500">Score per evaluation criterion</p>
                    </div>
                  </div>
                  <div class="flex items-center gap-1.5">
                    <span class="h-2 w-2 rounded-full bg-[#6010FF]"></span>
                    <span class="text-xs font-medium text-slate-500">Score</span>
                  </div>
                </div>
            
                <div class="flex flex-1 flex-col p-5">
            
                  <div id="categoryContainer" class="flex h-[300px] w-full items-center justify-center">
                    <canvas id="radarChartCanvas" class="block h-full w-full"></canvas>
                  </div>
            
                  <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
            
                    <!-- Highest -->
                    <div class="group relative overflow-hidden rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-emerald-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                      <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-emerald-500 to-emerald-500/10"></span>
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                            <i class="fas fa-arrow-trend-up text-xs"></i>
                          </div>
                          <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Highest Category</p>
                            <p class="mt-0.5 text-xs text-slate-500">Top performing criterion</p>
                          </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-100 bg-white px-2 py-0.5 text-xs font-semibold text-emerald-700">
                          <i class="fas fa-star text-[9px] text-emerald-500"></i>
                          Top Score
                        </span>
                      </div>
            
                      <div class="mt-4">
                        <p id="highestCategory" class="truncate text-base font-semibold tracking-tight text-slate-900">—</p>
                        <p class="mt-0.5 text-xs text-slate-500">Highest-rated evaluation category</p>
                      </div>
            
                      <div class="mt-4 flex items-end justify-between border-t border-emerald-100/80 pt-4">
                        <div class="flex items-baseline gap-1.5">
                          <p id="highestScore" class="text-3xl font-bold leading-none tabular-nums tracking-tight text-emerald-700">—</p>
                          <span class="text-xs font-medium text-slate-500">/ 5.00</span>
                        </div>
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                          <i class="fas fa-check text-xs"></i>
                        </div>
                      </div>
                    </div>
            
                    <!-- Lowest -->
                    <div class="group relative overflow-hidden rounded-xl border border-rose-100 bg-gradient-to-br from-rose-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-rose-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                      <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-rose-500 to-rose-500/10"></span>
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                            <i class="fas fa-arrow-trend-down text-xs"></i>
                          </div>
                          <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Lowest Category</p>
                            <p class="mt-0.5 text-xs text-slate-500">Area needing attention</p>
                          </div>
                        </div>
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full border border-rose-100 bg-white px-2 py-0.5 text-xs font-semibold text-rose-700">
                          <i class="fas fa-arrow-down text-[9px] text-rose-500"></i>
                          Needs Focus
                        </span>
                      </div>
            
                      <div class="mt-4">
                        <p id="lowestCategory" class="truncate text-base font-semibold tracking-tight text-slate-900">—</p>
                        <p class="mt-0.5 text-xs text-slate-500">Lowest-rated evaluation category</p>
                      </div>
            
                      <div class="mt-4 flex items-end justify-between border-t border-rose-100/80 pt-4">
                        <div class="flex items-baseline gap-1.5">
                          <p id="lowestScore" class="text-3xl font-bold leading-none tabular-nums tracking-tight text-rose-700">—</p>
                          <span class="text-xs font-medium text-slate-500">/ 5.00</span>
                        </div>
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-rose-100 text-rose-600">
                          <i class="fas fa-exclamation text-xs"></i>
                        </div>
                      </div>
                    </div>
            
                  </div>
                </div>
              </section>
            
            
              <!-- QUESTION GAP -->
              <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
                <div class="flex items-center justify-between gap-3 border-b border-slate-100 bg-gradient-to-r from-rose-50/70 to-white px-5 py-4">
                  <div class="flex min-w-0 items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 ring-1 ring-rose-200/60">
                      <i class="fa-solid fa-crosshairs text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                      <h2 class="text-sm font-semibold tracking-tight text-slate-900">Question Gap Analysis</h2>
                      <p class="mt-0.5 truncate text-xs text-slate-500">Highest vs. lowest rated evaluation questions</p>
                    </div>
                  </div>
                  <span class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full border border-rose-100 bg-white px-2.5 py-1 text-xs font-semibold text-rose-700">Talking Points</span>
                </div>
            
                <div id="parentContainer" class="grid min-h-0 flex-1 grid-cols-1 gap-5 p-5 sm:grid-cols-2">
            
                  <div class="flex min-h-0 flex-col">
                    <div class="mb-3 flex shrink-0 items-center gap-2">
                      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-emerald-100 text-emerald-600">
                        <i class="fa-solid fa-arrow-up text-xs" aria-hidden="true"></i>
                      </div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Strongest Questions</p>
                    </div>
                    <div id="highestQuestions"
                      class="flex h-auto max-h-[520px] min-h-0 flex-col gap-2 overflow-y-auto overflow-x-hidden rounded-xl border border-emerald-100 bg-emerald-50/40 p-2 pr-1 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-emerald-200">
                    </div>
                  </div>
            
                  <div class="flex min-h-0 flex-col">
                    <div class="mb-3 flex shrink-0 items-center gap-2">
                      <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-rose-100 text-rose-600">
                        <i class="fa-solid fa-arrow-down text-xs" aria-hidden="true"></i>
                      </div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-rose-700">Needs Improvement</p>
                    </div>
                    <div id="lowestQuestions"
                      class="flex h-auto max-h-[520px] min-h-0 flex-col gap-2 overflow-y-auto overflow-x-hidden rounded-xl border border-rose-100 bg-rose-50/40 p-2 pr-1 scrollbar-thin scrollbar-track-transparent scrollbar-thumb-rose-200">
                    </div>
                  </div>
            
                </div>
              </section>
            
            </div>
            
            <!-- ===== RATING DISTRIBUTION ===== -->
            <section class="w-full min-w-0" aria-labelledby="rating-distribution-title">
            
              <div class="mb-4 flex items-start gap-3">
                <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                <div>
                  <h2 class="text-base font-semibold tracking-tight text-slate-900">Overall Teacher Performance Distribution</h2>
                  <p class="mt-0.5 text-xs text-slate-500">Shows the breakdown of teachers by overall performance rating.</p>
                </div>
              </div>
            
              <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            
                <div class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-emerald-50/70 to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                  <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-100 text-emerald-600 ring-1 ring-emerald-200/60">
                      <i class="fa-solid fa-chart-column text-sm" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                      <h2 id="rating-distribution-title" class="text-sm font-semibold tracking-tight text-slate-900">Rating Distribution</h2>
                      <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Breakdown of teachers by evaluation performance rating.</p>
                    </div>
                  </div>
            
                  <span class="inline-flex w-[100px] shrink-0 items-center justify-center gap-1.5 rounded-full border border-emerald-100 bg-white px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span id="rating-distribution-total" class="tabular-nums">0</span>
                    <span>teachers</span>
                  </span>
                </div>
            
                <div class="grid grid-cols-1 gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(240px,0.85fr)_minmax(0,1.15fr)] lg:items-center">
            
                  <div class="min-w-0">
                    <div class="mb-4 flex items-center justify-between">
                      <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Performance Overview</h3>
                      <span class="text-xs text-slate-500">By rating</span>
                    </div>
                    <div id="rating-distribution" class="grid grid-cols-1 gap-3" aria-live="polite">
                      <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-8 text-center">
                        <i class="fa-solid fa-chart-simple mb-2 text-xl text-slate-300"></i>
                        <p class="text-sm font-medium text-slate-500">Rating data will appear when available.</p>
                      </div>
                    </div>
                  </div>
            
                  <div class="min-w-0 rounded-xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white p-3 sm:p-4">
                    <div class="mb-3 flex items-center justify-between">
                      <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-700">Teacher Count</h3>
                      <span class="text-xs text-slate-500">Rating comparison</span>
                    </div>
                    <div class="relative h-64 min-w-0 sm:h-72">
                      <canvas id="ratingDistributionChart" role="img" aria-label="Teacher rating distribution horizontal bar chart"></canvas>
                    </div>
                  </div>
            
                </div>
              </div>
            </section>
            
            <!-- ===== LONGITUDINAL TREND ===== -->
            <section class="w-full min-w-0">
                
              <div class="mb-4 flex items-start gap-3">
                  
                <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                <div>
                  <h2 class="text-base font-semibold tracking-tight text-slate-900">Historical Comparison</h2>
                  <p class="mt-0.5 text-xs text-slate-500">Compares previous evaluation periods.</p>
                </div>
                
              </div>
              
              <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                  
                  <div class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                        <i class="fa-solid fa-chart-line text-sm" aria-hidden="true"></i>
                      </div>
                      <div>
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900">Longitudinal Trend</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Mean score over the last 5 semesters</p>
                      </div>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-1 text-xs font-semibold text-[#6010FF]">Trend</span>
                  </div>
                
                  <div class="p-5 sm:p-6">
                
                    <div class="mb-4 flex items-center gap-2">
                      <span class="h-2 w-2 rounded-full bg-[#6010FF]"></span>
                      <span class="text-xs font-medium text-slate-500">Mean Score</span>
                    </div>
                
                    <div id="trendChart" class="relative h-[280px] w-full min-w-0 overflow-hidden rounded-xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white sm:h-[300px]">
                      <div id="trendChart" class="relative h-[280px] w-full min-w-0 overflow-x-auto overflow-y-hidden rounded-xl p-3 sm:h-[300px]">
                        <canvas id="trendChartCanvas" class="!block !h-full !w-full overflow-x"></canvas>
                      </div>
                    </div>
                
                    <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2">
                
                      <div id="meanParentContainer" class="group rounded-xl border border-emerald-100 bg-gradient-to-br from-emerald-50 via-white to-white p-4 shadow-sm transition duration-200 hover:border-emerald-200 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                          <p class="mean-title text-xs font-semibold uppercase tracking-wide" style="color:#047857;">Mean Score</p>
                          <i class="fas fa-star text-xs text-emerald-500"></i>
                        </div>
                        <div class="mt-2 flex items-end gap-2">
                          <p id="meanScore" class="text-3xl font-bold tabular-nums tracking-tight" style="color:#0F172A;"></p>
                          <sub class="adjectiveRating mb-1 text-xs font-medium text-slate-500"></sub>
                        </div>
                        <p class="mean-sublabel mt-1 text-xs text-slate-500">Overall Rating</p>
                      </div>
                
                      <div class="group rounded-xl border border-[#6010FF]/15 bg-gradient-to-br from-[#6010FF]/[0.07] via-white to-white p-4 shadow-sm transition duration-200 hover:border-[#6010FF]/30 hover:shadow-md motion-safe:hover:-translate-y-0.5">
                        <div class="flex items-center justify-between">
                          <p class="text-xs font-semibold uppercase tracking-wide text-[#6010FF]">Score Trend</p>
                          <i class="fas fa-arrow-trend-up text-xs text-[#6010FF]"></i>
                        </div>
                        <p id="trendGrowth" class="mt-2 text-3xl font-bold tabular-nums tracking-tight text-slate-900"></p>
                        <p class="mt-1 text-xs text-slate-500">vs. last semester</p>
                      </div>
                
                    </div>
                  </div>
                  
              </div>
              
            </section>
            
            <!-- ===== YEAR LEVEL PARTICIPATION ===== -->
            <section class="w-full min-w-0">
              
              <div class="mb-4 flex items-start gap-3">
                  
                <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                <div>
                  <h2 class="text-base font-semibold tracking-tight text-slate-900">Year-Level Particpation Analysis</h2>
                  <p class="mt-0.5 text-xs text-slate-500">Visualize student evaluation particpation across different year level.</p>
                </div>
                
              </div>
              
              <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                  
                  <div class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-blue-50/70 to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div class="flex items-center gap-3">
                      <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 ring-1 ring-blue-200/60">
                        <i class="fa-solid fa-users text-sm" aria-hidden="true"></i>
                      </div>
                      <div>
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900">Year-Level Participation</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Evaluation completion rate by student year level</p>
                      </div>
                    </div>
                    <span class="inline-flex w-fit items-center rounded-full border border-blue-100 bg-white px-2.5 py-1 text-xs font-semibold text-blue-700">Grouped Bar</span>
                  </div>
                
                  <div class="p-5 sm:p-6">
                    <div id="participationContainer" class="h-[300px] w-full overflow-hidden rounded-xl border border-slate-100 bg-gradient-to-b from-slate-50 to-white p-3">
                      <canvas id="participationChart" class="block h-full w-full"></canvas>
                    </div>
                  </div>
                  
              </div>
            </section>
            
            </div>
            
            
            <!-- ===== HISTORICAL PERIOD BANNER ===== -->
            <div id="historical-banner" class="fixed bottom-0 left-0 right-0 z-40 hidden px-4 pb-4">
              <div class="mx-auto max-w-5xl">
                <div class="flex flex-col gap-4 rounded-2xl border border-slate-700 bg-slate-900 px-4 py-4 shadow-2xl shadow-slate-900/30 sm:flex-row sm:items-center sm:justify-between sm:px-5">
            
                  <div class="flex min-w-0 items-start gap-3 sm:items-center">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/20 text-violet-300 ring-1 ring-[#6010FF]/30">
                      <i class="fas fa-clock text-xs"></i>
                    </div>
                    <div class="min-w-0">
                      <div class="flex flex-wrap items-center gap-2">
                        <p class="text-sm font-semibold text-white">Viewing Historical Period</p>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-[#6010FF]/20 px-2 py-0.5 text-xs font-medium text-violet-300">
                          <span class="h-1.5 w-1.5 rounded-full bg-violet-400 motion-safe:animate-pulse"></span>
                          Read-only
                        </span>
                      </div>
                      <p class="mt-1 text-xs leading-relaxed text-slate-300">You are viewing a past evaluation — data shown is not live.</p>
                      <div class="mt-2 flex items-center gap-1.5 sm:hidden">
                        <i class="fas fa-calendar-alt text-[10px] text-slate-400"></i>
                        <span class="text-xs text-slate-300" id="banner-period-label-mobile">—</span>
                      </div>
                    </div>
                  </div>
            
                  <div class="flex w-full items-center gap-2 sm:w-auto">
                    <div class="hidden items-center gap-1.5 rounded-xl border border-slate-700 bg-slate-800 px-3 py-2 sm:flex">
                      <i class="fas fa-calendar-alt text-[10px] text-slate-400"></i>
                      <span class="text-xs text-slate-200" id="banner-period-label">—</span>
                    </div>
                    <button id="btn-return-current"
                      class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 py-2 text-xs font-semibold text-white shadow-md shadow-[#6010FF]/30 transition duration-200 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-300 active:scale-[.98] sm:flex-none">
                      <i class="fas fa-rotate-left text-[10px]"></i>
                      Return to Current
                    </button>
                  </div>
            
                </div>
              </div>
            </div>
        
        </div>
    </main>

    <?php require __DIR__ . '/../partials/modals/shared/confirmation_modal.php';?>
    <?php require __DIR__ . '/../partials/modals/analytics_modal/progressModal.php';?>
    <?php require __DIR__ . '/../partials/modals/analytics_modal/viewHistoryModal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/analytics_modal/commentModal.php'; ?>

  </div>

<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>

<script> 
window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script> 

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= BASE_URL ?>assets/js/admin/report_analytics/index.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</html>