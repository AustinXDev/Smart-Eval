<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\Middleware\AdminAuthMiddleware;
use App\Repositories\AdminRepo\AdminRepository;
use App\Services\Admin\AdminContext;

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/nav.php';

AdminAuthMiddleware::handle();

$controller = new DashboardController(new AdminContext(new AdminRepository($pdo)), $navigation);
$data = $controller->index();

// The department comes from the navigation (?dept=college|shs); anything else falls back to College.
$department = strtolower(trim((string) ($data['department'] ?? '')));
if (!in_array($department, ['college', 'shs'], true)) {
    $department = 'college';
}
$departmentLabel = $department === 'shs' ? 'SHS' : 'College';

$admin = $data['admin'];
$role = $data['role'];
$navigation = $data['navigation'] ?? [];
$pageTitle = 'Evaluation Monitoring';
// Reminder queueing is an admin_manage endpoint (super admin only); ApiSecurity enforces it server-side too.
$canNotify = $role === 'super_admin';

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// [label, value id, description, icon, accent]
$kpis = [
    ['Overall Eligible', 'kpi-eligible', 'Students expected to participate', 'fa-users', 'violet'],
    ['Submitted Today', 'kpi-today', 'Unique students who submitted today', 'fa-calendar-check', 'blue'],
    ['Successfully Submitted', 'kpi-completed', 'Completed every required evaluation', 'fa-circle-check', 'emerald'],
    ['Unresponsive', 'kpi-unresponsive', 'No teacher evaluation submitted yet', 'fa-user-clock', 'rose'],
    ['Incomplete', 'kpi-incomplete', 'Started, with evaluations remaining', 'fa-hourglass-half', 'amber'],
    ['Remaining Participants', 'kpi-remaining', 'Unresponsive plus incomplete students', 'fa-list-check', 'indigo'],
];
$accent = [
    'violet' => ['card' => 'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'bar' => 'from-[#6010FF] to-[#6010FF]/10', 'icon' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15', 'hover' => 'group-hover:text-[#6010FF]'],
    'blue' => ['card' => 'from-blue-50/80 hover:border-blue-200', 'bar' => 'from-blue-500 to-blue-500/10', 'icon' => 'bg-blue-50 text-blue-600 ring-blue-100', 'hover' => 'group-hover:text-blue-500'],
    'emerald' => ['card' => 'from-emerald-50/80 hover:border-emerald-200', 'bar' => 'from-emerald-500 to-emerald-500/10', 'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'hover' => 'group-hover:text-emerald-500'],
    'rose' => ['card' => 'from-rose-50/80 hover:border-rose-200', 'bar' => 'from-rose-500 to-rose-500/10', 'icon' => 'bg-rose-50 text-rose-600 ring-rose-100', 'hover' => 'group-hover:text-rose-500'],
    'amber' => ['card' => 'from-amber-50/80 hover:border-amber-200', 'bar' => 'from-amber-500 to-amber-500/10', 'icon' => 'bg-amber-50 text-amber-600 ring-amber-100', 'hover' => 'group-hover:text-amber-500'],
    'indigo' => ['card' => 'from-indigo-50/80 hover:border-indigo-200', 'bar' => 'from-indigo-500 to-indigo-500/10', 'icon' => 'bg-indigo-50 text-indigo-600 ring-indigo-100', 'hover' => 'group-hover:text-indigo-500'],
];
$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluation Monitoring <?= $e(strtoupper($department)) ?></title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <!-- Same DataTables setup as the student list: pagination is styled by
       stylePagination() in shared/datatable_config.js (only .current is filled). -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <style>
    .donut-segment { transition: stroke-dasharray .9s cubic-bezier(.22,1,.36,1), stroke-dashoffset .9s cubic-bezier(.22,1,.36,1), opacity .2s; }
    .donut-segment:hover { opacity: .8; }
    @media (prefers-reduced-motion: reduce) { .donut-segment, .legend-bar { transition: none !important; } }
    /* Table pagination styles live in custom.css (#monitoring-student-table_wrapper). */
  </style>
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
                <span class="font-medium">Evaluation Monitoring</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                <span class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]"><?= $e($departmentLabel) ?></span>
              </nav>

              <div class="space-y-1.5">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Evaluation Monitoring</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                  Monitor student participation, track evaluation progress, and identify students who have not completed their teacher evaluations during the active evaluation period.
                </p>
              </div>

              <div class="mt-5 flex flex-wrap items-center gap-x-3 gap-y-2">
                <span class="inline-flex items-center gap-2 text-xs font-medium text-slate-500">
                  <i class="far fa-calendar text-[#6010FF]" aria-hidden="true"></i>
                  Active Period
                </span>
                <span class="hidden h-4 w-px bg-slate-200 sm:block" aria-hidden="true"></span>
                <span id="period-year" class="text-xs font-semibold text-slate-800"><span class="inline-block h-3 w-20 animate-pulse rounded bg-slate-100 align-middle"></span></span>
                <span class="text-xs text-slate-300" aria-hidden="true">/</span>
                <span id="period-semester" class="text-xs font-semibold text-slate-800"><span class="inline-block h-3 w-16 animate-pulse rounded bg-slate-100 align-middle"></span></span>
                <span id="period-state" class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-500">
                  <span class="h-1.5 w-1.5 rounded-full bg-slate-300"></span>
                  Loading
                </span>
              </div>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
              <div id="live-indicator" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 text-xs text-slate-500 shadow-sm sm:mr-1" title="Monitoring updates automatically">
                <span class="relative flex h-2 w-2">
                  <span data-live-ping class="absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75 motion-safe:animate-ping"></span>
                  <span data-live-dot class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span data-live-label class="font-semibold text-slate-700">Live</span>
                <span class="text-slate-300" aria-hidden="true">·</span>
                <span>Updated <span id="last-updated" class="font-medium tabular-nums text-slate-600">—</span></span>
              </div>
              <?php if ($canNotify): ?>
                <button type="button" id="notify-all" disabled
                  class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition duration-200 hover:shadow-lg hover:shadow-[#6010FF]/30 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none disabled:hover:brightness-100 sm:w-auto">
                  <i class="fas fa-paper-plane text-xs" aria-hidden="true"></i>
                  <span data-label>Notify All</span>
                </button>
              <?php endif; ?>
            </div>
          </div>
        </header>

        <!-- ===== STATE MESSAGES ===== -->
        <div id="no-period-state" class="hidden rounded-2xl border border-slate-200 bg-white p-8 text-center shadow-sm sm:p-12" role="status">
          <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
            <i class="far fa-calendar-xmark text-xl" aria-hidden="true"></i>
          </div>
          <h2 class="mt-4 text-base font-semibold text-slate-900">No active evaluation period</h2>
          <p class="mx-auto mt-1.5 max-w-md text-sm leading-relaxed text-slate-500">
            Monitoring is unavailable because there is no active <?= $e($departmentLabel) ?> evaluation period. Participation data will appear here once a period opens.
          </p>
        </div>

        <div id="error-state" class="hidden items-start gap-3 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800" role="alert">
          <i class="fas fa-circle-exclamation mt-0.5 text-rose-500" aria-hidden="true"></i>
          <div class="flex-1">
            <p class="font-semibold">Monitoring data could not be loaded</p>
            <p id="error-message" class="mt-0.5 text-rose-700">Please try again in a moment.</p>
          </div>
          <button type="button" data-retry class="shrink-0 rounded-lg border border-rose-200 bg-white px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">Retry</button>
        </div>

        <div id="monitoring-content" class="space-y-6">

          <!-- ===== KPI CARDS ===== -->
          <section aria-labelledby="overview-title">
            <div class="mb-4 flex items-start gap-3">
              <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
              <div>
                <h2 id="overview-title" class="text-base font-semibold tracking-tight text-slate-900">Participation Overview</h2>
                <p class="mt-0.5 text-xs text-slate-500">Live participation indicators for the active <?= $e($departmentLabel) ?> evaluation period</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
              <?php foreach ($kpis as [$label, $id, $description, $icon, $color]): $a = $accent[$color]; ?>
                <article class="group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br <?= $a['card'] ?> via-white to-white p-4 shadow-sm transition duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5">
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

          <!-- ===== PARTICIPATION RATE ===== -->
          <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="participation-title">
            <div class="flex flex-col gap-3 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
              <div class="flex items-center gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                  <i class="fa-solid fa-chart-pie text-sm" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                  <h2 id="participation-title" class="text-sm font-semibold tracking-tight text-slate-900">Overall Participation Rate</h2>
                  <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Students who submitted at least one required teacher evaluation</p>
                </div>
              </div>
              <span class="inline-flex w-fit items-center rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-1 text-xs font-semibold text-[#6010FF]"><?= $e($departmentLabel) ?></span>
            </div>

            <div class="grid grid-cols-1 items-center gap-8 p-5 sm:p-6 lg:grid-cols-[minmax(0,280px)_1fr] lg:gap-12">
              <!-- Donut -->
              <figure class="relative mx-auto aspect-square w-full max-w-[240px]">
                <svg viewBox="0 0 42 42" class="h-full w-full -rotate-90" role="img" aria-labelledby="donut-desc">
                  <desc id="donut-desc">Participation breakdown: completed, incomplete and unresponsive students</desc>
                  <circle cx="21" cy="21" r="15.9155" fill="none" stroke="#f1f5f9" stroke-width="4.2"></circle>
                  <circle id="donut-completed" class="donut-segment cursor-pointer" cx="21" cy="21" r="15.9155" fill="none" stroke="#10B981" stroke-width="4.2" stroke-dasharray="0 100" stroke-dashoffset="0"><title>Completed</title></circle>
                  <circle id="donut-incomplete" class="donut-segment cursor-pointer" cx="21" cy="21" r="15.9155" fill="none" stroke="#F59E0B" stroke-width="4.2" stroke-dasharray="0 100" stroke-dashoffset="0"><title>Incomplete</title></circle>
                  <circle id="donut-unresponsive" class="donut-segment cursor-pointer" cx="21" cy="21" r="15.9155" fill="none" stroke="#F43F5E" stroke-width="4.2" stroke-dasharray="0 100" stroke-dashoffset="0"><title>Unresponsive</title></circle>
                </svg>
                <figcaption class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center text-center">
                  <span id="participation-rate" class="text-3xl font-bold tabular-nums tracking-tight text-slate-900">—</span>
                  <span class="mt-0.5 text-[11px] font-semibold uppercase tracking-wider text-slate-400">Participation</span>
                </figcaption>
              </figure>

              <!-- Breakdown -->
              <div class="min-w-0 space-y-5">
                <div class="grid grid-cols-3 gap-2 sm:gap-3">
                  <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Participating</p>
                    <p id="participation-count" class="mt-1 text-xl font-bold tabular-nums text-slate-900">—</p>
                  </div>
                  <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Eligible</p>
                    <p id="participation-total" class="mt-1 text-xl font-bold tabular-nums text-slate-900">—</p>
                  </div>
                  <div class="rounded-xl border border-slate-100 bg-slate-50/70 p-3 sm:p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Remaining</p>
                    <p id="participation-remaining" class="mt-1 text-xl font-bold tabular-nums text-slate-900">—</p>
                  </div>
                </div>

                <ul class="space-y-3" aria-label="Participation legend">
                  <?php foreach ([
                    ['completed', 'Completed', 'All required evaluations submitted', 'bg-emerald-500', 'text-emerald-700'],
                    ['incomplete', 'Incomplete', 'Some evaluations still pending', 'bg-amber-500', 'text-amber-700'],
                    ['unresponsive', 'Unresponsive', 'No evaluation submitted yet', 'bg-rose-500', 'text-rose-700'],
                  ] as [$key, $label, $hint, $dot, $text]): ?>
                    <li class="rounded-xl border border-slate-100 px-4 py-3 transition hover:border-slate-200 hover:bg-slate-50/60" title="<?= $e($hint) ?>">
                      <div class="flex items-center justify-between gap-3">
                        <span class="flex min-w-0 items-center gap-2.5">
                          <span class="h-2.5 w-2.5 shrink-0 rounded-full <?= $dot ?>"></span>
                          <span class="text-sm font-semibold <?= $text ?>"><?= $label ?></span>
                          <span class="hidden truncate text-xs text-slate-400 sm:inline">· <?= $e($hint) ?></span>
                        </span>
                        <span class="shrink-0 text-sm tabular-nums text-slate-500"><strong id="legend-<?= $key ?>" class="font-bold text-slate-900">0</strong> <span id="legend-<?= $key ?>-pct" class="ml-1 text-xs text-slate-400">0%</span></span>
                      </div>
                      <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-slate-100">
                        <span id="legend-<?= $key ?>-bar" class="legend-bar block h-full rounded-full <?= $dot ?> transition-[width] duration-700 ease-out" style="width:0%"></span>
                      </div>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            </div>
          </section>

          <!-- ===== STUDENT MONITORING TABLE ===== -->
          <section aria-labelledby="student-monitoring-title" class="w-full min-w-0">
            <div class="mb-4 flex items-start gap-3">
              <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
              <div>
                <h2 class="text-base font-semibold tracking-tight text-slate-900">Student Evaluation Monitoring</h2>
                <p class="mt-0.5 text-xs text-slate-500">Track each student's teacher evaluation progress and identify who still needs to complete them.</p>
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
                      <h3 id="student-monitoring-title" class="text-sm font-semibold tracking-tight text-slate-900">Student Participation</h3>
                      <span class="inline-flex items-center gap-1.5 rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6010FF]">
                        <span id="table-count" class="tabular-nums">0</span><span>students</span>
                      </span>
                    </div>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Completed teacher evaluations against each student's required evaluations.</p>
                  </div>
                </div>
              </div>

              <!-- Filters -->
              <div class="grid grid-cols-1 gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 lg:flex lg:items-center">
                <div class="relative sm:col-span-2 lg:w-80">
                  <label for="student-search" class="sr-only">Search by Student ID or Name</label>
                  <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                  <input id="student-search" type="text" autocomplete="off" maxlength="100" placeholder="Search by Student ID or Name..."
                    class="h-10 w-full rounded-xl border border-slate-200 bg-white py-2 pl-10 pr-9 text-sm text-slate-700 shadow-sm outline-none transition duration-200 placeholder:text-slate-400 hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15">
                  <button id="clear-search" type="button" class="absolute right-2 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Clear search">
                    <i class="fas fa-xmark text-xs" aria-hidden="true"></i>
                  </button>
                </div>
                <div>
                  <label for="year-filter" class="sr-only">Year level</label>
                  <select id="year-filter" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15 lg:w-44">
                    <option value="">All Year Levels</option>
                  </select>
                </div>
                <div>
                  <label for="status-filter" class="sr-only">Status</label>
                  <select id="status-filter" class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15 lg:w-44">
                    <option value="">All Statuses</option>
                    <option value="Completed">Completed</option>
                    <option value="Incomplete">Incomplete</option>
                    <option value="Unresponsive">Unresponsive</option>
                  </select>
                </div>
                <button id="reset-filters" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] sm:col-span-2 lg:ml-auto">
                  <i class="fas fa-filter-circle-xmark text-xs" aria-hidden="true"></i>
                  Reset filters
                </button>
              </div>

              <div class="w-full min-w-0 overflow-x-auto" role="region" aria-label="Student evaluation monitoring results" tabindex="0">
                <table id="monitoring-student-table" class="w-full min-w-[820px] border-collapse text-left text-sm">
                  <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                    <tr>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Student ID</th>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Student Name</th>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Course</th>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Year Level</th>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Evaluations Submitted</th>
                      <th class="whitespace-nowrap px-5 py-3.5 font-semibold">Status</th>
                    </tr>
                  </thead>
                </table>
              </div>
            </div>
          </section>
        </div>
      </div>
    </main>
  </div>

  <?php $autoLogoutRole = 'admin';
  require __DIR__ . '/../partials/auto_logout.php'; ?>

  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.monitoringConfig = {
      department: <?= json_encode($department, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
      departmentLabel: <?= json_encode($departmentLabel, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>,
    };
  </script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
  <script type="module" src="<?= BASE_URL ?>assets/js/admin/evaluation_monitoring/index.js"></script>
</body>
</html>
