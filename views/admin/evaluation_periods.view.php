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

$pageTitle = "Manage Evaluation";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Evaluation Periods</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <!-- Modal JS -->
  <script src="<?= BASE_URL ?>assets/js/modal/modal.js" type="module"></script>

</head>
<body>

  <!-- Wrapper -->
  <div class="max-w-screen-2xl h-dvh mx-auto flex  relative overflow-hidden"> 

    <!-- header -->
    <?php require_once __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="pt-17 lg:ml-80 lg:mr-10 p-6 w-full  min-h-screen overflow-y-auto">
      <div class="p-4 lg:p-6 space-y-6">

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
                Manage Evaluation
              </h1>
              <p class="text-xs text-[#6010ff]/60 font-medium">Manage &amp; Organize Evaluation</p>
            </div>
          </div>

          <button
            class="createPeriodBtn bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md shadow hover:opacity-90 transition">
            + Create New Period
          </button>
          
        </div>

        <!-- SUMMARY CARDS -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

  <!-- COLLEGE CARD -->
  <div
    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">

    <!-- Accent -->
    <div class="absolute left-0 top-0 h-full w-1 bg-indigo-500"></div>

    <div class="space-y-6">

      <!-- HEADER -->
      <div class="flex items-start justify-between gap-4">

        <div class="flex items-start gap-4">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 14l9-5-9-5-9 5 9 5z" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M5 12v5c3.5 3 10.5 3 14 0v-5" />
            </svg>
          </div>

          <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
              College
            </p>

            <h2 class="mt-1 text-base font-bold text-slate-900">
              Active Period
            </h2>

            <p id="collegeYear" class="mt-1 text-sm font-medium text-indigo-600">
              --
            </p>

            <p id="collegeSem" class="mt-0.5 text-xs text-slate-500">
              --
            </p>
          </div>

        </div>

        <!-- STATUS -->
        <span id="collegeStatus"
          class="inline-flex shrink-0 items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">

          <span class="h-2 w-2 rounded-full bg-slate-400"></span>
          No Active
        </span>

      </div>


      <!-- PROGRESS SECTION -->
      <div>

        <div class="mb-2 flex items-center justify-between">

          <div>
            <p class="text-xs font-medium text-slate-500">
              Student Completion
            </p>

            <p id="collegeProgressCount"
              class="mt-1 text-xs text-slate-400">
              0 / 0 Students
            </p>
          </div>

          <p id="collegeProgressText"
            class="text-lg font-bold text-slate-900">
            0%
          </p>

        </div>

        <!-- PROGRESS BAR -->
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
          <div
            id="collegeProgressBar"
            class="h-full rounded-full bg-indigo-500 transition-all duration-700 ease-out"
            style="width:0%">
          </div>
        </div>

      </div>

    </div>
  </div>


  <!-- SHS CARD -->
  <div
    class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">

    <!-- Accent -->
    <div class="absolute left-0 top-0 h-full w-1 bg-cyan-500"></div>

    <div class="space-y-6">

      <!-- HEADER -->
      <div class="flex items-start justify-between gap-4">

        <div class="flex items-start gap-4">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan-50 text-cyan-600">

            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-5 w-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 19.5A2.5 2.5 0 016.5 17H20" />
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z" />
            </svg>

          </div>

          <div>

            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">
              Senior High School
            </p>

            <h2 class="mt-1 text-base font-bold text-slate-900">
              Active Period
            </h2>

            <p id="shsYear"
              class="mt-1 text-sm font-medium text-cyan-600">
              --
            </p>

            <p id="shsSem"
              class="mt-0.5 text-xs text-slate-500">
              --
            </p>

          </div>

        </div>


        <!-- STATUS -->
        <span id="shsStatus"
          class="inline-flex shrink-0 items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-500">

          <span class="h-2 w-2 rounded-full bg-slate-400"></span>
          No Active

        </span>

      </div>


      <!-- PROGRESS SECTION -->
      <div>

        <div class="mb-2 flex items-center justify-between">

          <div>

            <p class="text-xs font-medium text-slate-500">
              Student Completion
            </p>

            <p id="shsProgressCount"
              class="mt-1 text-xs text-slate-400">
              0 / 0 Students
            </p>

          </div>

          <p id="shsProgressText"
            class="text-lg font-bold text-slate-900">
            0%
          </p>

        </div>


        <!-- PROGRESS BAR -->
        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">

          <div
            id="shsProgressBar"
            class="h-full rounded-full bg-cyan-500 transition-all duration-700 ease-out"
            style="width:0%">
          </div>

        </div>

      </div>

    </div>
  </div>

</div>


      <!-- TABLE -->
      <div class="bg-white rounded-lg shadow p-4">

        <!-- FILTER -->
        <div class="flex flex-col md:flex-row justify-between gap-3 mb-4">

          <div class="flex items-center gap-2">
            <label for="statusFilter">Filter Status: </label>
            <select id="statusFilter"
              class="border rounded px-3 py-1 text-sm focus:ring focus:ring-purple-200">
              <option value="All">Show All</option>
              <option value="Active">Active</option>
              <option value="Archived">Archived</option>
              <option value="Upcoming">Upcoming</option>
            </select>
          </div>

        </div>

        <!-- TABLE -->
        <div class="overflow-x-auto">
          <table id="evaluationTable" class="w-full text-sm text-left">

            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2">Academic Year</th>
                <th class="px-4 py-2">Semester</th>
                <th class="px-4 py-2">Status</th>
                <th class="px-4 py-2">Restriction</th>
                <th class="px-4 py-2">Actions</th>
              </tr>
            </thead>

            <tbody id="evaluationTableBody" class="divide-y">
              <!-- JS will render rows here -->
            </tbody>

          </table>
        </div>

        </div>

      </div>

    </main>

    <!-- Modal Content -->
    <?php require_once __DIR__ . '/../partials/modals/periods/create_evaluation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/periods/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/periods/edit_evaluation_modal.php'; ?>

  </div>


<script src="<?= BASE_URL ?>assets/js/admin/evaluation_periods/actions.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/evaluation_periods/table.js" type="module"></script>
</body>
</html>