<?php
declare(strict_types=1);

require_once __DIR__ . '/../../app/init.php';
require_once __DIR__ . '/../../app/config/nav.php';

\App\middleware\AdminAuthMiddleware::handle();

\App\middleware\ApiSecurity::admin($pdo, true);

$adminContext = new \App\Services\Admin\AdminContext(new \App\Repositories\AdminRepo\AdminRepository($pdo));

$admin = $adminContext->getCurrentAdmin();

$role = $adminContext->getRole();

$navigation = $navigation[$role] ?? [];

$currentUrl = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$pageTitle = 'Archive Management';

// Shared control styles (same as the other management pages).
$control = 'mt-1 block h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-normal text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15';
$filterLabel = 'block text-[11px] font-semibold uppercase tracking-wide text-slate-500';
$secondaryButton = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98] disabled:cursor-not-allowed disabled:opacity-50';
$primaryButton = 'inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 disabled:shadow-none';
$thead = 'border-b border-slate-200 bg-slate-50/80 text-[11px] uppercase tracking-wider text-slate-500';

// KPI card accents, matching Evaluation Monitoring.
$accent = [
    'violet' => ['card' => 'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'bar' => 'from-[#6010FF] to-[#6010FF]/10', 'icon' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15'],
    'emerald' => ['card' => 'from-emerald-50/80 hover:border-emerald-200', 'bar' => 'from-emerald-500 to-emerald-500/10', 'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100'],
    'blue' => ['card' => 'from-blue-50/80 hover:border-blue-200', 'bar' => 'from-blue-500 to-blue-500/10', 'icon' => 'bg-blue-50 text-blue-600 ring-blue-100'],
    'amber' => ['card' => 'from-amber-50/80 hover:border-amber-200', 'bar' => 'from-amber-500 to-amber-500/10', 'icon' => 'bg-amber-50 text-amber-600 ring-amber-100'],
];

$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archive Management | Smart-Eval</title>
    <?php require __DIR__ . '/../../public/assets/includes/head.php'; ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Table and pagination styles are shared in custom.css (#archive-table_wrapper, #recovery-log-table_wrapper). -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-slate-50 pb-xl text-slate-800">

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
                            <a href="<?= BASE_URL ?>dashboard?dept=college" class="font-medium transition hover:text-[#6010FF]">Dashboard</a>
                            <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                            <span class="font-medium">Data Management</span>
                            <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                            <span aria-current="page" class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]">Archive Management</span>
                        </nav>

                        <div class="space-y-1.5">
                            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Archive Management</h1>
                            <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                                Manage archived evaluation records, backup recovery, and recovery activity.
                            </p>
                        </div>
                    </div>

                    <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
                        <button type="button" id="refresh-archive" class="<?= $secondaryButton ?>">
                            <i class="fa-solid fa-rotate text-xs" aria-hidden="true"></i>Refresh
                        </button>
                    </div>
                </div>
            </header>

            <div id="archive-notice" role="status" aria-live="polite" class="hidden rounded-2xl border border-slate-200 bg-white p-4 text-sm shadow-sm"></div>

            <!-- ===== KPI CARDS ===== -->
            <section aria-labelledby="overview-title">
                <div class="mb-4 flex items-start gap-3">
                    <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
                    <div>
                        <h2 id="overview-title" class="text-base font-semibold tracking-tight text-slate-900">Archive Overview</h2>
                        <p class="mt-0.5 text-xs text-slate-500">Archived periods, backups and recovery activity</p>
                    </div>
                </div>

                <div id="archive-overview" aria-busy="true" class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-4">
                    <?php foreach ([
                        ['archived_periods', 'Archived Periods', 'Total archived evaluation periods', 'fa-box-archive', 'violet'],
                        ['verified_backups', 'Verified Backups', 'Backups successfully verified', 'fa-shield-halved', 'emerald'],
                        ['recovery_operations', 'Recovery Operations', 'Total recovery attempts', 'fa-clock-rotate-left', 'blue'],
                        ['archive_issues', 'Archive Issues', 'Archive issues requiring attention', 'fa-triangle-exclamation', 'amber'],
                    ] as [$key, $label, $subtitle, $icon, $color]): $a = $accent[$color]; ?>
                        <article class="group relative flex min-h-[150px] flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br <?= $a['card'] ?> via-white to-white p-4 shadow-sm transition duration-200 hover:shadow-md motion-safe:hover:-translate-y-0.5 sm:p-5">
                            <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r <?= $a['bar'] ?>"></span>
                            <div class="flex items-start justify-between">
                                <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1 <?= $a['icon'] ?>"><i class="fa-solid <?= $icon ?> text-sm" aria-hidden="true"></i></span>
                            </div>
                            <div class="mt-4">
                                <h3 class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 sm:text-xs"><?= $label ?></h3>
                                <p data-metric="<?= $key ?>" class="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900 sm:text-3xl"><?= $skeleton ?></p>
                                <p class="mt-1 text-xs leading-snug text-slate-500"><?= $subtitle ?></p>
                                <?php if ($key === 'recovery_operations'): ?>
                                    <p class="mt-1 text-xs text-amber-700">Operation-level history is not recorded yet.</p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>

            <!-- ===== TABS ===== -->
            <section class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

                <div role="tablist" aria-label="Archive management" class="flex overflow-x-auto border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-3 sm:px-4">
                    <?php foreach ([
                        'records' => ['Archived Records', 'fa-box-archive'],
                        'recovery' => ['Backup Recovery', 'fa-cloud-arrow-up'],
                        'logs' => ['Recovery Logs', 'fa-clock-rotate-left'],
                    ] as $id => [$label, $icon]): ?>
                        <!-- navigation.js toggles border-violet-600/text-violet-700 vs border-transparent/text-slate-500 -->
                        <button type="button" role="tab" id="tab-<?= $id ?>" aria-controls="panel-<?= $id ?>" aria-selected="<?= $id === 'records' ? 'true' : 'false' ?>" tabindex="<?= $id === 'records' ? '0' : '-1' ?>"
                            data-tab="<?= $id ?>" class="inline-flex min-h-14 shrink-0 items-center gap-2 border-b-2 px-4 text-sm font-semibold transition-colors hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#6010FF] <?= $id === 'records' ? 'border-violet-600 text-violet-700' : 'border-transparent text-slate-500' ?>">
                            <i class="fa-solid <?= $icon ?> text-xs" aria-hidden="true"></i><?= $label ?>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- ===== ARCHIVED RECORDS ===== -->
                <div id="panel-records" role="tabpanel" aria-labelledby="tab-records">

                    <div class="px-5 pt-5 sm:px-6">
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900">Archived Evaluation Records</h2>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Browse archived evaluation periods, check backup status, and manage historical records.</p>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 border-y border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 xl:grid-cols-5">
                        <label class="<?= $filterLabel ?>">Department<select id="archive-department" class="<?= $control ?>">
                            <option value="">All Departments</option>
                            <option value="college">College</option>
                            <option value="shs">SHS</option>
                        </select></label>

                        <label class="<?= $filterLabel ?>">Academic Year<select id="archive-year" class="<?= $control ?>">
                            <option value="">All Years</option>
                        </select></label>

                        <label class="<?= $filterLabel ?>">Semester<select id="archive-semester" class="<?= $control ?>">
                            <option value="">All Semesters</option>
                        </select></label>

                        <label class="<?= $filterLabel ?>">Backup Status<select id="archive-backup_status" class="<?= $control ?>">
                            <option value="">All Statuses</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="failed">Failed</option>
                            <option value="missing">Missing job</option>
                        </select></label>

                        <label class="<?= $filterLabel ?>">Search<input id="archive-search" type="search" maxlength="100" placeholder="Year or period ID" class="<?= $control ?> placeholder:text-slate-400"></label>
                    </div>

                    <div class="px-5 sm:px-6">
                        <p class="mt-3 flex items-start gap-2 text-xs leading-5 text-slate-500">
                            <i class="fa-solid fa-circle-info mt-1 text-[10px] text-slate-400" aria-hidden="true"></i>
                            Verification checks the encrypted backup checksum. Archive deletion is disabled pending an approved retention policy, recoverability checks, and an audited deletion service.
                        </p>
                        <div id="records-state" role="status" aria-live="polite" class="mt-2 text-sm text-slate-600"></div>
                    </div>

                    <div class="mt-3 w-full min-w-0 overflow-x-auto" role="region" aria-label="Archived records" tabindex="0">
                        <table id="archive-table" class="w-full min-w-[980px] border-collapse text-left text-sm">
                            <caption class="sr-only">Archived evaluation periods</caption>
                            <thead class="<?= $thead ?>">
                                <tr>
                                    <?php foreach (['#', 'Evaluation Period', 'Department', 'Date Archived', 'Backup Status', 'Record Summary', 'Actions'] as $label): ?>
                                        <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold <?= $label === 'Actions' ? 'text-right' : '' ?>"><?= $label ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <!-- ===== BACKUP RECOVERY ===== -->
                <div id="panel-recovery" role="tabpanel" aria-labelledby="tab-recovery" class="hidden p-5 sm:p-6">

                    <h2 class="text-sm font-semibold tracking-tight text-slate-900">Restore Evaluation Backup</h2>
                    <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Recover archived evaluation data from an encrypted backup file.</p>

                    <div class="mt-5 grid gap-5 xl:grid-cols-3">

                        <div class="space-y-4 xl:col-span-2">

                            <form id="backup-upload-form" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                                <h3 class="flex items-center gap-2.5 text-sm font-semibold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#6010FF] text-xs font-bold text-white">1</span>Upload File
                                </h3>

                                <label for="backup-file" class="mt-4 block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Encrypted SMART-EVAL backup</label>

                                <input id="backup-file" name="backup" type="file" accept=".enc" required aria-describedby="backup-file-help"
                                    class="mt-2 block w-full cursor-pointer rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-4 text-sm text-slate-600 transition hover:border-[#6010FF]/40 focus:border-[#6010FF] focus:outline-none focus:ring-2 focus:ring-[#6010FF]/15 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-[#6010FF]/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-[#6010FF]">

                                <p id="backup-file-help" class="mt-2 text-xs text-slate-500">Choose a .json.enc file, up to 2 MiB. Temporary files are removed after validation.</p>

                                <button id="validate-backup" type="submit" class="<?= $primaryButton ?> mt-4 disabled:cursor-wait">
                                    <i class="fa-solid fa-shield-halved text-xs" aria-hidden="true"></i>Validate &amp; Preview
                                </button>

                                <p id="upload-state" role="status" aria-live="polite" class="mt-3 text-sm text-slate-600"></p>
                            </form>

                            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm" aria-labelledby="preview-title">
                                <h3 id="preview-title" class="flex items-center gap-2.5 text-sm font-semibold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-[#6010FF] text-xs font-bold text-white">2</span>Validate &amp; Preview
                                </h3>
                                <div id="backup-preview" class="mt-4 text-sm text-slate-500">Upload a backup to inspect its evaluation period and record counts.</div>
                            </section>

                            <section class="rounded-2xl border border-slate-200 bg-slate-50/70 p-5">
                                <h3 class="flex items-center gap-2.5 text-sm font-semibold text-slate-900">
                                    <span class="flex h-6 w-6 items-center justify-center rounded-full bg-slate-300 text-xs font-bold text-white">3</span>Confirm &amp; Restore
                                </h3>
                                <p id="restore-unavailable" class="mt-2 text-sm leading-6 text-slate-600">Restoration is disabled until the archive recovery service and operation-level audit logging are implemented and tested. Validation does not restore any records.</p>
                                <button id="restore-backup" type="button" disabled aria-describedby="restore-unavailable" class="<?= $primaryButton ?> mt-4">
                                    <i class="fa-solid fa-rotate-left text-xs" aria-hidden="true"></i>Restore to Archive
                                </button>
                            </section>
                        </div>

                        <aside class="h-fit overflow-hidden rounded-2xl border border-[#6010FF]/15 bg-gradient-to-br from-[#6010FF]/[0.06] via-white to-white p-5 shadow-sm">
                            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-900">
                                <i class="fa-solid fa-list-check text-xs text-[#6010FF]" aria-hidden="true"></i>Recovery Guidelines
                            </h3>
                            <ol class="mt-4 space-y-2.5 text-sm text-slate-700">
                                <?php foreach (['Upload Backup', 'Decrypt &amp; Validate', 'Preview Backup Details', 'Check Existing Records', 'Confirm &amp; Restore'] as $i => $step): ?>
                                    <li class="flex items-center gap-2.5">
                                        <span class="flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-[#6010FF]/10 text-[11px] font-bold tabular-nums text-[#6010FF]"><?= $i + 1 ?></span><?= $step ?>
                                    </li>
                                <?php endforeach; ?>
                            </ol>
                            <p class="mt-5 border-t border-[#6010FF]/10 pt-4 text-xs leading-5 text-slate-600">Only encrypted SMART-EVAL backup files are accepted. Restoration targets the archive database, not the live database. Existing archived periods cannot be overwritten through the standard recovery process.</p>
                        </aside>
                    </div>
                </div>

                <!-- ===== RECOVERY LOGS ===== -->
                <div id="panel-logs" role="tabpanel" aria-labelledby="tab-logs" class="hidden">

                    <div class="px-5 pt-5 sm:px-6">
                        <h2 class="text-sm font-semibold tracking-tight text-slate-900">Recovery Activity Logs</h2>
                        <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Review recorded recovery activity and its details.</p>

                        <div role="note" class="mt-4 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50/60 px-4 py-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                                <i class="fas fa-circle-info text-sm" aria-hidden="true"></i>
                            </span>
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="text-sm font-semibold text-slate-800">Audit Log Coverage</h3>
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-semibold text-amber-800">Current Limitation</span>
                                </div>
                                <p class="mt-1 text-xs leading-5 text-slate-600">
                                    This table tracks individual record actions, not complete backup restoration attempts.
                                    Recovery outcomes, failure reasons, and restored-record totals are not currently recorded.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 grid grid-cols-1 gap-3 border-y border-slate-100 bg-slate-50/70 px-5 py-3 sm:grid-cols-2 sm:px-6 xl:grid-cols-4">
                        <label class="<?= $filterLabel ?>">Evaluation Period
                            <select id="logs-period_id" class="<?= $control ?>">
                                <option value="">All Periods</option>
                            </select>
                        </label>

                        <label class="<?= $filterLabel ?>">Department
                            <select id="logs-department" class="<?= $control ?>">
                                <option value="">All Departments</option>
                                <option value="college">College</option>
                                <option value="shs">SHS</option>
                            </select>
                        </label>

                        <label class="<?= $filterLabel ?> sm:col-span-2 xl:col-span-1">Search
                            <input id="logs-search" type="search" maxlength="100" placeholder="Record, action, or period ID" class="<?= $control ?> placeholder:text-slate-400">
                        </label>
                    </div>

                    <div class="px-5 sm:px-6">
                        <div id="logs-state" role="status" aria-live="polite" class="mt-3 text-sm text-slate-600"></div>
                    </div>

                    <div class="mt-3 w-full min-w-0 overflow-x-auto" role="region" aria-label="Recovery logs" tabindex="0">
                        <table id="recovery-log-table" class="w-full min-w-[1040px] border-collapse text-left text-sm">
                            <caption class="sr-only">Read-only record recovery audit entries</caption>
                            <thead class="<?= $thead ?>">
                                <tr>
                                    <?php foreach (['#', 'Recovery Date', 'Evaluation Period', 'Department', 'Administrator', 'Result', 'Details', 'Actions'] as $label): ?>
                                        <th scope="col" class="whitespace-nowrap px-5 py-3.5 font-semibold <?= $label === 'Actions' ? 'text-right' : '' ?>"><?= $label ?></th>
                                    <?php endforeach; ?>
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
<?php require __DIR__ . '/../partials/modals/archive_modal/details.php'; ?>
<script>
window.BASE_URL = <?= json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_AMP) ?>;
window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_AMP) ?>;
</script>
<script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
<script type="module" src="<?= BASE_URL ?>assets/js/admin/archive_management/index.js"></script>
<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
