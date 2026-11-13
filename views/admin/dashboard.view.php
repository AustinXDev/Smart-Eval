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

$pageTitle = "Dashboard";

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// Quick actions. Management pages are super-admin only (ApiSecurity admin_manage);
// deans get the read-only pages their navigation already allows.
$isSuperAdmin = $role === 'super_admin';
$quickActions = array_values(array_filter([
    ['Create Evaluation Period', 'manage-evaluation-period', 'fa-calendar-plus', 'Schedule a new evaluation period', 'violet', true],
    ['Manage Students', 'manage-students?dept=college', 'fa-user-graduate', 'College student records', 'blue', true],
    ['Manage Teachers', 'manage-teachers?dept=college', 'fa-chalkboard-user', 'College faculty records', 'emerald', true],
    ['Manage Questionnaires', 'manage-questionnaires', 'fa-clipboard-list', 'Question sets and items', 'amber', true],
    ['Manage Programs', 'manage-program', 'fa-book', 'Programs and year levels', 'rose', true],
    ['Participation Monitoring', 'evaluation-monitoring?dept=' . $department, 'fa-chart-pie', 'Track student completion', 'indigo', false],
    ['Archive Management', 'archive-management', 'fa-box-archive', 'Backups and restoration', 'slate', true],
    ['Reports & Analytics', 'reports-analytics?dept=' . $department, 'fa-chart-column', 'Results and performance', 'violet', false],
], static fn (array $action): bool => $isSuperAdmin || !$action[5]));

