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

$control = 'mt-1 block min-h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-100';

$button = 'inline-flex min-h-11 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 disabled:cursor-not-allowed disabled:opacity-50';

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
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
</head>

<body class="bg-slate-50 text-slate-800">

<div class="relative mx-auto flex min-h-dvh max-w-screen-2xl">

    <?php require __DIR__ . '/../partials/header.php'; ?>
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="min-w-0 flex-1 pt-16 lg:ml-70">
        <div class="border-b border-slate-200 bg-white px-5 py-6 sm:px-8">

            <nav aria-label="Breadcrumb" class="mb-3 text-xs text-slate-500">
                <a href="<?= BASE_URL ?>dashboard?dept=college" class="hover:text-violet-700">
                    Dashboard
                </a>

                <span aria-hidden="true" class="mx-2">/</span>Data Management
                <span aria-hidden="true" class="mx-2">/</span><span aria-current="page">Archive Management</span>

            </nav>

            <div class="flex flex-wrap items-center justify-between gap-4">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-600 text-white">
                        <i class="fa-solid fa-box-archive" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h1 class="text-xl font-semibold tracking-tight sm:text-2xl">Archive Management</h1>
                        <p class="mt-1 text-sm text-slate-500">Manage archived evaluation records, backup recovery, and recovery activity.</p>
                    </div>

                </div>

                <button type="button" id="refresh-archive" class="<?= $button ?>">
                    <i class="fa-solid fa-rotate" aria-hidden="true"></i>Refresh
                </button>

            </div>

        </div>

        <div class="space-y-6 p-5 sm:p-8">

            <div id="archive-notice" role="status" aria-live="polite" class="hidden rounded-xl border border-slate-200 bg-white p-4 text-sm">
            </div>

            <section aria-label="Archive overview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4" id="archive-overview" aria-busy="true">

                <?php foreach ([
                    ['archived_periods', 'Archived Periods', 'Total archived evaluation periods', 'fa-box-archive'],
                    ['verified_backups', 'Verified Backups', 'Backups successfully verified', 'fa-shield-halved'],
                    ['recovery_operations', 'Recovery Operations', 'Total recovery attempts', 'fa-clock-rotate-left'],
                    ['archive_issues', 'Archive Issues', 'Archive issues requiring attention', 'fa-triangle-exclamation'],
                ] as [$key, $label, $subtitle, $icon]): ?>

                    <article class="rounded-xl border border-slate-200 bg-white p-5">

                        <div class="flex items-center justify-between gap-2">
                            <h2 class="text-sm font-medium text-slate-600"><?= $label ?></h2>
                            <i class="fa-solid <?= $icon ?> text-violet-500" aria-hidden="true"></i>
                        </div>

                        <p data-metric="<?= $key ?>" class="mt-3 text-2xl font-semibold tabular-nums">0</p>

                        <p class="mt-2 text-xs leading-5 text-slate-500"><?= $subtitle ?></p>

                        <?php if ($key === 'recovery_operations'): ?>
                            <p class="mt-1 text-xs text-amber-700">Operation-level history is not recorded yet.</p>
                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>
            </section>

            <section class="rounded-xl border border-slate-200 bg-white">

                <div role="tablist" aria-label="Archive management" class="flex overflow-x-auto border-b border-slate-200 px-4">

                    <?php foreach (['records' => 'Archived Records', 'recovery' => 'Backup Recovery', 'logs' => 'Recovery Logs'] as $id => $label): ?>

                        <button type="button" role="tab" id="tab-<?= $id ?>" aria-controls="panel-<?= $id ?>" aria-selected="<?= $id === 'records' ? 'true' : 'false' ?>" tabindex="<?= $id === 'records' ? '0' : '-1' ?>"
                            data-tab="<?= $id ?>" class="min-h-14 shrink-0 border-b-2 px-4 text-sm font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-violet-500 <?= $id === 'records' ? 'border-violet-600 text-violet-700' : 'border-transparent text-slate-500' ?>"><?= $label ?></button>
                    <?php endforeach; ?>

                </div>

                <div id="panel-records" role="tabpanel" aria-labelledby="tab-records" class="p-5 sm:p-6">

                    <h2 class="text-base font-semibold">
                        Archived Evaluation Records
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Browse archived evaluation periods, check backup status, and manage historical records.
                    </p>

                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                        <label class="text-xs font-medium text-slate-600">Department<select id="archive-department" class="<?= $control ?>">
                            <option value="">All Departments</option>
                            <option value="college">College</option>
                            <option value="shs">SHS</option>
                        </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Academic Year<select id="archive-year" class="<?= $control ?>">
                            <option value="">All Years</option>
                        </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Semester<select id="archive-semester" class="<?= $control ?>">
                            <option value="">All Semesters</option>
                        </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Backup Status<select id="archive-backup_status" class="<?= $control ?>">
                            <option value="">All Statuses</option>
                            <option value="verified">Verified</option>
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="failed">Failed</option>
                            <option value="missing">Missing job</option>
                        </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Search<input id="archive-search" type="search" maxlength="100" placeholder="Year or period ID" class="<?= $control ?>"></label>

                    </div>

                    <p class="mt-4 text-xs leading-5 text-slate-500">Verification checks the encrypted backup checksum. Archive deletion is disabled pending an approved retention policy, recoverability checks, and an audited deletion service.</p>

                    <div id="records-state" role="status" aria-live="polite" class="mt-3 text-sm text-slate-600"></div>

                    <div class="mt-4 overflow-x-auto">
                        <table id="archive-table" class="w-full text-left text-sm">
                            <caption class="sr-only">Archived evaluation periods</caption>
                            <thead class="bg-slate-50 text-xs text-slate-600">
                                <tr>
                                    <?php foreach (['#', 'Evaluation Period', 'Department', 'Date Archived', 'Backup Status', 'Record Summary', 'Actions'] as $label): ?>
                                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold"><?= $label ?></th>
                                    <?php endforeach; ?></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>

                <div id="panel-recovery" role="tabpanel" aria-labelledby="tab-recovery" class="hidden p-5 sm:p-6">

                    <h2 class="text-base font-semibold">Restore Evaluation Backup</h2>

                    <p class="mt-1 text-sm text-slate-500">Recover archived evaluation data from an encrypted backup file.</p>

                    <div class="mt-6 grid gap-6 xl:grid-cols-3">

                        <div class="space-y-5 xl:col-span-2">

                            <form id="backup-upload-form" class="rounded-xl border border-slate-200 p-5">

                                <h3 class="text-sm font-semibold"><span class="mr-2 text-violet-600">1</span>Upload File</h3>

                                <label for="backup-file" class="mt-4 block text-sm text-slate-600">Encrypted SMART-EVAL backup</label>

                                <input id="backup-file" name="backup" type="file" accept=".enc" required aria-describedby="backup-file-help"
                                    class="mt-2 block w-full rounded-lg border border-dashed border-slate-300 p-4 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-violet-50 file:px-3 file:py-2 file:text-violet-700">

                                <p id="backup-file-help" class="mt-2 text-xs text-slate-500">Choose a .json.enc file, up to 2 MiB. Temporary files are removed after validation.</p>

                                <button id="validate-backup" type="submit" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-lg bg-violet-600 px-4 text-sm font-semibold text-white hover:bg-violet-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-50">Validate &amp; Preview</button>

                                <p id="upload-state" role="status" aria-live="polite" class="mt-3 text-sm text-slate-600"></p>

                            </form>

                            <section class="rounded-xl border border-slate-200 p-5" aria-labelledby="preview-title">
                                <h3 id="preview-title" class="text-sm font-semibold"><span class="mr-2 text-violet-600">2</span>Validate &amp; Preview</h3>
                                <div id="backup-preview" class="mt-4 text-sm text-slate-500">Upload a backup to inspect its evaluation period and record counts.</div>
                            </section>

                            <section class="rounded-xl border border-slate-200 bg-slate-50 p-5">
                                <h3 class="text-sm font-semibold"><span class="mr-2 text-slate-500">3</span>Confirm &amp; Restore</h3>
                                <p id="restore-unavailable" class="mt-2 text-sm leading-6 text-slate-600">Restoration is disabled until the archive recovery service and operation-level audit logging are implemented and tested. Validation does not restore any records.</p>
                                <button
                                    id="restore-backup"
                                    type="button"
                                    disabled
                                    aria-describedby="restore-unavailable"
                                    class="mt-4 min-h-11 rounded-lg bg-violet-600 px-4 text-sm
                                        font-semibold text-white hover:bg-violet-700
                                        disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    Restore to Archive
                                </button>
                            </section>

                        </div>

                        <aside class="h-fit rounded-xl border border-violet-100 bg-violet-50 p-5">
                            <h3 class="text-sm font-semibold text-violet-900">Recovery Guidelines</h3>
                            <ol class="mt-4 list-inside list-decimal space-y-3 text-sm text-slate-700">
                                <li>Upload Backup</li>
                                <li>Decrypt &amp; Validate</li>
                                <li>Preview Backup Details</li>
                                <li>Check Existing Records</li>
                                <li>Confirm &amp; Restore</li>
                            </ol>

                            <p class="mt-5 border-t border-violet-200 pt-4 text-sm leading-6 text-violet-900">Only encrypted SMART-EVAL backup files are accepted. Restoration targets the archive database, not the live database. Existing archived periods cannot be overwritten through the standard recovery process.</p>

                        </aside>

                    </div>

                </div>

                <div id="panel-logs" role="tabpanel" aria-labelledby="tab-logs" class="hidden p-5 sm:p-6">

                    <h2 class="text-base font-semibold">Recovery Activity Logs</h2>

                    <p class="mt-1 text-sm text-slate-500">Review recorded recovery activity and its details.</p>

                    
                    <!-- RECOVERY LOG INFORMATION NOTICE -->
                    <div
                        role="note"
                        class="flex items-start gap-3 rounded-xl
                            border border-slate-200 bg-white
                            px-4 py-3.5 shadow-sm my-4"
                    >
                        <!-- Information Icon -->
                        <div
                            class="flex h-9 w-9 shrink-0
                                items-center justify-center
                                rounded-lg bg-indigo-50
                                text-indigo-600"
                        >
                            <i
                                class="fas fa-info-circle text-sm"
                                aria-hidden="true"
                            ></i>
                        </div>

                        <!-- Notice Content -->
                        <div class="min-w-0 flex-1">

                            <!-- Title -->
                            <div class="flex flex-wrap items-center gap-2">

                                <h3
                                    class="text-sm font-semibold
                                        text-slate-800"
                                >
                                    Audit Log Coverage
                                </h3>

                                <span
                                    class="rounded-md bg-slate-100
                                        px-2 py-0.5 text-[10px]
                                        font-medium text-slate-500"
                                >
                                    Current Limitation
                                </span>

                            </div>

                            <!-- Description -->
                            <p
                                class="mt-1 text-xs leading-5
                                    text-slate-500 sm:text-sm sm:leading-6"
                            >
                                This table tracks individual record actions,
                                not complete backup restoration attempts.
                                Recovery outcomes, failure reasons, and
                                restored-record totals are not currently recorded.
                            </p>

                        </div>
                    </div>


                    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">

                        <label class="text-xs font-medium text-slate-600">Evaluation Period
                            <select id="logs-period_id" class="<?= $control ?>">
                                <option value="">All Periods</option>
                            </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Department
                            <select id="logs-department" class="<?= $control ?>">
                                <option value="">All Departments</option>
                                <option value="college">College</option>
                                <option value="shs">SHS</option>
                            </select>
                        </label>

                        <label class="text-xs font-medium text-slate-600">Search
                            <input id="logs-search" type="search" maxlength="100" placeholder="Record, action, or period ID" class="<?= $control ?>">
                        </label>

                    </div>

                    <div id="logs-state" role="status" aria-live="polite" class="mt-3 text-sm text-slate-600"></div>

                    <div class="mt-4 overflow-x-auto">

                        <table id="recovery-log-table" class="w-full text-left text-sm">
                            <caption class="sr-only">Read-only record recovery audit entries</caption>

                            <thead class="bg-slate-50 text-xs text-slate-600">
                                <tr>
                                    <?php foreach (['#', 'Recovery Date', 'Evaluation Period', 'Department', 'Administrator', 'Result', 'Details', 'Actions'] as $label): ?>
                                        <th scope="col" class="whitespace-nowrap px-4 py-3 font-semibold"><?= $label ?></th>
                                    <?php endforeach; ?></tr></thead>
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
