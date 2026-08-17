<?php 

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\middleware\AdminAuthMiddleware;
use App\Repositories\AdminRepository;
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

    <main class="pt-17 lg:ml-80 lg:mr-10 p-6 w-full  min-h-screen overflow-y-auto">

      <div class="p-6 space-y-6 h-full">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

          <div class="flex items-center gap-3">
            <div class="bg-[#6010ff] p-2.5 rounded-xl flex-shrink-0">
              <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
              </svg>
            </div>
            <div>
              <h1 class="text-md lg:text-xl font-semibold text-[#2D3748] tracking-tight">
                Manage Questionnaires
              </h1>
              <p class="text-xs text-[#6010ff]/60 font-medium">Manage &amp; Organize Teacher Information</p>
            </div>
          </div>

          <button class="addSet bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md shadow hover:opacity-90 transition">
            + Add New Set
          </button>

        </div>

        <!-- CARDS GRID -->
        <div class="cards-container grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">

          <!-- Fill by Js -->

        </div>

      </div>

    </main>

    <!-- Modals -->
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/add_set_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/confirmation_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/manage_questions_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/edit_questions_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/manage_questionnaires_modal/edit_set_modal.php'; ?>

  </div>

  <script src="<?= BASE_URL ?>assets/js/admin/manage_questionnaires/action.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/admin/manage_questionnaires/list.js" type="module"></script>
</body>
</html>