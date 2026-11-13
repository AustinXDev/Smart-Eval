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

$pageTitle = "Manage Students";

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// Same year levels as the add-student form (StudentService::yearLevelsFor on the server).
$yearLevels = $department === 'shs'
    ? ['11' => 'Grade 11', '12' => 'Grade 12']
    : ['1' => '1st Year', '2' => '2nd Year', '3' => '3rd Year', '4' => '4th Year'];

// KPI card accents, matching Evaluation Monitoring.
$accent = [
    'violet' => ['card' => 'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'bar' => 'from-[#6010FF] to-[#6010FF]/10', 'icon' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15', 'fill' => 'bg-[#6010FF]', 'hover' => 'group-hover:text-[#6010FF]'],
    'blue' => ['card' => 'from-blue-50/80 hover:border-blue-200', 'bar' => 'from-blue-500 to-blue-500/10', 'icon' => 'bg-blue-50 text-blue-600 ring-blue-100', 'fill' => 'bg-blue-500', 'hover' => 'group-hover:text-blue-500'],
    'emerald' => ['card' => 'from-emerald-50/80 hover:border-emerald-200', 'bar' => 'from-emerald-500 to-emerald-500/10', 'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'fill' => 'bg-emerald-500', 'hover' => 'group-hover:text-emerald-500'],
    'rose' => ['card' => 'from-rose-50/80 hover:border-rose-200', 'bar' => 'from-rose-500 to-rose-500/10', 'icon' => 'bg-rose-50 text-rose-600 ring-rose-100', 'fill' => 'bg-rose-500', 'hover' => 'group-hover:text-rose-500'],
    'amber' => ['card' => 'from-amber-50/80 hover:border-amber-200', 'bar' => 'from-amber-500 to-amber-500/10', 'icon' => 'bg-amber-50 text-amber-600 ring-amber-100', 'fill' => 'bg-amber-500', 'hover' => 'group-hover:text-amber-500'],
    'indigo' => ['card' => 'from-indigo-50/80 hover:border-indigo-200', 'bar' => 'from-indigo-500 to-indigo-500/10', 'icon' => 'bg-indigo-50 text-indigo-600 ring-indigo-100', 'fill' => 'bg-indigo-500', 'hover' => 'group-hover:text-indigo-500'],
];

// [label, value id, description, icon, accent]
$statusKpis = [
    ['Total Students', 'total-students', 'All registered student records', 'fa-users', 'violet'],
    ['Active Students', 'total-active', 'Accounts that can sign in and evaluate', 'fa-user-check', 'emerald'],
    ['Inactive Students', 'total-inactive', 'Deactivated student records', 'fa-user-slash', 'rose'],
];
$yearAccents = ['indigo', 'blue', 'emerald', 'amber'];

$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
$cardBase = 'group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br via-white to-white p-4 text-left shadow-sm transition duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5';
$control = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students <?= $e(strtoupper($department)) ?></title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Same DataTables setup as Evaluation Monitoring; table and pagination
       styles are shared in custom.css (#studentsTable_wrapper). -->
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
                <span class="font-medium">Manage Students</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                <span class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]"><?= $e($departmentLabel) ?></span>
              </nav>

              <div class="space-y-1.5">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900"><?= $e($departmentLabel) ?> Students</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                  Manage and organize registered student information.
                </p>
              </div>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
              <button type="button"
                class="csv-btn inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98]">
                <i class="fas fa-file-csv text-xs" aria-hidden="true"></i>
                Import CSV
              </button>
              <button type="button"
                class="add-btn inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition duration-200 hover:shadow-lg hover:shadow-[#6010FF]/30 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98]">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                Add Student
              </button>
            </div>
          </div>
        </header>

        <!-- ===== KPI: ACCOUNT STATUS ===== -->
        <section aria-labelledby="overview-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="overview-title" class="text-base font-semibold tracking-tight text-slate-900">Student Overview</h2>
              <p class="mt-0.5 text-xs text-slate-500">Registered <?= $e($departmentLabel) ?> student records</p>
            </div>
          </div>

          <div id="card-container" class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
            <?php foreach ($statusKpis as $i => [$label, $id, $description, $icon, $color]): $a = $accent[$color]; ?>
              <article class="<?= $cardBase ?> <?= $a['card'] ?> <?= $i === 0 ? 'col-span-2 xl:col-span-1' : '' ?>">
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

        <!-- ===== KPI: STUDENTS PER YEAR LEVEL ===== -->
        <section aria-labelledby="year-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="year-title" class="text-base font-semibold tracking-tight text-slate-900">Students per <?= $department === 'shs' ? 'Grade' : 'Year' ?> Level</h2>
              <p class="mt-0.5 text-xs text-slate-500">Active <?= $e($departmentLabel) ?> students. Select a card to filter the directory.</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 sm:gap-4 <?= count($yearLevels) > 2 ? 'xl:grid-cols-4' : '' ?>">
            <?php $i = 0; foreach ($yearLevels as $level => $label): $a = $accent[$yearAccents[$i++ % count($yearAccents)]]; ?>
              <button type="button" data-year-card="<?= $e((string) $level) ?>" aria-pressed="false" aria-controls="studentsTable"
                class="<?= $cardBase ?> <?= $a['card'] ?> cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 aria-pressed:border-[#6010FF]/50 aria-pressed:ring-2 aria-pressed:ring-[#6010FF]/20">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r <?= $a['bar'] ?>"></span>
                <span class="flex w-full items-start justify-between">
                  <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 <?= $a['icon'] ?>"><i class="fas fa-layer-group text-sm" aria-hidden="true"></i></span>
                  <span class="inline-flex items-center gap-2">
                    <span data-year-filtered class="hidden rounded-full bg-[#6010FF]/10 px-2 py-0.5 text-[10px] font-semibold text-[#6010FF]">Filtered</span>
                    <span id="year-<?= $e((string) $level) ?>-share" class="text-[11px] font-semibold tabular-nums text-slate-400 transition-colors <?= $a['hover'] ?>"></span>
                  </span>
                </span>
                <span class="mt-4 block w-full">
                  <span class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs"><?= $e($label) ?></span>
                  <span id="year-<?= $e((string) $level) ?>" class="mt-1 block text-2xl font-bold tabular-nums tracking-tight text-slate-900 sm:text-3xl"><?= $skeleton ?></span>
                  <span class="mt-2 block h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                    <span id="year-<?= $e((string) $level) ?>-bar" class="block h-full rounded-full <?= $a['fill'] ?> transition-[width] duration-700 ease-out" style="width:0%"></span>
                  </span>
                </span>
              </button>
            <?php endforeach; ?>
          </div>
        </section>

        <!-- ===== STUDENT DIRECTORY ===== -->
        <section aria-labelledby="directory-title" class="w-full min-w-0">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 class="text-base font-semibold tracking-tight text-slate-900">Student Directory</h2>
              <p class="mt-0.5 text-xs text-slate-500">View and manage active <?= $e($departmentLabel) ?> students.</p>
            </div>
          </div>

          <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
              <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                  <i class="fa-solid fa-user-graduate text-sm" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2.5">
                    <h3 id="directory-title" class="text-sm font-semibold tracking-tight text-slate-900">Registered Students</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6010FF]">
                      <span id="table-count" class="tabular-nums">0</span><span>students</span>
                    </span>
                  </div>
                  <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Course, year level, account activation and registration date for each student.</p>
                </div>
              </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 lg:flex lg:items-center">
              <div class="relative sm:col-span-2 lg:w-80">
                <label for="searchBox" class="sr-only">Search by Student ID, name or course</label>
                <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                <input id="searchBox" type="text" autocomplete="off" maxlength="100" placeholder="Search by Student ID, name or course..."
                  class="<?= $control ?> py-2 pl-10 pr-9 placeholder:text-slate-400">
                <button id="clear-search" type="button" class="absolute right-2 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Clear search">
                  <i class="fas fa-xmark text-xs" aria-hidden="true"></i>
                </button>
              </div>
              <div>
                <label for="courseFilter" class="sr-only">Course</label>
                <select id="courseFilter" class="<?= $control ?> lg:w-52">
                  <option value="All">All Courses</option>
                </select>
              </div>
              <div>
                <label for="yearFilter" class="sr-only"><?= $department === 'shs' ? 'Grade level' : 'Year level' ?></label>
                <select id="yearFilter" class="<?= $control ?> lg:w-44">
                  <option value=""><?= $department === 'shs' ? 'All Grade Levels' : 'All Year Levels' ?></option>
                  <?php foreach ($yearLevels as $level => $label): ?>
                    <option value="<?= $e((string) $level) ?>"><?= $e($label) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <button id="reset-filters" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] sm:col-span-2 lg:ml-auto">
                <i class="fas fa-filter-circle-xmark text-xs" aria-hidden="true"></i>
                Reset filters
              </button>
            </div>

            <div id="tableWrapper" class="w-full min-w-0 overflow-x-auto" role="region" aria-label="Student directory" tabindex="0"
                 data-department="<?= $e($department) ?>">
              <table id="studentsTable" class="w-full min-w-[960px] border-collapse text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                  <tr>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Student ID</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Student Name</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Course</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold"><?= $department === 'shs' ? 'Grade Level' : 'Year Level' ?></th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Account Status</th>
                    <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Created</th>
                    <th class="whitespace-nowrap px-5 py-3.5 text-right font-semibold">Actions</th>
                  </tr>
                </thead>
                <tbody id="studentsTableBody"></tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
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
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
</script>

<script src="<?= BASE_URL ?>assets/js/admin/students/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/students/index.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>

<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
