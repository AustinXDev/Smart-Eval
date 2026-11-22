<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\middleware\AdminAuthMiddleware;
use App\Repositories\AdminRepo\AdminRepository;
use App\Services\Admin\AdminContext;

require_once __DIR__ . '/../../app/config/database.php';
require_once __DIR__ . '/../../app/config/nav.php';

AdminAuthMiddleware::handle();

$controller = new DashboardController(new AdminContext(new AdminRepository($pdo)), $navigation);
$data = $controller->index();

$admin      = $data['admin'];
$role       = $data['role'];
$navigation = $data['navigation'] ?? [];

// Activity logs are super-admin only (the API enforces this too).
if ($role !== 'super_admin') {
    header('Location: ' . BASE_URL . 'dashboard?dept=college');
    exit;
}

$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pageTitle = 'Activity Logs';

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// Tabs: one per actor type. [key, label, description, icon]
$tabs = [
    ['admin', 'Admin', 'Actions by administrators', 'fa-user-shield'],
    ['student', 'Student', 'Student sign-ins and evaluation progress', 'fa-user-graduate'],
    ['system', 'System', 'Background jobs: period activation and closing, reminder emails, teacher reports and archive backups', 'fa-gears'],
];

$control = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15';
$filterLabel = 'block text-[11px] font-semibold uppercase tracking-wide text-slate-500';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Activity Logs | Smart-Eval</title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <?php require __DIR__ . '/../partials/theme_head.php'; ?>

  <!-- Table and pagination styles: custom.css (.se-datatable). The class is added at runtime by
       createDataTable() in assets/js/admin/shared/datatable_config.js, so it is not in this markup. -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>
<body class="bg-slate-50 pb-xl">
  <div class="pb-lg relative mx-auto flex min-h-dvh max-w-screen-2xl">

    <?php require __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-h-screen w-full min-w-0 px-2 pb-8 pt-20 lg:ml-70 lg:px-10">
      <div class="mx-auto max-w-[1500px] space-y-6">

        <!-- ===== PAGE HEADER ===== -->
        <header class="overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#6010FF]/[0.07] via-white to-white shadow-sm">
          <div class="flex flex-col gap-6 p-5 sm:p-6 xl:flex-row xl:items-center xl:justify-between">
            <div class="min-w-0 flex-1">
              <nav aria-label="Breadcrumb" class="mb-3 flex flex-wrap items-center gap-2 text-xs text-slate-500">
                <span class="font-medium">Data Management</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                <span aria-current="page" class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]">Activity Logs</span>
              </nav>

              <div class="space-y-1.5">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Activity Logs</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                  Who did what and when, across administrators, students and the system scheduler.
                </p>
              </div>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
              <button type="button" id="refresh-logs"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98]">
                <i class="fa-solid fa-rotate text-xs" aria-hidden="true"></i>Refresh
              </button>
            </div>
          </div>
        </header>

        <!-- ===== PRIVACY NOTE ===== -->
        <div role="note" class="flex items-start gap-3 rounded-2xl border border-[#6010FF]/15 bg-[#6010FF]/[0.04] px-4 py-3">
          <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#6010FF]/10 text-[#6010FF]">
            <i class="fas fa-user-lock text-sm" aria-hidden="true"></i>
          </span>
          <p class="text-xs leading-5 text-slate-600 sm:text-sm sm:leading-6">
            Student evaluation entries show only how many evaluations were submitted. Teachers, ratings and comments are never logged,
            and passwords, tokens and verification codes are never stored.
          </p>
        </div>

        <!-- ===== LOGS ===== -->
        <section class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm" aria-labelledby="logs-title">
          <h2 id="logs-title" class="sr-only">Activity log entries</h2>

          <!-- Tabs: Admin / Student / System -->
          <div role="tablist" aria-label="Log type" class="flex overflow-x-auto border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-3 sm:px-4">
            <?php foreach ($tabs as $i => [$key, $label, $hint, $icon]): ?>
              <button type="button" role="tab" id="tab-<?= $key ?>" data-log-tab="<?= $key ?>"
                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>" aria-controls="logs-panel" title="<?= $e($hint) ?>"
                class="inline-flex min-h-14 shrink-0 items-center gap-2 border-b-2 px-4 text-sm font-semibold transition-colors hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#6010FF] <?= $i === 0 ? 'border-[#6010FF] text-[#6010FF]' : 'border-transparent text-slate-500' ?>">
                <i class="fa-solid <?= $icon ?> text-xs" aria-hidden="true"></i><?= $e($label) ?>
                <span data-log-count="<?= $key ?>" class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-semibold tabular-nums text-slate-600">0</span>
              </button>
            <?php endforeach; ?>
          </div>

          <div id="logs-panel" role="tabpanel" aria-labelledby="tab-admin">

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-3 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 xl:grid-cols-[minmax(0,1.6fr)_repeat(4,minmax(0,1fr))_auto] xl:items-end">
              <label class="<?= $filterLabel ?> relative sm:col-span-2 xl:col-span-1">Search
                <span class="relative mt-1 block">
                  <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                  <input id="log-search" type="text" autocomplete="off" maxlength="100" placeholder="Name, ID, or description..."
                    class="<?= $control ?> py-2 pl-10 pr-9 font-normal normal-case tracking-normal placeholder:text-slate-400">
                  <button id="clear-search" type="button" class="absolute right-2 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Clear search">
                    <i class="fas fa-xmark text-xs" aria-hidden="true"></i>
                  </button>
                </span>
              </label>

              <label class="<?= $filterLabel ?>">Module
                <select id="log-module" class="<?= $control ?> mt-1 font-normal normal-case tracking-normal">
                  <option value="">All Modules</option>
                </select>
              </label>

              <label class="<?= $filterLabel ?>">Department
                <select id="log-department" class="<?= $control ?> mt-1 font-normal normal-case tracking-normal">
                  <option value="">All Departments</option>
                  <option value="college">College</option>
                  <option value="shs">SHS</option>
                </select>
              </label>

              <label class="<?= $filterLabel ?>">From
                <input id="log-from" type="date" class="<?= $control ?> mt-1 font-normal normal-case tracking-normal">
              </label>

              <label class="<?= $filterLabel ?>">To
                <input id="log-to" type="date" class="<?= $control ?> mt-1 font-normal normal-case tracking-normal">
              </label>

              <button id="reset-filters" type="button" class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] sm:col-span-2 xl:col-span-1">
                <i class="fas fa-filter-circle-xmark text-xs" aria-hidden="true"></i>
                Reset
              </button>
            </div>

            <div class="w-full min-w-0 overflow-x-auto" role="region" aria-label="Activity log entries" tabindex="0">
              <table id="activity-log-table" class="w-full min-w-[1040px] border-collapse text-left text-sm">
                <caption class="sr-only">Activity log entries, newest first</caption>
                <thead class="border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500">
                  <tr>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">Date &amp; Time</th>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">Performed By</th>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">Module</th>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">Action</th>
                    <th scope="col" class="px-5 py-3.5 font-semibold">Description</th>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">Department</th>
                    <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold">IP Address</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </section>
      </div>
    </main>
  </div>

  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
  <script type="module" src="<?= BASE_URL ?>assets/js/admin/activity_logs/index.js"></script>

  <?php $autoLogoutRole = 'admin'; require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
