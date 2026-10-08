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

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Questionnaires</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

  <!-- Wrapper -->
  <div class="max-w-screen-2xl h-dvh mx-auto flex  relative overflow-hidden"> 

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
                <svg class="h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2.25-12.75H7.5A2.25 2.25 0 0 0 5.25 5.5v13A2.25 2.25 0 0 0 7.5 20.75h9A2.25 2.25 0 0 0 18.75 18.5v-9.75L15 5.25H7.5Z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 5.25V9h3.75"/>
                </svg>
              </div>

              <div>
                <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
                  Manage Questionnaires
                </h1>

                <p class="mt-0.5 text-xs font-medium text-slate-500">
                  Create, organize, and manage teacher evaluation questionnaires
                </p>
              </div>
            </div>

            <button
              type="button"
              class="addSet inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/15 active:translate-y-0 active:scale-[0.98]"
            >
              <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-white/15">
                <i class="fas fa-plus text-[11px]"></i>
              </span>
              <span class="whitespace-nowrap">Add New Set</span>
            </button>
          </div>
        </div>
      </div>

      <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">

      <!-- =========================================================
          QUESTIONNAIRE CONTENT
      ========================================================== -->
      <section
        class="
          overflow-hidden
          rounded-2xl
          border
          border-slate-200
          bg-white
          shadow-sm
        "
      >

        <!-- =======================================================
            SECTION HEADER
        ======================================================== -->
        <div
          class="
            border-b
            border-slate-200
            bg-white
            px-5
            py-5
            sm:px-6
          "
        >

          <div
            class="
              flex
              flex-col
              gap-4
              sm:flex-row
              sm:items-center
              sm:justify-between
            "
          >

            <!-- Section Title -->
            <div class="flex items-center gap-3">

              <div
                class="
                  flex h-10 w-10
                  shrink-0
                  items-center justify-center
                  rounded-xl
                  bg-violet-50
                  text-violet-600
                "
              >
                <i class="fas fa-clipboard-list text-sm"></i>
              </div>

              <div>

                <h2
                  class="
                    text-sm
                    font-semibold
                    text-slate-900
                    sm:text-base
                  "
                >
                  Evaluation Sets
                </h2>

                <p class="mt-0.5 text-xs text-slate-400">
                  View and manage available questionnaire sets
                </p>

              </div>

            </div>


            <div class="relative w-full sm:w-[240px]">

              <span
                class="
                  pointer-events-none
                  absolute
                  inset-y-0
                  left-3
                  flex
                  items-center
                  text-slate-400
                "
              >
                <i class="fas fa-search text-xs"></i>
              </span>

              <input
                type="text"
                id="questionnaireSearch"
                placeholder="Search questionnaire..."
                class="
                  h-10
                  w-full
                  rounded-xl
                  border
                  border-slate-200
                  bg-slate-50
                  pl-9
                  pr-3
                  text-sm
                  text-slate-700
                  outline-none
                  transition

                  placeholder:text-slate-400

                  hover:border-slate-300

                  focus:border-violet-400
                  focus:bg-white
                  focus:ring-4
                  focus:ring-violet-500/10
                "
              >

            </div>

          </div>

        </div>


        <!-- =======================================================
            QUESTIONNAIRE CARDS
        ======================================================== -->
        <div class="p-5 sm:p-6">

          <div
            class="
              cards-container
              grid
              grid-cols-1
              gap-4
              md:grid-cols-2
              xl:grid-cols-3
            "
          >

            <!--
              Cards are generated dynamically by JavaScript.
            -->

          </div>

        </div>


        <!-- =======================================================
            FOOTER
        ======================================================== -->
        <div
          class="
            border-t
            border-slate-100
            bg-slate-50/50
            px-5
            py-3
            sm:px-6
          "
        >

          <div
            class="
              flex
              flex-col
              gap-2
              text-xs
              text-slate-400
              sm:flex-row
              sm:items-center
              sm:justify-between
            "
          >

            <span>
              Questionnaire records
            </span>

            <span class="inline-flex items-center gap-1.5">

              <span
                class="
                  h-1.5
                  w-1.5
                  rounded-full
                  bg-emerald-500
                "
              ></span>

              Evaluation sets are ready to manage

            </span>

          </div>

        </div>

      </section>

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
  window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
  </script> 


  <script src="<?= BASE_URL ?>assets/js/admin/manage_questionnaires/index.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>