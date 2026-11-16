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

$pageTitle = "Manage Questionnaires";

$e = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

// KPI card accents, matching Evaluation Monitoring.
$accent = [
    'violet' => ['card' => 'from-[#6010FF]/[0.06] hover:border-[#6010FF]/30', 'bar' => 'from-[#6010FF] to-[#6010FF]/10', 'icon' => 'bg-[#6010FF]/10 text-[#6010FF] ring-[#6010FF]/15', 'hover' => 'group-hover:text-[#6010FF]'],
    'emerald' => ['card' => 'from-emerald-50/80 hover:border-emerald-200', 'bar' => 'from-emerald-500 to-emerald-500/10', 'icon' => 'bg-emerald-50 text-emerald-600 ring-emerald-100', 'hover' => 'group-hover:text-emerald-500'],
    'blue' => ['card' => 'from-blue-50/80 hover:border-blue-200', 'bar' => 'from-blue-500 to-blue-500/10', 'icon' => 'bg-blue-50 text-blue-600 ring-blue-100', 'hover' => 'group-hover:text-blue-500'],
];

// Filled from the sets list the page already loads (questionRender.js).
// [label, value id, description, icon, accent]
$kpis = [
    ['Questionnaire Sets', 'kpi-sets', 'All evaluation question sets', 'fa-clipboard-list', 'violet'],
    ['In Use', 'kpi-in-use', 'Sets used by an active evaluation period', 'fa-circle-play', 'emerald'],
    ['Total Questions', 'kpi-questions', 'Questions across all sets', 'fa-list-check', 'blue'],
];

$skeleton = '<span class="inline-block h-8 w-16 animate-pulse rounded-md bg-slate-100 align-middle"></span>';
$control = 'h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#6010FF] focus:ring-2 focus:ring-[#6010FF]/15';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Questionnaires</title>

  <?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
                <span class="font-medium">Manage Evaluation</span>
                <i class="fas fa-chevron-right text-[9px] text-slate-300" aria-hidden="true"></i>
                <span class="rounded-md bg-[#6010FF]/10 px-2 py-0.5 font-semibold text-[#6010FF]">Questionnaires</span>
              </nav>

              <div class="space-y-1.5">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Manage Questionnaires</h1>
                <p class="max-w-2xl text-sm leading-relaxed text-slate-500">
                  Create, organize, and manage teacher evaluation questionnaires.
                </p>
              </div>
            </div>

            <div class="flex w-full shrink-0 flex-col items-stretch gap-2 sm:w-auto sm:flex-row sm:items-center xl:self-center">
              <button type="button"
                class="addSet inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-gradient-to-b from-[#7230FF] to-[#6010FF] px-4 text-sm font-semibold text-white shadow-md shadow-[#6010FF]/25 transition duration-200 hover:shadow-lg hover:shadow-[#6010FF]/30 hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2 active:scale-[0.98]">
                <i class="fas fa-plus text-xs" aria-hidden="true"></i>
                Add New Set
              </button>
            </div>
          </div>
        </header>

        <!-- ===== KPI CARDS ===== -->
        <section aria-labelledby="overview-title">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 id="overview-title" class="text-base font-semibold tracking-tight text-slate-900">Questionnaire Overview</h2>
              <p class="mt-0.5 text-xs text-slate-500">Question sets available for evaluation periods</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-3 sm:gap-4 xl:grid-cols-3">
            <?php foreach ($kpis as $i => [$label, $id, $description, $icon, $color]): $a = $accent[$color]; ?>
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

        <!-- ===== EVALUATION SETS ===== -->
        <section aria-labelledby="sets-title" class="w-full min-w-0">
          <div class="mb-4 flex items-start gap-3">
            <span class="mt-0.5 h-8 w-1 rounded-full bg-gradient-to-b from-[#6010FF] to-[#8B5CF6]"></span>
            <div>
              <h2 class="text-base font-semibold tracking-tight text-slate-900">Evaluation Sets</h2>
              <p class="mt-0.5 text-xs text-slate-500">View and manage available questionnaire sets.</p>
            </div>
          </div>

          <div class="w-full min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-4 border-b border-slate-100 bg-gradient-to-r from-[#6010FF]/[0.05] to-white px-5 py-4 sm:px-6 lg:flex-row lg:items-center lg:justify-between">
              <div class="flex min-w-0 items-start gap-3">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
                  <i class="fa-solid fa-clipboard-list text-sm" aria-hidden="true"></i>
                </div>
                <div class="min-w-0">
                  <div class="flex flex-wrap items-center gap-2.5">
                    <h3 id="sets-title" class="text-sm font-semibold tracking-tight text-slate-900">Question Sets</h3>
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-[#6010FF]/15 bg-[#6010FF]/10 px-2.5 py-0.5 text-xs font-semibold text-[#6010FF]">
                      <span id="table-count" class="tabular-nums">0</span><span>sets</span>
                    </span>
                  </div>
                  <p class="mt-0.5 text-xs leading-relaxed text-slate-500">Questions, usage and creation date for each set.</p>
                </div>
              </div>
            </div>

            <!-- Filters -->
            <div class="grid grid-cols-1 gap-2 border-b border-slate-100 bg-slate-50/70 px-5 py-3 sm:px-6 lg:flex lg:items-center">
              <div class="relative lg:w-80">
                <label for="questionnaireSearch" class="sr-only">Search questionnaire sets</label>
                <i class="fas fa-search pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-slate-400" aria-hidden="true"></i>
                <input id="questionnaireSearch" type="text" autocomplete="off" maxlength="100" placeholder="Search questionnaire set..."
                  class="<?= $control ?> py-2 pl-10 pr-9 placeholder:text-slate-400">
                <button id="clear-search" type="button" class="absolute right-2 top-1/2 hidden h-6 w-6 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 transition hover:bg-slate-100 hover:text-slate-600" aria-label="Clear search">
                  <i class="fas fa-xmark text-xs" aria-hidden="true"></i>
                </button>
              </div>
            </div>

            <div class="p-5 sm:p-6">
              <div class="cards-container grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3" aria-live="polite">
                <!-- Cards are generated by questionRender.js -->
              </div>
            </div>
          </div>
        </section>
      </div>
    </main>

    <!-- Modals -->
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/add_set_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/confirmation_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/manage_questions_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/edit_questions_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/edit_set_modal.php'; ?>

  </div>

  <script>
    window.BASE_URL = <?= json_encode(BASE_URL) ?>;
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>

  <script src="<?= BASE_URL ?>assets/js/admin/manage_questionnaires/index.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
<!-- Floating Add button: appears after the page header scrolls out of view -->
<script type="module">
  import { initFloatingActions } from "<?= BASE_URL ?>assets/js/admin/shared/floating_actions.js";
  initFloatingActions([{ target: ".addSet", label: "Add New Set", icon: "fa-plus" }]);
</script>
<?php $autoLogoutRole = 'admin'; require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
