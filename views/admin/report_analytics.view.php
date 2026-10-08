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

    <!-- ============================================================
        REPORT & ANALYTICS PAGE
    ============================================================ -->

    <main class="min-h-screen w-full pt-20 pb-8 px-2 lg:px-10 lg:ml-70">

      <div class="mx-auto max-w-[1500px]">

        <!-- ========================================================
            PAGE HEADER
        ========================================================= -->
        <div class="mb-6 rounded-2xl border border-slate-200 bg-white shadow-sm">

          <div class="flex flex-col gap-5 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">

            <!-- Left -->
            <div class="min-w-0">

              <!-- Breadcrumb -->
              <div class="mb-3 flex flex-wrap items-center gap-2 text-xs font-medium text-slate-400">
                <span>Reports & Analytics</span>

                <i class="fas fa-chevron-right text-[8px] text-slate-300"></i>

                <span
                  class="inline-flex items-center rounded-lg border border-violet-100 bg-violet-50 px-2.5 py-1
                        font-semibold text-violet-700"
                >
                  <?php echo isset($_GET['dept']) ? ucfirst($_GET['dept']) : 'Department'; ?>
                </span>
              </div>

              <!-- Title -->
              <div class="flex flex-col gap-1">
                <h1 class="text-xl font-bold tracking-tight text-slate-900 sm:text-2xl">
                  Department Performance
                </h1>

                <p class="max-w-2xl text-xs leading-relaxed text-slate-400 sm:text-sm">
                  Monitor evaluation participation, performance trends, category scores,
                  and teacher evaluation results.
                </p>
              </div>

              <!-- Current View -->
              <div class="mt-4 flex flex-wrap items-center gap-2">

                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-400">
                  <i class="fas fa-calendar-alt text-[11px]"></i>
                  Current view
                </span>

                <span
                  id="evaluationPeriod"
                  class="inline-flex items-center rounded-lg border border-slate-200
                        bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700"
                >
                  Evaluation Period
                </span>

                <span class="text-slate-300">/</span>

                <span
                  id="semester"
                  class="inline-flex items-center rounded-lg border border-slate-200
                        bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-700"
                >
                  Semester
                </span>

                <!-- Live -->
                <div
                  id="status"
                  class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200
                        bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700"
                >
                  <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-emerald-500"></span>
                  Live
                </div>

              </div>

            </div>


            <!-- ====================================================
                HEADER ACTIONS
            ===================================================== -->
            <div class="flex w-full flex-col gap-2 sm:flex-row xl:w-auto">

              <button 
                id="viewHistoryBtn" 
                class="
                  inline-flex
                  h-10
                  w-full
                  items-center
                  justify-center
                  gap-2
                  rounded-xl
                  border
                  border-slate-200
                  bg-white
                  px-4
                  text-xs
                  font-semibold
                  text-slate-600
                  shadow-sm
                  transition-all
                  duration-200
                  hover:border-violet-200
                  hover:bg-violet-50
                  hover:text-violet-700
                  active:scale-[.98]
                  sm:w-auto
                "
              >
                <i class="fas fa-history text-[11px]"></i>
                View History
              </button>

              <button 
                id="exportPdfBtn" 
                class="
                  inline-flex
                  h-10
                  w-full
                  items-center
                  justify-center
                  gap-2
                  rounded-xl
                  border
                  border-violet-600
                  bg-violet-600
                  px-4
                  text-xs
                  font-semibold
                  text-white
                  shadow-sm
                  shadow-violet-600/20
                  transition-all
                  duration-200
                  hover:bg-violet-700
                  hover:shadow-md
                  hover:shadow-violet-600/20
                  active:scale-[.98]
                  sm:w-auto
                "
              >
                <i class="fas fa-file-pdf text-[11px]"></i>
                Export to PDF
              </button>

            </div>

          </div>

        </div>


        <!-- ========================================================
            ANALYTICS GRID
        ========================================================= -->
        <div class="grid grid-cols-1 gap-5">


          <!-- ======================================================
              PARTICIPATION FUNNEL
          ======================================================= -->
          <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <!-- Header -->
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

              <div>
                <div class="flex items-center gap-2">

                  <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg
                          bg-violet-50 text-violet-600"
                  >
                    <i class="fas fa-filter text-xs"></i>
                  </div>

                  <h2 class="text-sm font-semibold text-slate-900">
                    Participation Funnel
                  </h2>

                </div>

                <p class="mt-1.5 pl-10 text-xs text-slate-400">
                  Drop-off analysis across evaluation stages
                </p>
              </div>

              <span
                class="inline-flex w-fit items-center rounded-full border border-violet-100
                      bg-violet-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide
                      text-violet-700"
              >
                Funnel
              </span>

            </div>


            <!-- Funnel -->
            <div class="p-5 sm:p-6">

              <div
                id="funnel-container"
                class="mx-auto w-full max-w-5xl space-y-4"
              >

                <!-- Total -->
                <div class="funnel-row">
                  <span class="funnel-label">Total Students</span>

                  <div class="funnel-track">
                    <div
                      class="funnel-fill"
                      id="funnel-fill-TotalEnrolled"
                      style="width:0%;background:#7C3AED;"
                    >
                      <span id="totalEnrolled" class="mr-1"></span>
                      enrolled
                    </div>
                  </div>

                  <span class="funnel-count" id="totalStudents"></span>
                </div>


                <!-- Never Started -->
                <div class="funnel-row">
                  <span
                    class="funnel-label"
                    style="color:#B45309;"
                  >
                    Unresponsive
                  </span>

                  <div class="funnel-track">
                    <div
                      class="funnel-fill whitespace-nowrap"
                      id="funnel-fill-Unresponsive"
                      style="width:0%;background:#F59E0B;"
                    >
                      Never started
                    </div>
                  </div>

                  <span
                    class="funnel-count"
                    style="color:#B45309;"
                    id="totalNeverStarted"
                  ></span>
                </div>


                <!-- In Progress -->
                <div class="funnel-row">
                  <span
                    class="funnel-label"
                    style="color:#92400E;"
                  >
                    In-Progress
                  </span>

                  <div class="funnel-track">
                    <div
                      class="funnel-fill"
                      id="funnel-fill-InProgress"
                      style="width:0%;background:#D97706;"
                    >
                      In-progress
                    </div>
                  </div>

                  <span
                    class="funnel-count"
                    style="color:#92400E;"
                    id="totalAbandoned"
                  ></span>
                </div>


                <!-- Completed -->
                <div class="funnel-row" style="margin-bottom:0;">
                  <span
                    class="funnel-label"
                    style="color:#047857;"
                  >
                    Completed
                  </span>

                  <div class="funnel-track">
                    <div
                      class="funnel-fill whitespace-nowrap"
                      id="funnel-fill-Completed"
                      style="width:0%;background:#059669;"
                    >
                      Fully submitted
                    </div>
                  </div>

                  <span
                    class="funnel-count"
                    style="color:#047857;"
                    id="totalCompleted"
                  ></span>
                </div>

              </div>


              <!-- Metrics -->
              <div class="mt-6 grid grid-cols-1 gap-3 border-t border-slate-100 pt-5 sm:grid-cols-3">

  
                <!-- Completion Rate -->
                <div
                  class="
                    group relative overflow-hidden rounded-2xl
                    border border-violet-100
                    bg-gradient-to-br from-violet-50/90 via-white to-white
                    p-4 sm:p-5
                    shadow-sm
                    transition-all duration-200
                    hover:-translate-y-0.5
                    hover:border-violet-200
                    hover:shadow-md hover:shadow-violet-100/50
                  "
                >
                  <!-- Decorative background -->
                  <div
                    class="
                      pointer-events-none absolute -right-8 -top-8
                      h-24 w-24 rounded-full
                      bg-violet-100/60
                      blur-2xl
                      transition-all duration-300
                      group-hover:bg-violet-200/60
                    "
                  ></div>

                  <div class="relative">

                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3">

                      <div class="flex items-center gap-2.5">
                        <div
                          class="
                            flex h-8 w-8 shrink-0 items-center justify-center
                            rounded-lg
                            bg-violet-100
                            text-violet-600
                          "
                        >
                          <i class="fas fa-check text-xs"></i>
                        </div>

                        <p
                          class="
                            text-[10px] font-bold uppercase
                            tracking-[0.12em] text-slate-500
                          "
                        >
                          Completion Rate
                        </p>
                      </div>

                      <span
                        class="
                          inline-flex h-7 w-7 items-center justify-center
                          rounded-full
                          border border-violet-100
                          bg-white/80
                          text-violet-500
                        "
                      >
                        <i class="fas fa-chart-pie text-[10px]"></i>
                      </span>

                    </div>

                    <!-- Value -->
                    <div class="mt-5 flex items-end justify-between gap-4">

                      <div>
                        <p
                          id="completionRate"
                          class="
                            text-2xl font-bold leading-none
                            tracking-tight text-slate-900
                          "
                        ></p>

                        <p class="mt-2 text-[11px] font-medium text-slate-400">
                          Students completed evaluation
                        </p>
                      </div>

                      <!-- Status -->
                      <div
                        class="
                          hidden rounded-lg
                          bg-violet-100/70
                          px-2.5 py-1.5
                          text-[10px] font-semibold
                          text-violet-700
                          sm:block
                        "
                      >
                        Completed
                      </div>

                    </div>

                    <!-- Bottom accent -->
                    <div class="mt-4 h-1 overflow-hidden rounded-full bg-violet-100">
                      <div
                        class="h-full w-full rounded-full bg-violet-500"
                      ></div>
                    </div>

                  </div>
                </div>


                <!-- Abandoned -->
                <div
                  class="
                    group rounded-2xl
                    border border-amber-100
                    bg-amber-50/60
                    p-4
                    transition-all duration-200
                    hover:-translate-y-0.5
                    hover:border-amber-200
                    hover:bg-amber-50
                    hover:shadow-sm
                  "
                >
                  <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2.5">
                      <div
                        class="
                          flex h-8 w-8 items-center justify-center
                          rounded-lg
                          bg-white
                          text-amber-600
                          shadow-sm
                        "
                      >
                        <i class="fas fa-pause-circle text-xs"></i>
                      </div>

                      <p
                        class="
                          text-[10px] font-bold uppercase
                          tracking-wider text-amber-700
                        "
                      >
                        Incomplete
                      </p>
                    </div>

                    <i
                      class="
                        fas fa-arrow-up-right
                        text-[10px] text-amber-400
                        transition-transform duration-200
                        group-hover:translate-x-0.5
                        group-hover:-translate-y-0.5
                      "
                    ></i>

                  </div>

                  <div class="mt-4">
                    <p
                      id="abandonedRate"
                      class="
                        text-2xl font-bold
                        tracking-tight text-slate-900
                      "
                    ></p>

                    <p class="mt-1 text-[11px] font-medium text-slate-400">
                      Evaluations not completed
                    </p>
                  </div>
                </div>


                <!-- Never Started -->
                <div
                  class="
                    group rounded-2xl
                    border border-rose-100
                    bg-rose-50/60
                    p-4
                    transition-all duration-200
                    hover:-translate-y-0.5
                    hover:border-rose-200
                    hover:bg-rose-50
                    hover:shadow-sm
                  "
                >
                  <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2.5">
                      <div
                        class="
                          flex h-8 w-8 items-center justify-center
                          rounded-lg
                          bg-white
                          text-rose-600
                          shadow-sm
                        "
                      >
                        <i class="fas fa-circle-xmark text-xs"></i>
                      </div>

                      <p
                        class="
                          text-[10px] font-bold uppercase
                          tracking-wider text-rose-700
                        "
                      >
                        Unresponsive
                      </p>
                    </div>

                    <i
                      class="
                        fas fa-arrow-up-right
                        text-[10px] text-rose-400
                        transition-transform duration-200
                        group-hover:translate-x-0.5
                        group-hover:-translate-y-0.5
                      "
                    ></i>

                  </div>

                  <div class="mt-4">
                    <p
                      id="neverStartedRate"
                      class="
                        text-2xl font-bold
                        tracking-tight text-slate-900
                      "
                    ></p>

                    <p class="mt-1 text-[11px] font-medium text-slate-400">
                      Evaluations not started
                    </p>
                  </div>
                </div>

              </div>

            </div>

          </section>


          <!-- ======================================================
              LONGITUDINAL TREND
          ======================================================= -->
          <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

              <div class="flex items-center gap-3">

                <div
                  class="flex h-8 w-8 items-center justify-center rounded-lg
                        bg-sky-50 text-sky-600"
                >
                  <i class="fas fa-chart-line text-xs"></i>
                </div>

                <div>
                  <h2 class="text-sm font-semibold text-slate-900">
                    Longitudinal Trend
                  </h2>

                  <p class="mt-1 text-xs text-slate-400">
                    Mean score over the last 5 semesters
                  </p>
                </div>

              </div>

              <span
                class="inline-flex w-fit items-center rounded-full border border-sky-100
                      bg-sky-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide
                      text-sky-700"
              >
                Trend
              </span>

            </div>


            <div class="p-5 sm:p-6">

              <!-- Legend -->
              <div class="mb-4 flex items-center gap-2">
                <span class="h-2 w-2 rounded-full bg-violet-600"></span>
                <span class="text-[11px] font-medium text-slate-500">
                  Mean Score
                </span>
              </div>


              <!-- Chart -->
              <div
                id="trendChart"
                class="
                  relative
                  h-[280px]
                  w-full
                  min-w-0
                  overflow-hidden
                  rounded-xl
                  border border-slate-100
                  bg-slate-50/40
                  sm:h-[300px]
                "
              >
                <div
                  id="trendChart"
                  class="
                    relative h-[280px] w-full min-w-0 overflow-x-auto overflow-y-hidden rounded-xl border border-slate-100 bg-slate-50/40 sm:h-[300px]
                  "
                >
                  <canvas
                    id="trendChartCanvas"
                    class="!block !h-full !w-full overflow-x"
                  ></canvas>
                </div>
              </div>


              <!-- Summary -->
              <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                <div
                  id="meanParentContainer"
                  class="rounded-xl border border-emerald-100 bg-emerald-50/60 p-4"
                >
                  <div class="flex items-center justify-between">

                    <p
                      class="mean-title text-[10px] font-bold uppercase tracking-wider"
                      style="color:#334155;"
                    >
                      Mean Score
                    </p>

                    <i class="fas fa-star text-xs text-emerald-500"></i>

                  </div>

                  <div class="mt-2 flex items-end gap-2">

                    <p
                      id="meanScore"
                      class="text-2xl font-bold tracking-tight"
                      style="color:#3C3489;"
                    ></p>

                    <sub class="adjectiveRating mb-1 text-xs font-medium text-slate-500"></sub>

                  </div>

                  <p class="mean-sublabel mt-1 text-xs text-slate-400">
                    Overall Rating
                  </p>

                </div>


                <div
                  class="rounded-xl border border-violet-100 bg-violet-50/60 p-4"
                >

                  <div class="flex items-center justify-between">

                    <p class="text-[10px] font-bold uppercase tracking-wider text-violet-600">
                      Score Trend
                    </p>

                    <i class="fas fa-arrow-trend-up text-xs text-violet-400"></i>

                  </div>

                  <p
                    id="trendGrowth"
                    class="mt-2 text-2xl font-bold tracking-tight text-violet-800"
                  ></p>

                  <p class="mt-1 text-xs text-violet-500">
                    vs. last semester
                  </p>

                </div>

              </div>

            </div>

          </section>


          <!-- ======================================================
              YEAR LEVEL PARTICIPATION
          ======================================================= -->
          <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">

              <div class="flex items-center gap-3">

                <div
                  class="flex h-8 w-8 items-center justify-center rounded-lg
                        bg-amber-50 text-amber-600"
                >
                  <i class="fas fa-users text-xs"></i>
                </div>

                <div>
                  <h2 class="text-sm font-semibold text-slate-900">
                    Year-Level Participation
                  </h2>

                  <p class="mt-1 text-xs text-slate-400">
                    Evaluation completion rate by student year level
                  </p>
                </div>

              </div>

              <span
                class="inline-flex w-fit items-center rounded-full border border-amber-100
                      bg-amber-50 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide
                      text-amber-700"
              >
                Grouped Bar
              </span>

            </div>


            <div class="p-5 sm:p-6">

              <div
                id="participationContainer"
                class="h-[300px] w-full overflow-hidden rounded-xl border border-slate-100 bg-slate-50/40"
              >
                <canvas
                  id="participationChart"
                  class="block h-full w-full"
                ></canvas>
              </div>

            </div>

          </section>


          <!-- ======================================================
              CATEGORY + QUESTION GAP
          ======================================================= -->
          <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">


            <!-- PERFORMANCE BY CATEGORY -->
            <section class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

              <div class="flex items-center justify-between border-b border-slate-100 px-5 py-5">

                <div class="flex items-center gap-3">

                  <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg
                          bg-violet-50 text-violet-600"
                  >
                    <i class="fas fa-chart-pie text-xs"></i>
                  </div>

                  <div>
                    <h2 class="text-sm font-semibold text-slate-900">
                      Performance by Category
                    </h2>

                    <p class="mt-1 text-xs text-slate-400">
                      Score per evaluation criterion
                    </p>
                  </div>

                </div>

                <div class="flex items-center gap-1.5">
                  <span class="h-2 w-2 rounded-full bg-violet-600"></span>
                  <span class="text-[11px] font-medium text-slate-400">
                    Score
                  </span>
                </div>

              </div>


              <div class="flex flex-1 flex-col p-5">

                <div
                  id="categoryContainer"
                  class="flex h-[300px] w-full items-center justify-center"
                >
                  <canvas
                    id="radarChartCanvas"
                    class="block h-full w-full"
                  ></canvas>
                </div>


                <!-- Category summary -->
                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                  <!-- Highest Category -->
                  <div
                    class="
                      group relative overflow-hidden rounded-2xl
                      border border-emerald-100
                      bg-gradient-to-br from-emerald-50 via-white to-white
                      p-4
                      shadow-sm
                      transition-all duration-200
                      hover:-translate-y-0.5
                      hover:border-emerald-200
                      hover:shadow-md hover:shadow-emerald-100/50
                    "
                  >

                    <!-- Decorative glow -->
                    <div
                      class="
                        pointer-events-none absolute -right-8 -top-8
                        h-24 w-24 rounded-full
                        bg-emerald-100/60
                        blur-2xl
                        transition-all duration-300
                        group-hover:bg-emerald-200/60
                      "
                    ></div>


                    <div class="relative">

                      <!-- Header -->
                      <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2.5">

                          <div
                            class="
                              flex h-8 w-8 shrink-0 items-center justify-center
                              rounded-xl
                              bg-emerald-100
                              text-emerald-600
                            "
                          >
                            <i class="fas fa-arrow-trend-up text-xs"></i>
                          </div>

                          <div>
                            <p
                              class="
                                text-[10px] font-bold uppercase
                                tracking-[0.12em] text-emerald-700
                              "
                            >
                              Highest Category
                            </p>

                            <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                              Top performing criterion
                            </p>
                          </div>

                        </div>


                        <!-- Score badge -->
                        <span
                          class="
                            inline-flex shrink-0 items-center gap-1
                            rounded-full
                            border border-emerald-100
                            bg-white/80
                            px-2 py-1
                            text-[10px] font-semibold
                            text-emerald-700
                          "
                        >
                          <i class="fas fa-star text-[9px] text-emerald-500"></i>
                          Top Score
                        </span>

                      </div>


                      <!-- Category -->
                      <div class="mt-5">

                        <p
                          id="highestCategory"
                          class="
                            truncate
                            text-base font-bold
                            tracking-tight
                            text-slate-900
                          "
                        >
                          —
                        </p>

                        <p class="mt-1 text-[11px] text-slate-400">
                          Highest-rated evaluation category
                        </p>

                      </div>


                      <!-- Score -->
                      <div
                        class="
                          mt-4 flex items-end justify-between
                          border-t border-emerald-100/70
                          pt-4
                        "
                      >

                        <div class="flex items-baseline gap-1.5">

                          <p
                            id="highestScore"
                            class="
                              text-3xl font-bold
                              leading-none
                              tracking-tight
                              text-emerald-700
                            "
                          >
                            —
                          </p>

                          <span class="text-xs font-medium text-slate-400">
                            / 5.00
                          </span>

                        </div>


                        <div
                          class="
                            flex h-8 w-8 items-center justify-center
                            rounded-full
                            bg-emerald-100
                            text-emerald-600
                          "
                        >
                          <i class="fas fa-check text-xs"></i>
                        </div>

                      </div>


                      <!-- Bottom accent -->
                      <div class="mt-4 h-1 overflow-hidden rounded-full bg-emerald-100">

                        <div
                          class="
                            h-full w-full rounded-full
                            bg-emerald-500
                          "
                        ></div>

                      </div>

                    </div>

                  </div>


                  <!-- Lowest Category -->
                  <div
                    class="
                      group relative overflow-hidden rounded-2xl
                      border border-rose-100
                      bg-gradient-to-br from-rose-50 via-white to-white
                      p-4
                      shadow-sm
                      transition-all duration-200
                      hover:-translate-y-0.5
                      hover:border-rose-200
                      hover:shadow-md hover:shadow-rose-100/50
                    "
                  >

                    <!-- Decorative glow -->
                    <div
                      class="
                        pointer-events-none absolute -right-8 -top-8
                        h-24 w-24 rounded-full
                        bg-rose-100/60
                        blur-2xl
                        transition-all duration-300
                        group-hover:bg-rose-200/60
                      "
                    ></div>


                    <div class="relative">

                      <!-- Header -->
                      <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2.5">

                          <div
                            class="
                              flex h-8 w-8 shrink-0 items-center justify-center
                              rounded-xl
                              bg-rose-100
                              text-rose-600
                            "
                          >
                            <i class="fas fa-arrow-trend-down text-xs"></i>
                          </div>

                          <div>
                            <p
                              class="
                                text-[10px] font-bold uppercase
                                tracking-[0.12em] text-rose-700
                              "
                            >
                              Lowest Category
                            </p>

                            <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                              Area needing attention
                            </p>
                          </div>

                        </div>


                        <!-- Score badge -->
                        <span
                          class="
                            inline-flex shrink-0 items-center gap-1
                            rounded-full
                            border border-rose-100
                            bg-white/80
                            px-2 py-1
                            text-[10px] font-semibold
                            text-rose-700
                          "
                        >
                          <i class="fas fa-arrow-down text-[9px] text-rose-500"></i>
                          Needs Focus
                        </span>

                      </div>


                      <!-- Category -->
                      <div class="mt-5">

                        <p
                          id="lowestCategory"
                          class="
                            truncate
                            text-base font-bold
                            tracking-tight
                            text-slate-900
                          "
                        >
                          —
                        </p>

                        <p class="mt-1 text-[11px] text-slate-400">
                          Lowest-rated evaluation category
                        </p>

                      </div>


                      <!-- Score -->
                      <div
                        class="
                          mt-4 flex items-end justify-between
                          border-t border-rose-100/70
                          pt-4
                        "
                      >

                        <div class="flex items-baseline gap-1.5">

                          <p
                            id="lowestScore"
                            class="
                              text-3xl font-bold
                              leading-none
                              tracking-tight
                              text-rose-700
                            "
                          >
                            —
                          </p>

                          <span class="text-xs font-medium text-slate-400">
                            / 5.00
                          </span>

                        </div>


                        <div
                          class="
                            flex h-8 w-8 items-center justify-center
                            rounded-full
                            bg-rose-100
                            text-rose-600
                          "
                        >
                          <i class="fas fa-exclamation text-xs"></i>
                        </div>

                      </div>


                      <!-- Bottom accent -->
                      <div class="mt-4 h-1 overflow-hidden rounded-full bg-rose-100">

                        <div
                          class="
                            h-full w-full rounded-full
                            bg-rose-500
                          "
                        ></div>

                      </div>

                    </div>

                  </div>

                </div>

              </div>

            </section>


            <!-- QUESTION GAP -->
            <section
              class="flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >

              <!-- Header -->
              <div
                class="flex items-center justify-between gap-3 border-b border-slate-100
                      px-5 py-5"
              >

                <div class="flex min-w-0 items-center gap-3">

                  <div
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg
                          bg-rose-50 text-rose-600"
                  >
                    <i class="fas fa-bullseye text-xs"></i>
                  </div>

                  <div class="min-w-0">
                    <h2 class="text-sm font-semibold text-slate-900">
                      Question Gap Analysis
                    </h2>

                    <p class="mt-1 truncate text-xs text-slate-400">
                      Highest vs. lowest rated evaluation questions
                    </p>
                  </div>

                </div>

                <span
                  class="inline-flex shrink-0 items-center whitespace-nowrap rounded-full
                        border border-rose-100 bg-rose-50 px-2.5 py-1
                        text-[10px] font-bold uppercase tracking-wide text-rose-700"
                >
                  Talking Points
                </span>

              </div>


              <!-- Content -->
              <div
                id="parentContainer"
                class="grid flex-1 min-h-0 grid-cols-1 gap-5 p-5 sm:grid-cols-2"
              >

                <!-- ==================================================
                    STRONGEST QUESTIONS
                =================================================== -->
                <div class="flex min-h-0 flex-col">

                  <!-- Section title -->
                  <div class="mb-3 flex shrink-0 items-center gap-2">

                    <div
                      class="flex h-6 w-6 items-center justify-center rounded-lg
                            bg-emerald-100 text-emerald-600"
                    >
                      <i class="fas fa-arrow-up text-[10px]"></i>
                    </div>

                    <p
                      class="text-[10px] font-bold uppercase tracking-wide text-emerald-700"
                    >
                      Strongest Questions
                    </p>

                  </div>


                  <!-- Fixed-height scroll area -->
                  <div
                    id="highestQuestions"
                    class="
                      flex
                      h-auto
                      max-h-[520px]
                      min-h-0
                      flex-col
                      gap-2
                      overflow-y-auto
                      overflow-x-hidden
                      rounded-xl
                      border
                      border-slate-100
                      bg-slate-50/40
                      p-2
                      pr-1
                    "
                  >

                    <!-- JS populated -->

                  </div>

                </div>


                <!-- ==================================================
                    NEEDS IMPROVEMENT
                =================================================== -->
                <div class="flex min-h-0 flex-col">

                  <!-- Section title -->
                  <div class="mb-3 flex shrink-0 items-center gap-2">

                    <div
                      class="flex h-6 w-6 items-center justify-center rounded-lg
                            bg-rose-100 text-rose-600"
                    >
                      <i class="fas fa-arrow-down text-[10px]"></i>
                    </div>

                    <p
                      class="text-[10px] font-bold uppercase tracking-wide text-rose-600"
                    >
                      Needs Improvement
                    </p>

                  </div>


                  <!-- Fixed-height scroll area -->
                  <div
                    id="lowestQuestions"
                    class="
                      flex
                      h-auto
                      max-h-[520px]
                      min-h-0
                      flex-col
                      gap-2
                      overflow-y-auto
                      overflow-x-hidden
                      rounded-xl
                      border
                      border-slate-100
                      bg-slate-50/40
                      p-2
                      pr-1

                      scrollbar-thin
                      scrollbar-track-transparent
                      scrollbar-thumb-slate-300
                      hover:scrollbar-thumb-slate-400
                    "
                  >

                    <!-- JS populated -->

                  </div>

                </div>

              </div>

            </section>

          </div>


          <!-- ======================================================
              EVALUATION MONITORING
          ======================================================= -->
          <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <!-- ====================================================
                MAIN SECTION HEADER
            ===================================================== -->
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

              <div class="flex items-start gap-3.5">

                <!-- Icon -->
                <div
                  class="
                    flex
                    h-10
                    w-10
                    shrink-0
                    items-center
                    justify-center
                    rounded-xl
                    border
                    border-violet-100
                    bg-violet-50
                    text-violet-600
                  "
                >
                  <i class="fas fa-chart-line text-sm"></i>
                </div>

                <!-- Heading -->
                <div class="min-w-0">

                  <div class="flex flex-wrap items-center gap-2">

                    <h2 class="text-base font-bold tracking-tight text-slate-900">
                      Evaluation Monitoring
                    </h2>

                    <span
                      class="
                        inline-flex
                        items-center
                        gap-1.5
                        rounded-full
                        border
                        border-slate-200
                        bg-slate-50
                        px-2
                        py-0.5
                        text-[9px]
                        font-semibold
                        uppercase
                        tracking-wider
                        text-slate-500
                      "
                    >
                      <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                      Monitoring Center
                    </span>

                  </div>

                  <p class="mt-1 text-xs leading-relaxed text-slate-500">
                    Monitor evaluation progress, participation, and performance across the institution.
                  </p>

                </div>

              </div>

            </div>


            <!-- ====================================================
                TABS
            ===================================================== -->
            <div class="border-b border-slate-100 px-5 sm:px-6">

              <div class="tabs flex items-center gap-1 overflow-x-auto">

                <!-- Ranking -->
                <button
                  class="tab-btn active whitespace-nowrap"
                  data-target="panel-ranking"
                >

                  <span class="inline-flex items-center gap-2">

                    <i class="fas fa-ranking-star text-[11px]"></i>

                    <span>Ranking</span>

                    <span
                      class="badge badge-blue"
                      id="cnt-ranking"
                    >
                      0
                    </span>

                  </span>

                </button>


                <!-- Not Evaluated -->
                <button
                  class="tab-btn whitespace-nowrap"
                  data-target="panel-not-evaluated"
                >

                  <span class="inline-flex items-center gap-2">

                    <i class="fas fa-hourglass-half text-[11px]"></i>

                    <span>Not Evaluated</span>

                    <span
                      class="badge badge-gray"
                      id="cnt-not-evaluated"
                    >
                      0
                    </span>

                  </span>

                </button>


                <!-- Abandoned -->
                <button
                  class="tab-btn whitespace-nowrap"
                  data-target="panel-abandoned"
                >

                  <span class="inline-flex items-center gap-2">

                    <i class="fas fa-circle-exclamation text-[11px]"></i>

                    <span>Abandoned</span>

                    <span
                      class="badge badge-red"
                      id="cnt-abandoned"
                    >
                      0
                    </span>

                  </span>

                </button>

              </div>

            </div>


            <!-- ====================================================
                RANKING
            ===================================================== -->
            <div
              id="panel-ranking"
              class="tab-panel active overflow-hidden p-5 sm:p-6"
            >

              <!-- Ranking Intro -->
              <div
                class="
                  mb-5
                  flex
                  flex-col
                  gap-4
                  lg:flex-row
                  lg:items-end
                  lg:justify-between
                "
              >

                <!-- Description -->
                <div class="min-w-0">

                  <div class="flex items-center gap-2">

                    <div
                      class="
                        flex
                        h-8
                        w-8
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        border
                        border-violet-100
                        bg-violet-50
                        text-violet-600
                      "
                    >
                      <i class="fas fa-chart-column text-[11px]"></i>
                    </div>

                    <div>

                      <h3 class="text-xs font-bold text-slate-800">
                        Performance Ranking
                      </h3>

                      <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                        Teacher performance based on evaluation results
                      </p>

                    </div>

                  </div>

                </div>


                <!-- Toolbar -->
                <div
                  class="
                    flex
                    w-full
                    flex-col
                    gap-2.5
                    sm:flex-row
                    lg:w-auto
                  "
                >

                  <!-- Search -->
                  <div class="relative w-full sm:w-64">

                    <div
                      class="
                        pointer-events-none
                        absolute
                        inset-y-0
                        left-0
                        flex
                        items-center
                        pl-3
                        text-slate-400
                      "
                    >
                      <i class="fas fa-search text-[11px]"></i>
                    </div>

                    <input
                      type="text"
                      id="search-ranking"
                      placeholder="Search teacher..."
                      class="
                        h-10
                        w-full
                        rounded-xl
                        border
                        border-slate-200
                        bg-slate-50
                        pl-9
                        pr-3
                        text-xs
                        font-medium
                        text-slate-700
                        outline-none
                        transition
                        placeholder:text-slate-400
                        focus:border-violet-400
                        focus:bg-white
                        focus:ring-4
                        focus:ring-violet-500/10
                      "
                    />

                  </div>


                  <!-- Export -->
                  <button
                    id="btn-export-ranking"
                    type="button"
                    class="
                      btn-export
                      group
                      inline-flex
                      h-10
                      w-full
                      items-center
                      justify-center
                      gap-2
                      rounded-xl
                      border
                      border-[#217346]
                      bg-[#217346]
                      px-4
                      text-xs
                      font-semibold
                      text-white
                      shadow-sm
                      shadow-[#217346]/20
                      transition-all
                      duration-200
                      hover:-translate-y-0.5
                      hover:border-[#185C37]
                      hover:bg-[#185C37]
                      hover:shadow-md
                      hover:shadow-[#217346]/25
                      focus:outline-none
                      focus:ring-2
                      focus:ring-[#217346]/20
                      active:translate-y-0
                      active:scale-[0.98]
                      sm:w-auto
                    "
                  >

                    <span
                      class="
                        inline-flex
                        h-6
                        w-6
                        items-center
                        justify-center
                        rounded-md
                        bg-white/15
                        text-white
                        transition-colors
                        duration-200
                        group-hover:bg-white/20
                      "
                    >
                      <i class="fas fa-file-excel text-[12px]"></i>
                    </span>

                    <span>Export to Excel</span>

                  </button>

                </div>

              </div>


              <!-- Ranking Table -->
              <div
                class="
                  overflow-hidden
                  rounded-xl
                  border
                  border-slate-200
                  bg-white
                  shadow-sm
                "
              >

                <!-- Table Context Header -->
                <div
                  class="
                    flex
                    items-center
                    justify-between
                    gap-3
                    border-b
                    border-slate-100
                    bg-slate-50/60
                    px-4
                    py-3
                    sm:px-5
                  "
                >

                  <div class="flex min-w-0 items-center gap-2.5">

                    <div
                      class="
                        flex
                        h-8
                        w-8
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        border
                        border-violet-100
                        bg-violet-50
                        text-violet-600
                      "
                    >
                      <i class="fas fa-ranking-star text-[11px]"></i>
                    </div>

                    <div class="min-w-0">

                      <p class="truncate text-xs font-bold text-slate-800">
                        Teacher Ranking
                      </p>

                      <p class="mt-0.5 hidden text-[10px] font-medium text-slate-400 sm:block">
                        Ranked according to evaluation performance
                      </p>

                    </div>

                  </div>


                  <div
                    class="
                      hidden
                      items-center
                      gap-1.5
                      rounded-full
                      border
                      border-emerald-100
                      bg-emerald-50
                      px-2.5
                      py-1
                      text-[9px]
                      font-semibold
                      uppercase
                      tracking-wider
                      text-emerald-600
                      sm:inline-flex
                    "
                  >
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Performance
                  </div>

                </div>


                <!-- Dynamic Table -->
                <div
                  class="
                    w-full
                    overflow-x-auto
                    overscroll-x-contain
                  "
                >

                  <table
                    id="tbl-ranking"
                    class="w-full min-w-[850px] text-left text-sm"
                  >
                  </table>

                </div>

              </div>

            </div>


            <!-- ====================================================
                NOT EVALUATED
            ===================================================== -->
            <div
              id="panel-not-evaluated"
              class="tab-panel hidden overflow-hidden p-5 sm:p-6"
            >

              <!-- Intro + Toolbar -->
              <div
                class="
                  mb-5
                  flex
                  flex-col
                  gap-4
                  lg:flex-row
                  lg:items-end
                  lg:justify-between
                "
              >

                <!-- Description -->
                <div class="min-w-0">

                  <div class="flex items-center gap-2.5">

                    <div
                      class="
                        flex
                        h-9
                        w-9
                        shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        border
                        border-amber-100
                        bg-amber-50
                        text-amber-600
                      "
                    >
                      <i class="fas fa-hourglass-half text-xs"></i>
                    </div>

                    <div>

                      <h3 class="text-xs font-bold text-slate-800">
                        Pending Participation
                      </h3>

                      <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                        Students who still need to complete their evaluation
                      </p>

                    </div>

                  </div>

                </div>


                <!-- Toolbar -->
                <div
                  class="
                    flex
                    w-full
                    flex-col
                    gap-2.5
                    sm:flex-row
                    lg:w-auto
                  "
                >

                  <!-- Search -->
                  <div class="toolbar-search w-full sm:w-64">

                    <svg
                      viewBox="0 0 24 24"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      aria-hidden="true"
                    >
                      <circle cx="11" cy="11" r="8"/>
                      <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                    </svg>

                    <input
                      type="text"
                      id="search-not-evaluated"
                      placeholder="Search student…"
                    />

                  </div>


                  <!-- Notify -->
                  <button
                    class="
                      btn-notify-all
                      inline-flex
                      h-10
                      w-full
                      items-center
                      justify-center
                      gap-2
                      rounded-xl
                      sm:w-auto
                    "
                    id="btn-notify-all"
                  >

                    <svg
                      viewBox="0 0 16 16"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="1.5"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      class="h-4 w-4"
                      aria-hidden="true"
                    >
                      <rect x="1" y="3" width="14" height="10" rx="1.5"/>
                      <path d="m1 4 7 5 7-5"/>
                    </svg>

                    <span>Notify All</span>

                  </button>

                </div>

              </div>


              <!-- Pending Table -->
              <div
                class="
                  overflow-hidden
                  rounded-xl
                  border
                  border-slate-200
                  bg-white
                  shadow-sm
                "
              >

                <!-- Context Header -->
                <div
                  class="
                    flex
                    items-center
                    justify-between
                    gap-3
                    border-b
                    border-slate-100
                    bg-slate-50/60
                    px-4
                    py-3
                    sm:px-5
                  "
                >

                  <div class="flex min-w-0 items-center gap-2.5">

                    <div
                      class="
                        flex
                        h-8
                        w-8
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        border
                        border-amber-100
                        bg-amber-50
                        text-amber-600
                      "
                    >
                      <i class="fas fa-user-clock text-[11px]"></i>
                    </div>

                    <div class="min-w-0">

                      <p class="truncate text-xs font-bold text-slate-800">
                        Students Awaiting Evaluation
                      </p>

                      <p class="mt-0.5 hidden text-[10px] font-medium text-slate-400 sm:block">
                        Monitor outstanding student participation
                      </p>

                    </div>

                  </div>


                  <span
                    class="
                      hidden
                      items-center
                      gap-1.5
                      rounded-full
                      border
                      border-amber-100
                      bg-amber-50
                      px-2.5
                      py-1
                      text-[9px]
                      font-semibold
                      uppercase
                      tracking-wider
                      text-amber-600
                      sm:inline-flex
                    "
                  >
                    <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                    Pending
                  </span>

                </div>


                <!-- Dynamic Table -->
                <div class="w-full overflow-x-auto overscroll-x-contain">

                  <table
                    id="tbl-not-evaluated"
                    class="w-full min-w-[750px]"
                  >
                  </table>

                </div>

              </div>

            </div>


            <!-- ====================================================
                ABANDONED
            ===================================================== -->
            <div
              id="panel-abandoned"
              class="tab-panel hidden overflow-hidden p-5 sm:p-6"
            >

              <!-- Intro + Toolbar -->
              <div
                class="
                  mb-5
                  flex
                  flex-col
                  gap-4
                  lg:flex-row
                  lg:items-end
                  lg:justify-between
                "
              >

                <!-- Description -->
                <div class="min-w-0">

                  <div class="flex items-center gap-2.5">

                    <div
                      class="
                        flex
                        h-9
                        w-9
                        shrink-0
                        items-center
                        justify-center
                        rounded-xl
                        border
                        border-rose-100
                        bg-rose-50
                        text-rose-600
                      "
                    >
                      <i class="fas fa-circle-exclamation text-xs"></i>
                    </div>

                    <div>

                      <h3 class="text-xs font-bold text-slate-800">
                        Incomplete Evaluations
                      </h3>

                      <p class="mt-0.5 text-[10px] font-medium text-slate-400">
                        Evaluations that were started but not completed
                      </p>

                    </div>

                  </div>

                </div>


                <!-- Search -->
                <div class="toolbar-search w-full sm:w-64">

                  <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                  >
                    <circle cx="11" cy="11" r="8"/>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                  </svg>

                  <input
                    type="text"
                    id="search-abandoned"
                    placeholder="Search student…"
                  />

                </div>

              </div>


              <!-- Abandoned Table -->
              <div
                class="
                  overflow-hidden
                  rounded-xl
                  border
                  border-slate-200
                  bg-white
                  shadow-sm
                "
              >

                <!-- Context Header -->
                <div
                  class="
                    flex
                    items-center
                    justify-between
                    gap-3
                    border-b
                    border-slate-100
                    bg-slate-50/60
                    px-4
                    py-3
                    sm:px-5
                  "
                >

                  <div class="flex min-w-0 items-center gap-2.5">

                    <div
                      class="
                        flex
                        h-8
                        w-8
                        shrink-0
                        items-center
                        justify-center
                        rounded-lg
                        border
                        border-rose-100
                        bg-rose-50
                        text-rose-600
                      "
                    >
                      <i class="fas fa-rotate-left text-[11px]"></i>
                    </div>

                    <div class="min-w-0">

                      <p class="truncate text-xs font-bold text-slate-800">
                        Abandoned Evaluations
                      </p>

                      <p class="mt-0.5 hidden text-[10px] font-medium text-slate-400 sm:block">
                        Review evaluations that remain incomplete
                      </p>

                    </div>

                  </div>


                  <span
                    class="
                      hidden
                      items-center
                      gap-1.5
                      rounded-full
                      border
                      border-rose-100
                      bg-rose-50
                      px-2.5
                      py-1
                      text-[9px]
                      font-semibold
                      uppercase
                      tracking-wider
                      text-rose-600
                      sm:inline-flex
                    "
                  >
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    Incomplete
                  </span>

                </div>


                <!-- Dynamic Table -->
                <div class="w-full overflow-x-auto overscroll-x-contain">

                  <table
                    id="tbl-abandoned"
                    class="w-full min-w-[750px]"
                  >
                  </table>

                </div>

              </div>

            </div>

          </section>

        </div>


        <!-- ========================================================
            HISTORICAL PERIOD BANNER
        ========================================================= -->
        <div
          id="historical-banner"
          class="fixed bottom-0 left-0 right-0 z-40 hidden px-4 pb-4"
        >

          <div class="mx-auto max-w-5xl">

            <div
              class="flex flex-col gap-4 rounded-2xl border border-violet-400/20
                    bg-[#0F172A] px-4 py-4 shadow-2xl shadow-slate-900/20
                    sm:flex-row sm:items-center sm:justify-between sm:px-5"
            >

              <!-- Left -->
              <div class="flex min-w-0 items-start gap-3 sm:items-center">

                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                        border border-violet-400/20 bg-violet-500/10 text-violet-300"
                >
                  <i class="fas fa-clock text-xs"></i>
                </div>


                <div class="min-w-0">

                  <div class="flex flex-wrap items-center gap-2">

                    <p class="text-xs font-semibold text-white">
                      Viewing Historical Period
                    </p>

                    <span
                      class="inline-flex items-center gap-1.5 rounded-full border
                            border-violet-400/20 bg-violet-500/10 px-2 py-0.5
                            text-[10px] font-semibold text-violet-300"
                    >
                      <span class="h-1.5 w-1.5 animate-pulse rounded-full bg-violet-400"></span>
                      Read-only
                    </span>

                  </div>

                  <p class="mt-1 text-xs leading-relaxed text-slate-400">
                    You are viewing a past evaluation — data shown is not live.
                  </p>


                  <!-- Mobile period -->
                  <div class="mt-2 flex items-center gap-1.5 sm:hidden">

                    <i class="fas fa-calendar-alt text-[10px] text-slate-500"></i>

                    <span
                      class="text-xs text-slate-400"
                      id="banner-period-label-mobile"
                    >
                      —
                    </span>

                  </div>

                </div>

              </div>


              <!-- Right -->
              <div class="flex w-full items-center gap-2 sm:w-auto">

                <!-- Desktop Period -->
                <div
                  class="hidden items-center gap-1.5 rounded-xl border border-slate-700
                        bg-slate-800 px-3 py-2 sm:flex"
                >

                  <i class="fas fa-calendar-alt text-[10px] text-slate-500"></i>

                  <span
                    class="text-xs text-slate-300"
                    id="banner-period-label"
                  >
                    —
                  </span>

                </div>


                <!-- Return -->
                <button
                  id="btn-return-current"
                  class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl
                        border border-violet-400/30 bg-violet-600 px-4 py-2
                        text-xs font-semibold text-white transition-all duration-200
                        hover:bg-violet-500 active:scale-[.98] sm:flex-none"
                >

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

</body>

<script> 
window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script> 

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="<?= BASE_URL ?>assets/js/admin/report_analytics/index.js" type="module"></script>
</html>