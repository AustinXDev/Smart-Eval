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

// The department comes from the navigation (?dept=college|shs); anything else falls back to College.
$department = strtolower(trim((string) ($data['department'] ?? '')));
if (!in_array($department, ['college', 'shs'], true)) {
    $department = 'college';
}
$departmentLabel = $department === 'shs' ? 'SHS' : 'College';

$admin      = $data['admin'];
$role       = $data['role'];
$navigation = $data['navigation'] ?? [];

$currentUrl = parse_url(
    $_SERVER['REQUEST_URI'],
    PHP_URL_PATH
);

$pageTitle = "Manage Teachers";

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// KPI card accents, matching Evaluation Monitoring.
$accent = [
    'violet' => ['card' => 'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'bar' => 'from-[#6010FF] to-[#6010FF]/10', 'icon' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15', 'hover' => 'group-hover:text-[#6010FF]'],
    'emerald' => ['card' => 'from-emerald-50/80 hover:border-emerald-200', 'bar' => 'from-emerald-500 to-emerald-500/10', 'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'hover' => 'group-hover:text-emerald-500'],
    'rose' => ['card' => 'from-rose-50/80 hover:border-rose-200', 'bar' => 'from-rose-500 to-rose-500/10', 'icon' => 'bg-rose-50 text-rose-600 ring-rose-100', 'hover' => 'group-hover:text-rose-500'],
];

// [label, value id, description, icon, accent]
$statusKpis = [
    ['Total Teachers', 'total-teachers', 'All registered teacher records', 'fa-chalkboard-user', 'violet'],
    ['Active Teachers', 'total-active', 'Teachers available for evaluation', 'fa-user-check', 'emerald'],
    ['Inactive Teachers', 'total-inactive', 'Deactivated teacher records', 'fa-user-slash', 'rose'],
];

$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
$control = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Teachers <?= $e(strtoupper($department)) ?></title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Same DataTables setup as Evaluation Monitoring; table and pagination
       styles are shared in custom.css (#teachersTable_wrapper). -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <!-- Modal JS -->
  <script src="<?= BASE_URL ?>assets/js/modal/modal.js" type="module"></script>
</head>
<body class="bg-slate-50 pb-xl">
  <div class="pb-lg relative mx-auto flex min-h-dvh max-w-screen-2xl">

    <?php require __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full px-2 pb-8 pt-20 lg:ml-70 lg:px-10">
      <div class="mx-auto max-w-[1500px] space-y-6">

        <!-- ===== PAGE HEADER ===== -->
        <header class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#6010FF]/[0.07] via-white to-white shadow-sm">
          <div class="flex flex-col gap-6 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0 flex-1">
              <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="font-medium">Manage Teachers</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                <span class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]"><?= $e($departmentLabel) ?></span>
              </nav>

              <div class="space-y-1.5">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900"><?= $e($departmentLabel) ?> Teachers</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                  Manage and organize registered teacher information.
                </p>
              </div>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
              <button type="button"
                class="add-btn inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition duration-200 hover:shadow-lg hover:shadow-[#6010FF]/30 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98]">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                Add Teacher
              </button>
            </div>
          </div>
        </header>

        <!-- ===== KPI CARDS ===== -->
        <section aria-labelledby="overview-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="overview-title" class="text-base font-semibold tracking-tight text-slate-900">Teacher Overview</h2>
              <p class="mt-0.5 text-xs text-slate-500">Registered <?= $e($departmentLabel) ?> teacher records</p>
            </div>
          </div>

          <div id="card-container" data-department="<?= $e($department) ?>" class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
            <?php foreach ($statusKpis as $i => [$label, $id, $description, $icon, $color]): $a = $accent[$color]; ?>
              <article class="group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br <?= $a['card'] ?> via-white to-white p-4 shadow-sm transition duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5 <?= $i === 0 ? 'col-span-2 xl:col-span-1' : '' ?>">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r <?= $a['bar'] ?>"></span>
                <div class="flex items-start justify-between">
                  <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 <?= $a['icon'] ?>"><i class="fas <?= $icon ?> text-sm" aria-hidden="true"></i></span>
                  <span id="<?= $id ?>-share" class="text-[11px] font-semibold tabular-nums text-slate-400 transition-colors <?= $a['hover'] ?>"></span>
                </div>
                <div class="mt-4">
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs"><?= $e($label) ?></p>
                  <p id="<?= $id ?>" class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900 sm:text-3xl"><?= $skeleton ?></p>
                  <p class="mt-1 text-xs leading-snug text-slate-500"><?= $e($description) ?></p>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- ===== TEACHER DIRECTORY ===== -->
        <section aria-labelledby="directory-title" class="w-full min-w-0">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 class="text-base font-semibold tracking-tight text-slate-900">Teacher Directory</h2>
              <p class="mt-0.5 text-xs text-slate-500">View and manage <?= $e($departmentLabel) ?> teachers and their handled programs.</p>
            </div>
          </div>

          <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
              <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                  <i class="fa-solid fa-chalkboard-user text-sm" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2.5">
                    <h3 id="directory-title" class="text-sm font-semibold tracking-tight text-slate-900">Registered Teachers</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6010FF]">
                      <span id="table-count" class="tabular-nums">0</span><span>teachers</span>
                    </span>
                  </div>
                  <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Handled programs, account status and registration date for each teacher.</p>
                </div>
              </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 lg:flex lg:items-center">
              <div class="relative sm:col-span-2 lg:w-80">
                <label for="searchBox" class="sr-only">Search by Teacher ID or name</label>
                <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                <input id="searchBox" type="text" autocomplete="off" maxlength="100" placeholder="Search by Teacher ID or name..."
                  class="<?= $control ?> py-2 pl-10 pr-9 placeholder:text-slate-400">
                <button id="clear-search" type="button" class="absolute right-2 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Clear search">
                  <i class="fas fa-xmark text-xs" aria-hidden="true"></i>
                </button>
              </div>
              <div>
                <label for="statusFilter" class="sr-only">Status</label>
                <select id="statusFilter" class="<?= $control ?> lg:w-44">
                  <option value="All">All Statuses</option>
                  <option value="Active">Active</option>
                  <option value="Inactive">Inactive</option>
                </select>
              </div>
              <button id="reset-filters" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] lg:ml-auto">
                <i class="fas fa-filter-circle-xmark text-xs" aria-hidden="true"></i>
                Reset filters
              </button>
            </div>

            <div id="tableWrapper" class="w-full min-w-0 overflow-x-auto" role="region" aria-label="Teacher directory" tabindex="0"
                 data-department="<?= $e($department) ?>">
              <table id="teachersTable" class="w-full min-w-[900px] border-collapse text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                  <tr>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Teacher ID</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Teacher Name</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Handled Programs</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Status</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Created</th>
                    <th class="whitespace-nowrap px-5 py-3.5 text-right font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody id="teachersTableBody"></tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Modal Content -->
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/add_teacher_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/view_teacher_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/teachers_modal/edit_teacher_modal.php'; ?>

  </div>

<script>
window.BASE_URL = <?= json_encode(BASE_URL) ?>;
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>

<script src="<?= BASE_URL ?>assets/js/admin/teachers/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/teachers/index.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
<!-- Floating Add button: appears after the page header scrolls out of view -->
<script type="module">
  import { initFloatingActions } from "<?= BASE_URL ?>assets/js/admin/shared/floating_actions.js";
  initFloatingActions([{ target: ".add-btn", label: "Add Teacher", icon: "fa-plus" }]);
</script>
<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