$accent = [
    'violet' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15 group-hover:bg-[#6010FF] group-hover:text-white',
    'blue' => 'bg-blue-50 text-blue-600 ring-blue-100 group-hover:bg-blue-600 group-hover:text-white',
    'emerald' => 'bg-emerald-50 text-emerald-600 ring-emerald-100 group-hover:bg-emerald-600 group-hover:text-white',
    'amber' => 'bg-amber-50 text-amber-600 ring-amber-100 group-hover:bg-amber-500 group-hover:text-white',
    'rose' => 'bg-rose-50 text-rose-600 ring-rose-100 group-hover:bg-rose-600 group-hover:text-white',
    'indigo' => 'bg-indigo-50 text-indigo-600 ring-indigo-100 group-hover:bg-indigo-600 group-hover:text-white',
    'slate' => 'bg-slate-100 text-slate-600 ring-slate-200 group-hover:bg-slate-700 group-hover:text-white',
];

// [label, value id, description, icon, card gradient, top bar, icon chip]
$kpis = [
    ['Total Students', 'kpi-students', 'Active registered students', 'fa-user-graduate',
        'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'from-[#6010FF] to-[#6010FF]/10', 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15'],
    ['Total Teachers', 'kpi-teachers', 'Active registered teachers', 'fa-chalkboard-user',
        'from-blue-50/80 hover:border-blue-200', 'from-blue-500 to-blue-500/10', 'bg-blue-50 text-blue-600 ring-blue-100'],
    ['Submitted Evaluations', 'kpi-submitted', 'Teacher evaluations submitted this period', 'fa-clipboard-check',
        'from-emerald-50/80 hover:border-emerald-200', 'from-emerald-500 to-emerald-500/10', 'bg-emerald-50 text-emerald-600 ring-emerald-100'],
];
$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
$lineSkeleton = static fn (string $w): string => '<span class="inline-block h-3 ' . $w . ' animate-pulse rounded bg-slate-100 align-middle"></span>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard <?= $e($departmentLabel) ?></title>

  <?php require_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-slate-50 pb-xl">
  <div class="pb-lg relative mx-auto flex min-h-dvh max-w-screen-2xl">

    <?php require __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full px-2 pb-8 pt-20 lg:ml-70 lg:px-10">
      <div class="mx-auto max-w-[1500px] space-y-6">

        <!-- ===== HEADER ===== -->
        <header class="space-y-5">

          <!-- Title row -->
          <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
            <div class="min-w-0">
              <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-xs text-stone-500">
                <span>Overview</span>
                <i class="fas fa-chevron-right text-[8px] text-stone-400" aria-hidden="true"></i>
                <span class="font-medium text-[#16213E]"><?= $e($departmentLabel) ?></span>
              </nav>

              <h1 class="mt-2 text-2xl font-semibold tracking-tight text-[#16213E]">Dashboard</h1>
              <p class="mt-1 max-w-2xl text-sm text-stone-500">
                Participation, results and activity for the <?= $e($departmentLabel) ?> department.
              </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
              <!-- Live status -->
              <div class="inline-flex h-9 items-center gap-2 rounded-xl border border-stone-200 bg-white px-3 text-xs text-stone-500"
                   title="This dashboard refreshes automatically" aria-live="polite">
                <span class="relative flex h-2 w-2">
                  <span id="live-ping" class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60 motion-safe:animate-ping"></span>
                  <span id="live-dot" class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span id="live-label" class="font-medium text-[#16213E]">Live</span>
                <span class="text-stone-300" aria-hidden="true">·</span>
                <span>Updated <span id="last-updated" class="font-medium tabular-nums text-stone-600">—</span></span>
              </div>

              <?php if ($isSuperAdmin): ?>
                <a href="manage-evaluation-period"
                   class="inline-flex h-9 items-center gap-2 rounded-xl bg-[#7C3AED] px-4 text-xs font-semibold text-white transition hover:bg-[#5B21B6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7C3AED] focus-visible:ring-offset-2 motion-safe:active:scale-[0.98]">
                  <i class="fas fa-plus text-[10px]" aria-hidden="true"></i>
                  New evaluation period
                </a>
              <?php endif; ?>
            </div>
          </div>

          <!-- Active period strip -->
          <div class="flex flex-col gap-3 rounded-2xl border border-stone-200 bg-white px-5 py-3.5 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1.5 text-sm">
              <span class="inline-flex items-center gap-2 text-xs font-medium text-stone-500">
                <i class="far fa-calendar text-stone-400" aria-hidden="true"></i>
                Active period
              </span>
              <span class="hidden h-4 w-px bg-stone-200 sm:block" aria-hidden="true"></span>
              <span class="inline-flex items-center gap-1.5 font-medium text-[#16213E]">
                <span id="hdr-year"><?= $lineSkeleton('w-20') ?></span>
                <span class="text-stone-300" aria-hidden="true">/</span>
                <span id="hdr-semester"><?= $lineSkeleton('w-16') ?></span>
              </span>
              <span id="hdr-dates" class="hidden text-xs tabular-nums text-stone-500"></span>
            </div>

            <span id="hdr-status" class="inline-flex w-fit items-center gap-1.5 rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600">
              <span class="h-1.5 w-1.5 rounded-full bg-stone-400"></span>Loading
            </span>
          </div>
        </header>

        <div id="error-state" class="hidden items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
          <i class="fas fa-circle-exclamation mt-0.5 text-rose-500" aria-hidden="true"></i>
          <div class="flex-1">
            <p class="font-semibold">Dashboard data could not be loaded</p>
            <p class="mt-0.5 text-rose-700">Retrying automatically. You can also retry now.</p>
          </div>
          <button type="button" id="retry-dashboard" class="shrink-0 rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Retry</button>
        </div>

        <!-- ===== KPI SUMMARY CARDS ===== -->
        <section aria-labelledby="summary-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="summary-title" class="text-base font-semibold tracking-tight text-slate-900">Summary</h2>
              <p class="mt-0.5 text-xs text-slate-500"><?= $e($departmentLabel) ?> department at a glance</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
            <?php foreach ($kpis as [$label, $id, $description, $icon, $card, $bar, $chip]): ?>
              <article class="group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br <?= $card ?> via-white to-white p-4 shadow-sm transition duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5">
                <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r <?= $bar ?>"></span>
                <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 <?= $chip ?>"><i class="fas <?= $icon ?> text-sm" aria-hidden="true"></i></span>
                <div class="mt-4">
                  <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs"><?= $e($label) ?></p>
                  <p id="<?= $id ?>" class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900 sm:text-3xl"><?= $skeleton ?></p>
                  <p class="mt-1 text-xs leading-snug text-slate-500"><?= $e($description) ?></p>
                </div>
              </article>
            <?php endforeach; ?>

            <!-- Active Evaluation -->
            <article class="group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-amber-50/80 via-white to-white p-4 shadow-sm transition duration-200 hover:border-amber-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5">
              <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-amber-500 to-amber-500/10"></span>
              <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 ring-1 ring-amber-100"><i class="fas fa-hourglass-half text-sm" aria-hidden="true"></i></span>
              <div class="mt-4 min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs">Active Evaluation</p>
                <p id="kpi-active" class="mt-1 truncate text-lg font-bold tracking-tight text-slate-900 sm:text-xl"><?= $skeleton ?></p>
                <p id="kpi-active-detail" class="mt-1 truncate text-xs leading-snug text-slate-500">&nbsp;</p>
              </div>
            </article>
          </div>
        </section>

        <!-- ===== CURRENT EVALUATION OVERVIEW + TEACHER PERFORMANCE ===== -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

          <!-- ===== Current evaluation ===== -->
          <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white xl:col-span-2" aria-labelledby="current-title">
            <div class="flex flex-col gap-2 border-b border-stone-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
              <div>
                <h2 id="current-title" class="text-sm font-semibold text-[#16213E]">Current evaluation</h2>
                <p class="mt-0.5 text-xs text-stone-500">Participation in the active <?= $e($departmentLabel) ?> period</p>
              </div>
              <a href="evaluation-monitoring?dept=<?= $e($department) ?>"
                 class="group inline-flex w-fit items-center gap-1.5 rounded-md text-xs font-semibold text-[#7C3AED] transition hover:text-[#5B21B6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7C3AED] focus-visible:ring-offset-2">
                Open monitoring
                <i class="fas fa-arrow-right text-[10px] transition motion-safe:group-hover:translate-x-0.5" aria-hidden="true"></i>
              </a>
            </div>

            <div id="current-body" class="p-5 sm:p-6">
              <div class="flex flex-col gap-1 sm:flex-row sm:items-baseline sm:justify-between">
                <p id="cur-name" class="text-base font-semibold text-[#16213E]"><?= $lineSkeleton('w-48') ?></p>
                <p id="cur-dates" class="text-xs tabular-nums text-stone-500"><?= $lineSkeleton('w-40') ?></p>
              </div>

              <!-- Headline: participation rate -->
              <div class="mt-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                  <p class="text-xs font-medium text-stone-500">Participation rate</p>
                  <p id="cur-rate" class="mt-1 text-4xl font-semibold tabular-nums tracking-tight text-[#7C3AED]">—</p>
                </div>
                <p class="pb-1 text-xs text-stone-500">
                  <span id="cur-participating" class="font-semibold tabular-nums text-[#16213E]">0</span>
                  of <span id="cur-eligible-2" class="font-semibold tabular-nums text-[#16213E]">0</span> students participated
                </p>
              </div>

              <div id="cur-progress" class="mt-3 flex h-2.5 w-full overflow-hidden rounded-full bg-stone-100"
                   role="progressbar" aria-label="Participation rate" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span id="cur-bar-completed" class="h-full bg-[#7C3AED] transition-[width] duration-700 ease-out" style="width:0%" title="Completed"></span>
                <span id="cur-bar-incomplete" class="h-full bg-[#C4B5FD] transition-[width] duration-700 ease-out" style="width:0%" title="In progress"></span>
              </div>

              <div class="mt-3 flex flex-wrap gap-x-5 gap-y-1 text-xs text-stone-500">
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#7C3AED]"></span>Completed <strong id="cur-legend-completed" class="font-semibold tabular-nums text-[#16213E]">0</strong></span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-[#C4B5FD]"></span>In progress <strong id="cur-legend-incomplete" class="font-semibold tabular-nums text-[#16213E]">0</strong></span>
                <span class="inline-flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-stone-300"></span>Not started <strong id="cur-legend-unresponsive" class="font-semibold tabular-nums text-[#16213E]">0</strong></span>
              </div>

              <!-- Breakdown -->
              <dl class="mt-6 grid grid-cols-3 divide-x divide-stone-100 border-t border-stone-100 pt-5">
                <div class="pr-4">
                  <dt class="text-xs font-medium text-stone-500">Eligible students</dt>
                  <dd id="cur-eligible" class="mt-1 text-xl font-semibold tabular-nums text-[#16213E]">—</dd>
                </div>
                <div class="px-4">
                  <dt class="text-xs font-medium text-stone-500">Submitted</dt>
                  <dd id="cur-completed" class="mt-1 text-xl font-semibold tabular-nums text-[#16213E]">—</dd>
                </div>
                <div class="pl-4">
                  <dt class="text-xs font-medium text-stone-500">Remaining</dt>
                  <dd id="cur-remaining" class="mt-1 text-xl font-semibold tabular-nums text-[#16213E]">—</dd>
                </div>
              </dl>
            </div>

            <div id="current-empty" class="hidden px-6 py-12 text-center">
              <i class="far fa-calendar-xmark text-xl text-stone-400" aria-hidden="true"></i>
              <p class="mt-3 text-sm font-semibold text-[#16213E]">No active evaluation period</p>
              <p class="mx-auto mt-1 max-w-sm text-xs text-stone-500">Participation appears here once a <?= $e($departmentLabel) ?> evaluation period is open.</p>
              <?php if ($isSuperAdmin): ?>
                <a href="manage-evaluation-period"
                   class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl bg-[#7C3AED] px-4 text-xs font-semibold text-white transition hover:bg-[#5B21B6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7C3AED] focus-visible:ring-offset-2 motion-safe:active:scale-[0.98]">
                  Open an evaluation period
                </a>
              <?php endif; ?>
            </div>
          </section>

          <!-- ===== Teacher performance ===== -->
          <section class="overflow-hidden rounded-2xl border border-stone-200 bg-white" aria-labelledby="performance-title">
            <div class="border-b border-stone-100 px-5 py-4 sm:px-6">
              <h2 id="performance-title" class="text-sm font-semibold text-[#16213E]">Teacher performance</h2>
              <p class="mt-0.5 text-xs text-stone-500">Results so far this period</p>
            </div>

            <div class="p-5 sm:p-6">
              <div class="flex items-end justify-between gap-4">
                <div>
                  <p class="text-xs font-medium text-stone-500">Overall average</p>
                  <p class="mt-1 flex items-baseline gap-1.5">
                    <span id="perf-average" class="text-4xl font-semibold tabular-nums tracking-tight text-[#16213E]">—</span>
                    <span class="text-xs text-stone-500">/ 5.00</span>
                  </p>
                </div>
                <span id="perf-rating" class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600">No rating</span>
              </div>

              <div class="mt-6 flex items-center justify-between border-t border-stone-100 pt-5">
                <p class="text-xs font-medium text-stone-500">Top 3 teachers</p>
                <a href="reports-analytics?dept=<?= $e($department) ?>"
                   class="rounded-md text-xs font-semibold text-[#7C3AED] transition hover:text-[#5B21B6] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#7C3AED] focus-visible:ring-offset-2">
                  Full ranking
                </a>
              </div>

              <ol id="perf-top" class="mt-3 space-y-2" aria-label="Top teachers">
                <?php for ($i = 0; $i < 3; $i++): ?>
                  <li class="flex items-center gap-3 rounded-xl border border-stone-100 p-3">
                    <span class="h-8 w-8 rounded-lg bg-stone-100 motion-safe:animate-pulse"></span>
                    <span class="h-3 flex-1 rounded bg-stone-100 motion-safe:animate-pulse"></span>
                  </li>
                <?php endfor; ?>
              </ol>

              <p class="mt-4 flex items-start gap-2 text-xs leading-relaxed text-stone-500">
                <i class="fas fa-lock mt-0.5 text-[10px] text-stone-400" aria-hidden="true"></i>
                Based on students who completed all their evaluations. Individual responses are never shown.
              </p>
            </div>
          </section>
        </div>

        <!-- ===== SUBMISSION TRENDS + RECENT ACTIVITY ===== -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

          <!-- Submissions per day -->
          <section class="flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white xl:col-span-2" aria-labelledby="trend-title">
            <div class="flex flex-col gap-3 border-b border-stone-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
              <div>
                <h2 id="trend-title" class="text-sm font-semibold text-[#16213E]">Submissions per day</h2>
                <p class="mt-0.5 text-xs text-stone-500">Teacher evaluations submitted in the active period</p>
              </div>
              <span id="trend-range" class="inline-flex w-fit items-center gap-1.5 rounded-lg border border-stone-200 bg-stone-50 px-2.5 py-1 text-xs font-medium tabular-nums text-stone-600">—</span>
            </div>

            <div class="flex-1 p-5 sm:p-6">
              <div class="relative h-72 w-full">
                <canvas id="trend-chart" aria-label="Daily evaluation submissions" role="img"></canvas>
                <div id="trend-empty" class="absolute inset-0 hidden flex-col items-center justify-center text-center">
                  <span class="flex h-11 w-11 items-center justify-center rounded-full bg-stone-100">
                    <i class="fa-solid fa-chart-line text-stone-500" aria-hidden="true"></i>
                  </span>
                  <p class="mt-3 text-sm font-semibold text-[#16213E]">No submissions yet</p>
                  <p id="trend-empty-text" class="mt-1 max-w-xs text-xs text-stone-500">Daily submissions appear here as students submit evaluations.</p>
                </div>
              </div>
            </div>
          </section>

          <!-- Recent activity -->
          <section class="flex flex-col overflow-hidden rounded-2xl border border-stone-200 bg-white" aria-labelledby="activity-title">
            <div class="flex items-center justify-between gap-3 border-b border-stone-100 px-5 py-4 sm:px-6">
              <div>
                <h2 id="activity-title" class="text-sm font-semibold text-[#16213E]">Recent activity</h2>
                <p class="mt-0.5 text-xs text-stone-500">Latest <?= $e($departmentLabel) ?> events</p>
              </div>
              <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>Live
              </span>
            </div>

            <ul id="activity-list"
                class="max-h-[26rem] flex-1 divide-y divide-stone-100 overflow-y-auto [&>li]:transition-colors [&>li:hover]:bg-stone-50"
                aria-live="polite">
              <?php for ($i = 0; $i < 5; $i++): ?>
                <li class="flex items-start gap-3 px-5 py-3.5 sm:px-6">
                  <span class="h-8 w-8 shrink-0 rounded-full bg-stone-100 motion-safe:animate-pulse"></span>
                  <span class="mt-1 flex-1 space-y-2">
                    <span class="block h-3 w-3/4 rounded bg-stone-100 motion-safe:animate-pulse"></span>
                    <span class="block h-2.5 w-1/3 rounded bg-stone-100 motion-safe:animate-pulse"></span>
                  </span>
                </li>
              <?php endfor; ?>
            </ul>
          </section>
        </div>

        <!-- ===== QUICK ACTIONS ===== -->
        <section aria-labelledby="actions-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="actions-title" class="text-base font-semibold tracking-tight text-slate-900">Quick Actions</h2>
              <p class="mt-0.5 text-xs text-slate-500">Shortcuts to frequently used administrative tasks</p>
            </div>
          </div>
          <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <?php foreach ($quickActions as [$label, $url, $icon, $hint, $color]): ?>
              <a href="<?= $e($url) ?>" class="group flex items-center gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition duration-200 hover:border-[#6010FF]/30 hover:shadow-md focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] motion-safe:hover:-translate-y-0.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl ring-1 transition-colors <?= $accent[$color] ?>"><i class="fas <?= $icon ?> text-sm" aria-hidden="true"></i></span>
                <span class="min-w-0 flex-1">
                  <span class="block truncate text-sm font-semibold text-slate-900"><?= $e($label) ?></span>
                  <span class="block truncate text-xs text-slate-500"><?= $e($hint) ?></span>
                </span>
                <i class="fas fa-chevron-right text-[10px] text-slate-300 transition group-hover:translate-x-0.5 group-hover:text-[#6010FF]" aria-hidden="true"></i>
              </a>
            <?php endforeach; ?>
          </div>
        </section>

      </div>
    </main>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.dashboardConfig = { department: <?= json_encode($department) ?> };
  </script>
  <script src="<?= BASE_URL ?>assets/js/admin/dashboard/dashboard.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>

  <?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
