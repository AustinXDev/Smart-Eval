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

$pageTitle = "Manage Students";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students <?php echo strtoupper($department); ?></title>

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
<body>

  <!-- Wrapper -->
  <div class="max-w-screen-2xl h-dvh mx-auto flex  relative"> 

    <!-- header -->
    <?php require __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full bg-slate-50 pt-15 lg:ml-70">
      <div class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-[1800px] px-5 py-5 sm:px-6 lg:px-8">
          <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-center gap-3">
              <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                </svg>
              </div>

              <div>
                <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                  <?= htmlspecialchars(ucfirst($department)) ?> Students
                </h1>

                <p class="mt-0.5 text-xs font-medium text-slate-500">
                  Manage and organize registered student information
                </p>
              </div>
            </div>

            <div class="flex w-full flex-col gap-2.5 sm:flex-row xl:w-auto">
              <button
                type="button"
                class="add-btn inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/15 active:translate-y-0 active:scale-[0.98]"
              >
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/15">
                  <i class="fas fa-plus text-[11px]"></i>
                </span>
                <span class="whitespace-nowrap">Add Student</span>
              </button>

              <button
                type="button"
                class="csv-btn inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/50 hover:text-slate-900 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-500/10 active:translate-y-0 active:scale-[0.98]"
              >
                <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-50">
                  <i class="fas fa-file-csv text-[11px] text-emerald-600"></i>
                </span>
                <span class="whitespace-nowrap">Import CSV</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">

      <!-- =========================================================
          STATISTICS
      ========================================================== -->
      <div
        id="card-container"
        class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
      >

        <!-- Total Students -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-100">

              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 640"
                fill="currentColor"
              >
                <path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/>
              </svg>

            </div>

            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Total Students
              </p>

              <p
                id="total-students"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <div class="hidden h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 sm:flex">
              <i class="fas fa-users text-xs"></i>
            </div>

          </div>

        </div>


        <!-- Active Students -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100">

              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 640"
                fill="currentColor"
              >
                <path d="M286 368C384.5 368 464.3 447.8 464.3 546.3C464.3 562.7 451 576 434.6 576L78 576C61.6 576 48.3 562.7 48.3 546.3C48.3 447.8 128.1 368 226.6 368L286 368zM585.7 169.9C593.5 159.2 608.5 156.8 619.2 164.6C629.9 172.4 632.3 187.4 624.5 198.1L522.1 338.9C517.9 344.6 511.4 348.3 504.4 348.7C497.4 349.1 490.4 346.5 485.5 341.4L439.1 293.4C429.9 283.9 430.1 268.7 439.7 259.5C449.2 250.3 464.4 250.6 473.6 260.1L500.1 287.5L585.7 169.8zM256.3 312C190 312 136.3 258.3 136.3 192C136.3 125.7 190 72 256.3 72C322.6 72 376.3 125.7 376.3 192C376.3 258.3 322.6 312 256.3 312z"/>
              </svg>

            </div>

            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Active Students
              </p>

              <p
                id="total-active"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Active
            </span>

          </div>

        </div>


        <!-- Inactive Students -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500 transition-colors group-hover:bg-red-100">

              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 640"
                fill="currentColor"
              >
                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 405.8 61.8 344 138.3 344l91.4 0C306.2 344 368 405.8 368 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM484.7 246.7L440.1 202c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l84.7 84.7c9.4 9.4 9.4 24.6 0 33.9l-84.7 84.7c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l44.6-44.7L416 292.7c-13.3 0-24-10.7-24-24s10.7-24 24-24l68.7 0z"/>
              </svg>

            </div>

            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Inactive Students
              </p>

              <p
                id="total-inactive"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600">
              <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
              Inactive
            </span>

          </div>

        </div>

      </div>


      <!-- =========================================================
          STUDENT DIRECTORY
      ========================================================== -->
      <section
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
      >

        <!-- =======================================================
            TABLE HEADER
        ======================================================== -->
        <div class="border-b border-slate-200 bg-white px-5 py-5 sm:px-6">

          <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <!-- Section title -->
            <div class="flex items-center gap-3">

              <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                <i class="fas fa-users text-sm"></i>
              </div>

              <div>

                <h2 class="text-sm font-semibold text-slate-900 sm:text-base">
                  Student Directory
                </h2>

                <p class="mt-0.5 text-xs text-slate-400">
                  View and manage registered students
                </p>

              </div>

            </div>


            <!-- Filter -->
            <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-center lg:w-auto">

              <!-- Search -->
              <div class="relative w-full sm:w-[220px]">

                <div
                  class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-slate-400"
                >
                  <i class="fas fa-search text-[11px]"></i>
                </div>

                <input
                  id="searchBox"
                  type="search"
                  placeholder="Search programs..."
                  class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 text-sm font-medium text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                />

              </div>

              <div class="relative w-full sm:w-[220px]">

                <select
                  id="courseFilter"
                  class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm font-medium text-slate-700 outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                >
                  <option value="All">All</option>
                </select>

                <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">
                  <i class="fas fa-chevron-down text-[10px]"></i>
                </div>

              </div>

            </div>

          </div>

        </div>


        <!-- =======================================================
            TABLE
        ======================================================== -->
        <div
          id="tableWrapper"
          class="w-full overflow-x-auto"
          data-department="<?php echo htmlspecialchars($department); ?>"
        >

          <table
            id="studentsTable"
            class="w-full min-w-[850px] text-left text-sm"
          >

            <!-- Table Head -->
            <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">

              <tr>

                <th class="whitespace-nowrap px-5 py-3.5 font-semibold">
                  Student ID
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Student Name
                </th>

                <th class="whitespace-nowrap px-5 py-3.5 font-semibold">
                  Department
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Course
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Status
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Actions
                </th>

              </tr>

            </thead>


            <!-- Table Body -->
            <tbody id="studentsTableBody" class="divide-y divide-slate-100">

              <!-- LoadData Function fills this section -->

            </tbody>

          </table>

        </div>


        <!-- =======================================================
            TABLE FOOTER
        ======================================================== -->
        <div class="border-t border-slate-100 bg-slate-50/50 px-5 py-3 sm:px-6">

          <div class="flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between">

            <span>
              Student records
            </span>

            <span class="inline-flex items-center gap-1.5">
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Data synced with Smart-Eval
            </span>

          </div>

        </div>

      </section>

    </main>

    <!-- Modal Content -->
    <?php require_once __DIR__ . '/../partials/modals/students_modal/add_student_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/import_csv_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/summary_report_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/loader_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/view_student_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/edit_student_modal.php'; ?>

  </div>

<script> 
window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script> 

<script src="<?= BASE_URL ?>assets/js/admin/students/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/students/index.js" type="module"></script> 
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>

</body>
</html>