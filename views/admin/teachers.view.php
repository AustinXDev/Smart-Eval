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

      <!-- TEACHER MANAGEMENT KPI CARDS -->
      <div
        id="card-container"
        data-department="<?php echo htmlspecialchars($department, ENT_QUOTES, 'UTF-8'); ?>"
        class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3"
      >

        <!-- TOTAL TEACHERS -->
        <article
          class="group relative flex min-h-[185px] min-w-0 flex-col
                justify-between overflow-hidden rounded-2xl
                border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-violet-200 hover:shadow-md sm:p-6"
        >
          <!-- Header -->
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Total Teachers
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Faculty records overview
              </p>
            </div>

            <!-- Status Badge -->
            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-violet-50 px-2.5 py-1
                    text-[11px] font-medium text-violet-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
              All
            </span>
          </div>

          <!-- KPI Value -->
          <div class="mt-6">
            <p
              id="total-teachers"
              class="text-4xl font-light leading-none
                    tracking-tight tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-violet-500"></span>

              <p class="text-xs text-slate-500">
                Total registered teachers
              </p>
            </div>
          </div>

          <!-- Bottom Accent -->
          <div class="absolute bottom-0 left-0 h-0.5 w-full bg-violet-100"></div>
        </article>


        <!-- ACTIVE TEACHERS -->
        <article
          class="group relative flex min-h-[185px] min-w-0 flex-col
                justify-between overflow-hidden rounded-2xl
                border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-emerald-200 hover:shadow-md sm:p-6"
        >
          <!-- Header -->
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Active Teachers
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Faculty account status
              </p>
            </div>

            <!-- Status Badge -->
            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-emerald-50 px-2.5 py-1
                    text-[11px] font-medium text-emerald-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Active
            </span>
          </div>

          <!-- KPI Value -->
          <div class="mt-6">
            <p
              id="total-active"
              class="text-4xl font-light leading-none
                    tracking-tight tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>

              <p class="text-xs text-slate-500">
                Currently active teachers
              </p>
            </div>
          </div>

          <!-- Bottom Accent -->
          <div class="absolute bottom-0 left-0 h-0.5 w-full bg-emerald-100"></div>
        </article>


        <!-- INACTIVE TEACHERS -->
        <article
          class="group relative flex min-h-[185px] min-w-0 flex-col
                justify-between overflow-hidden rounded-2xl
                border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-rose-200 hover:shadow-md sm:p-6"
        >
          <!-- Header -->
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Inactive Teachers
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Faculty account status
              </p>
            </div>

            <!-- Status Badge -->
            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-rose-50 px-2.5 py-1
                    text-[11px] font-medium text-rose-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
              Inactive
            </span>
          </div>

          <!-- KPI Value -->
          <div class="mt-6">
            <p
              id="total-inactive"
              class="text-4xl font-light leading-none
                    tracking-tight tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500"></span>

              <p class="text-xs text-slate-500">
                Currently inactive teachers
              </p>
            </div>
          </div>

          <!-- Bottom Accent -->
          <div class="absolute bottom-0 left-0 h-0.5 w-full bg-rose-100"></div>
        </article>

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
<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>