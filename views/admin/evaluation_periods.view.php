<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\middleware\AdminAuthMiddleware;
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

$pageTitle = "Manage Evaluation";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluation Periods</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <!-- Modal JS -->
  <script src="<?= BASE_URL ?>assets/js/modal/modal.js" type="module"></script>

</head>
<body class="">

  <!-- Wrapper -->
  <div class="max-w-screen-2xl h-dvh mx-auto flex relative"> 

    <!-- header -->
    <?php require_once __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full bg-slate-50 pt-15 lg:ml-70">
      <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1800px] px-5 py-5 sm:px-6 lg:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                <i class="fas fa-clipboard-check text-base text-white"></i>
              </div>

              <div>
                <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                  Manage Evaluation
                </h1>

                <p class="mt-0.5 text-xs font-medium text-slate-500">
                  Manage and organize evaluation periods
                </p>
              </div>
            </div>

            <button
              type="button"
              class="createPeriodBtn inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/15 active:translate-y-0 active:scale-[0.98]"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/15">
                <i class="fas fa-plus text-[11px]"></i>
              </span>

              <span class="whitespace-nowrap">
                Create New Period
              </span>
            </button>
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">

        <!-- =========================================================
            SUMMARY CARDS
        ========================================================== -->
        <div class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">

          <!-- COLLEGE CARD -->
          <div
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md"
          >

            <div class="space-y-6">

              <!-- HEADER -->
              <div class="flex items-start justify-between gap-4">

                <div class="flex items-start gap-4">

                  <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-100"
                  >
                    <i class="fas fa-university text-lg"></i>
                  </div>

                  <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                      College
                    </p>

                    <h2 class="mt-1 text-base font-bold text-slate-900">
                      Active Period
                    </h2>

                    <p id="collegeYear" class="mt-1 text-sm font-semibold text-violet-600">
                      --
                    </p>

                    <p id="collegeSem" class="mt-0.5 text-xs text-slate-500">
                      --
                    </p>
                  </div>

                </div>

                <!-- STATUS -->
                <span
                  id="collegeStatus"
                  class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                  No Active
                </span>

              </div>


              <!-- PROGRESS SECTION -->
              <div>

                <div class="mb-2 flex items-center justify-between">

                  <div>
                    <p class="text-xs font-medium text-slate-500">
                      Student Completion
                    </p>

                    <p id="collegeProgressCount" class="mt-1 text-xs text-slate-400">
                      0 / 0 Students
                    </p>
                  </div>

                  <p id="collegeProgressText" class="text-lg font-bold tracking-tight text-slate-900">
                    0%
                  </p>

                </div>

                <!-- PROGRESS BAR -->
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                  <div
                    id="collegeProgressBar"
                    class="h-full rounded-full bg-violet-500 transition-all duration-700 ease-out"
                    style="width:0%"
                  ></div>
                </div>

              </div>

            </div>
          </div>


          <!-- SHS CARD -->
          <div
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-cyan-200 hover:shadow-md"
          >

            <div class="space-y-6">

              <!-- HEADER -->
              <div class="flex items-start justify-between gap-4">

                <div class="flex items-start gap-4">

                  <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600 transition-colors group-hover:bg-cyan-100"
                  >
                    <i class="fas fa-school text-lg"></i>
                  </div>

                  <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                      Senior High School
                    </p>

                    <h2 class="mt-1 text-base font-bold text-slate-900">
                      Active Period
                    </h2>

                    <p id="shsYear" class="mt-1 text-sm font-semibold text-cyan-600">
                      --
                    </p>

                    <p id="shsSem" class="mt-0.5 text-xs text-slate-500">
                      --
                    </p>
                  </div>

                </div>

                <!-- STATUS -->
                <span
                  id="shsStatus"
                  class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-semibold text-slate-500"
                >
                  <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                  No Active
                </span>

              </div>


              <!-- PROGRESS SECTION -->
              <div>

                <div class="mb-2 flex items-center justify-between">

                  <div>
                    <p class="text-xs font-medium text-slate-500">
                      Student Completion
                    </p>

                    <p id="shsProgressCount" class="mt-1 text-xs text-slate-400">
                      0 / 0 Students
                    </p>
                  </div>

                  <p id="shsProgressText" class="text-lg font-bold tracking-tight text-slate-900">
                    0%
                  </p>

                </div>

                <!-- PROGRESS BAR -->
                <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                  <div
                    id="shsProgressBar"
                    class="h-full rounded-full bg-cyan-500 transition-all duration-700 ease-out"
                    style="width:0%"
                  ></div>
                </div>

              </div>

            </div>
          </div>

        </div>



        <!-- =========================================================
            EVALUATION PERIODS
        ========================================================== -->
        <section
          class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

          <!-- SECTION HEADER / TOOLBAR -->
          <div class="border-b border-slate-200 bg-white px-5 py-5 sm:px-6">

            <div
              class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
            >

              <!-- Section title -->
              <div class="flex items-center gap-3">

                <div
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                >
                  <i class="fas fa-calendar-days text-sm"></i>
                </div>

                <div>
                  <h2 class="text-sm font-semibold text-slate-900 sm:text-base">
                    Evaluation Periods
                  </h2>

                  <p class="mt-0.5 text-xs text-slate-400">
                    View and manage all evaluation periods
                  </p>
                </div>

              </div>

              <!-- Filters -->
              <div class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto">

                <!-- Status Filter -->
                <div class="relative w-full sm:w-[180px]">

                  <select
                    id="statusFilter"
                    class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm font-medium text-slate-700 outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                  >
                    <option value="All">Show All</option>
                    <option value="Active">Active</option>
                    <option value="Archived">Archived</option>
                    <option value="Upcoming">Upcoming</option>
                  </select>

                  <div
                    class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400"
                  >
                    <i class="fas fa-chevron-down text-[10px]"></i>
                  </div>

                </div>

              </div>

            </div>

          </div>


          <!-- TABLE -->
          <div id="tableWrapper" class="w-full overflow-x-auto">

            <table id="evaluationTable" class="w-full min-w-[750px] text-left text-sm">

              <thead
                class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500"
              >
                <tr>
                  <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Academic Year</th>
                  <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Semester</th>
                  <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Status</th>
                  <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Restriction</th>
                  <th class="whitespace-nowrap px-5 py-3.5 text-center font-semibold">Actions</th>
                </tr>
              </thead>

              <tbody id="evaluationTableBody" class="divide-y divide-slate-100">
                <!-- JS will render rows here -->
              </tbody>

            </table>

          </div>


          <!-- TABLE FOOTER -->
          <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3 sm:px-6">

            <div
              class="flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between"
            >
              <span>Evaluation period records</span>

              <span class="inline-flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Data synced with Smart-Eval
              </span>
            </div>

          </div>

        </section>

      </div>

    </main>

    <!-- Modal Content -->
    <?php require_once __DIR__ . '/../partials/modals/periods/create_evaluation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/periods/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/periods/edit_evaluation_modal.php'; ?>

  </div>

  <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script> 

<script src="<?= BASE_URL ?>assets/js/admin/evaluation_periods/index.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>