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

$pageTitle = "Manage Teachers";
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Teachers <?php echo strtoupper($department); ?></title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'?>

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
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.118a7.5 7.5 0 0 1 15 0A17.933 17.933 0 0 1 12 21.75a17.933 17.933 0 0 1-7.5-1.632Z"/>
                </svg>
              </div>

              <div>
                <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                  <?= htmlspecialchars(ucfirst($department)) ?> Teachers
                </h1>

                <p class="mt-0.5 text-xs font-medium text-slate-500">
                  Manage and organize registered teacher information
                </p>
              </div>
            </div>

            <button
              type="button"
              value=""
              class="add-btn inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/15 active:translate-y-0 active:scale-[0.98]"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/15">
                <i class="fas fa-plus text-[11px]"></i>
              </span>
              <span class="whitespace-nowrap">Add Teacher</span>
            </button>
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">

      <!-- =========================================================
          STATISTICS
      ========================================================== -->
      <div
        id="card-container"
        data-department="<?php echo htmlspecialchars($department); ?>"
        class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
      >

        <!-- =======================================================
            TOTAL TEACHERS
        ======================================================== -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                transition-all duration-200
                hover:-translate-y-0.5
                hover:border-violet-200
                hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <!-- Icon -->
            <div
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                    bg-violet-50 text-violet-600
                    transition-colors group-hover:bg-violet-100"
            >
              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 640"
                fill="currentColor"
              >
                <path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/>
              </svg>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Total Teachers
              </p>

              <p
                id="total-teachers"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <!-- Secondary Icon -->
            <div class="hidden h-8 w-8 items-center justify-center rounded-lg bg-slate-50 text-slate-400 sm:flex">
              <i class="fas fa-chalkboard-teacher text-xs"></i>
            </div>

          </div>

        </div>


        <!-- =======================================================
            ACTIVE TEACHERS
        ======================================================== -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                transition-all duration-200
                hover:-translate-y-0.5
                hover:border-emerald-200
                hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <!-- Icon -->
            <div
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                    bg-emerald-50 text-emerald-600
                    transition-colors group-hover:bg-emerald-100"
            >
              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 640 640"
                fill="currentColor"
              >
                <path d="M286 368C384.5 368 464.3 447.8 464.3 546.3C464.3 562.7 451 576 434.6 576L78 576C61.6 576 48.3 562.7 48.3 546.3C48.3 447.8 128.1 368 226.6 368L286 368zM585.7 169.9C593.5 159.2 608.5 156.8 619.2 164.6C629.9 172.4 632.3 187.4 624.5 198.1L522.1 338.9C517.9 344.6 511.4 348.3 504.4 348.7C497.4 349.1 490.4 346.5 485.5 341.4L439.1 293.4C429.9 283.9 430.1 268.7 439.7 259.5C449.2 250.3 464.4 250.6 473.6 260.1L500.1 287.5L585.7 169.8zM256.3 312C190 312 136.3 258.3 136.3 192C136.3 125.7 190 72 256.3 72C322.6 72 376.3 125.7 376.3 192C376.3 258.3 322.6 312 256.3 312z"/>
              </svg>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Active Teachers
              </p>

              <p
                id="total-active"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <!-- Status -->
            <span
              class="inline-flex items-center gap-1.5 rounded-full
                    bg-emerald-50 px-2.5 py-1
                    text-[10px] font-semibold text-emerald-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Active
            </span>

          </div>

        </div>


        <!-- =======================================================
            INACTIVE TEACHERS
        ======================================================== -->
        <div
          class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm
                transition-all duration-200
                hover:-translate-y-0.5
                hover:border-red-200
                hover:shadow-md"
        >

          <div class="flex items-center gap-4">

            <!-- Icon -->
            <div
              class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl
                    bg-red-50 text-red-500
                    transition-colors group-hover:bg-red-100"
            >
              <svg
                class="h-6 w-6"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.625 6c-.54 0-.828.419-.936.634a1.96 1.96 0 0 0-.189.866c0 .298.059.605.189.866.108.215.395.634.936.634.54 0 .828-.419.936-.634.13-.26.189-.568-.189-.866-.108-.215-.395-.634-.936-.634Zm4.314.634c.108-.215.395-.634.936-.634.54 0 .828.419.936.634.13.26.189.568.189.866 0 .298-.059.605-.189.866-.108.215-.395.634-.936.634-.54 0-.828-.419-.936-.634a1.96 1.96 0 0 1-.189-.866c0-.298.059-.605.189-.866Zm-4.34 7.964a.75.75 0 0 1-1.061-1.06 5.236 5.236 0 0 1 3.73-1.538 5.236 5.236 0 0 1 3.695 1.538.75.75 0 1 1-1.061 1.06 3.736 3.736 0 0 0-2.639-1.098 3.736 3.736 0 0 0-2.664 1.098Z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">

              <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
                Inactive Teachers
              </p>

              <p
                id="total-inactive"
                class="mt-1 text-2xl font-bold tracking-tight text-slate-900"
              ></p>

            </div>

            <!-- Status -->
            <span
              class="inline-flex items-center gap-1.5 rounded-full
                    bg-red-50 px-2.5 py-1
                    text-[10px] font-semibold text-red-600"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
              Inactive
            </span>

          </div>

        </div>

      </div>


      <!-- =========================================================
          TEACHER DIRECTORY
      ========================================================== -->
      <section
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
      >

        <!-- =======================================================
            TABLE HEADER
        ======================================================== -->
        <div class="border-b border-slate-200 bg-white px-5 py-5 sm:px-6">

          <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">

            <!-- Section Title -->
            <div class="flex items-center gap-3">

              <div
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                      bg-violet-50 text-violet-600"
              >
                <i class="fas fa-chalkboard-teacher text-sm"></i>
              </div>

              <div>

                <h2 class="text-sm font-semibold text-slate-900 sm:text-base">
                  Teacher Directory
                </h2>

                <p class="mt-0.5 text-xs text-slate-400">
                  View and manage registered teachers
                </p>

              </div>

            </div>


            <!-- ===================================================
                STATUS FILTER
            ==================================================== -->
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
                  id="statusFilter"
                  class="h-10 w-full cursor-pointer appearance-none rounded-xl
                        border border-slate-200
                        bg-slate-50
                        px-3 pr-9
                        text-sm font-medium text-slate-700
                        outline-none
                        transition
                        hover:border-slate-300
                        focus:border-violet-400
                        focus:bg-white
                        focus:ring-4 focus:ring-violet-500/10"
                >

                  <option value="All">
                    All
                  </option>

                  <option value="Active">
                    Active
                  </option>

                  <option value="Inactive">
                    Inactive
                  </option>

                </select>

                <!-- Custom Arrow -->
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
          data-department="<?php echo htmlspecialchars($department); ?>"
        >

          <table
            id="teachersTable"
            class="w-full min-w-[900px] text-left text-sm"
          >

            <!-- Table Head -->
            <thead
              class="border-b border-slate-200 bg-slate-50/80
                    text-[11px] uppercase tracking-wider text-slate-500"
            >

              <tr>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Photo
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Teacher ID
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Teacher Name
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Department
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Status
                </th>

                <th class="text-center whitespace-nowrap px-5 py-3.5 font-semibold">
                  Actions
                </th>

              </tr>

            </thead>


            <!-- =================================================
                TABLE BODY
            ================================================== -->
            <tbody id="teachersTableBody" class="divide-y divide-slate-100">

              <!-- LoadData Function fills this section -->

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
            class="flex flex-col gap-2 text-xs text-slate-400
                  sm:flex-row sm:items-center sm:justify-between"
          >

            <span>
              Teacher records
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
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/add_teacher_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/view_teacher_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/edit_teacher_modal.php'; ?>

  </div>

<script> 
window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script> 


<script src="<?= BASE_URL ?>assets/js/admin/teachers/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/teachers/index.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>