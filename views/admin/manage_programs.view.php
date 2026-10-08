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

$pageTitle = "Manage Programs";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Programs</title>

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
<body class="pb-6">

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
                <i class="fas fa-layer-group text-base text-white"></i>
              </div>

              <div>
                <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                  Program Management
                </h1>

                <p class="mt-0.5 text-xs font-medium text-slate-500">
                  Manage and organize registered academic programs
                </p>
              </div>
            </div>

            <button
              type="button"
              class="addProgram inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/15 active:translate-y-0 active:scale-[0.98]"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/15">
                <i class="fas fa-plus text-[11px]"></i>
              </span>

              <span class="whitespace-nowrap">
                Add Program
              </span>
            </button>

          </div>

        </div>

      </div>

      <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">

        <!-- =========================================================
            PROGRAM STATISTICS
        ========================================================== -->
        <div
          id="card-container"
          class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
        >

          <!-- Total Programs -->
          <div
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md"
          >

            <div class="flex items-center gap-4">

              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600 transition-colors group-hover:bg-violet-100"
              >
                <i class="fas fa-layer-group text-lg"></i>
              </div>

              <div class="min-w-0 flex-1">

                <p
                  class="text-xs font-medium uppercase tracking-wide text-slate-400"
                >
                  Total Programs
                </p>

                <p
                  id="total-programs"
                  class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
                >
                  0
                </p>

              </div>

              <div
                class="hidden h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 sm:flex"
              >
                <i class="fas fa-list text-xs"></i>
              </div>

            </div>

          </div>



          <!-- Active Programs -->
          <div
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md"
          >

            <div class="flex items-center gap-4">

              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100"
              >
                <i class="fas fa-check-circle text-lg"></i>
              </div>

              <div class="min-w-0 flex-1">

                <p
                  class="text-xs font-medium uppercase tracking-wide text-slate-400"
                >
                  Active Programs
                </p>

                <p
                  id="total-active"
                  class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
                >
                  0
                </p>

              </div>

              <span
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[10px] font-semibold text-emerald-700"
              >
                <span
                  class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                ></span>

                Active
              </span>

            </div>

          </div>



          <!-- Inactive Programs -->
          <div
            class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-red-200 hover:shadow-md"
          >

            <div class="flex items-center gap-4">

              <div
                class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-50 text-red-500 transition-colors group-hover:bg-red-100"
              >
                <i class="fas fa-ban text-lg"></i>
              </div>

              <div class="min-w-0 flex-1">

                <p
                  class="text-xs font-medium uppercase tracking-wide text-slate-400"
                >
                  Inactive Programs
                </p>

                <p
                  id="total-inactive"
                  class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
                >
                  0
                </p>

              </div>

              <span
                class="inline-flex items-center gap-1.5 rounded-full bg-red-50 px-2.5 py-1 text-[10px] font-semibold text-red-600"
              >
                <span
                  class="h-1.5 w-1.5 rounded-full bg-red-500"
                ></span>

                Inactive
              </span>

            </div>

          </div>

        </div>



        <!-- =========================================================
            PROGRAM DIRECTORY
        ========================================================== -->
        <section
          class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >

          <!-- =======================================================
              SECTION HEADER / TOOLBAR
          ======================================================== -->
          <div
            class="border-b border-slate-200 bg-white px-5 py-5 sm:px-6"
          >

            <div
              class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between"
            >

              <!-- Section title -->
              <div class="flex items-center gap-3">

                <div
                  class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
                >
                  <i class="fas fa-graduation-cap text-sm"></i>
                </div>

                <div>

                  <h2
                    class="text-sm font-semibold text-slate-900 sm:text-base"
                  >
                    Program Directory
                  </h2>

                  <p class="mt-0.5 text-xs text-slate-400">
                    View and manage registered academic programs
                  </p>

                </div>

              </div>



              <!-- Filters -->
              <div
                class="flex w-full flex-col gap-2 sm:flex-row lg:w-auto"
              >

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



                <!-- Department Filter -->
                <div class="relative w-full sm:w-[160px]">

                  <select
                    id="departmentFilter"
                    class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm font-medium text-slate-700 outline-none transition focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                  >

                    <option value="All">
                      All Departments
                    </option>

                    <option value="College">
                      College
                    </option>

                    <option value="SHS">
                      SHS
                    </option>

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



          <!-- =======================================================
              TABLE
          ======================================================== -->
          <div
            id="tableWrapper"
            class="w-full overflow-x-auto"
          >

            <table
              id="programTable"
              class="w-full min-w-[850px] text-left text-sm"
            >

              <!-- Table Head -->
              <thead
                class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500"
              >

                <tr>

                  <th
                    class="whitespace-nowrap px-5 py-3.5 font-semibold"
                  >
                    Program Code
                  </th>

                  <th
                    class="whitespace-nowrap px-5 py-3.5 font-semibold"
                  >
                    Program Name
                  </th>

                  <th
                    class="whitespace-nowrap px-5 py-3.5 font-semibold"
                  >
                    Department
                  </th>

                  <th
                    class="text-center whitespace-nowrap px-5 py-3.5 font-semibold"
                  >
                    Status
                  </th>

                  <th
                    class="text-center whitespace-nowrap px-5 py-3.5  font-semibold"
                  >
                    Actions
                  </th>

                </tr>

              </thead>



              <!-- Table Body -->
              <tbody
                id="programTableBody"
                class="divide-y divide-slate-100"
              >

                <!-- JS fills this -->

              </tbody>

            </table>

          </div>



          <!-- =======================================================
              TABLE FOOTER
          ======================================================== -->
          <div
            class="border-t border-slate-100 bg-slate-50/50 px-5 py-3 sm:px-6"
          >

            <div
              class="flex flex-col gap-2 text-xs text-slate-400 sm:flex-row sm:items-center sm:justify-between"
            >

              <span>
                Program records
              </span>

              <span class="inline-flex items-center gap-1.5">

                <span
                  class="h-1.5 w-1.5 rounded-full bg-emerald-500"
                ></span>

                Data synced with Smart-Eval

              </span>

            </div>

          </div>

        </section>

      </div>

    </main>

    <!-- Modal Content -->
    <?php require __DIR__ . '/../partials/modals/programs_modal/add_program_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/programs_modal/confirmation_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/programs_modal/edit_program_modal.php'; ?>

  </div>

  <script> 
  window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
  window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script> 


  <script src="<?= BASE_URL ?>assets/js/admin/manage_program/index.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>