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

$pageTitle = "Manage Programs";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Programs</title>

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
    <?php require __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="pt-22 lg:ml-80 lg:mr-10 p-6 w-full  min-h-screen overflow-y-auto">

      <div class="p-4 space-y-6">

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
                Manage Programs
              </h1>
              <p class="text-xs text-[#6010ff]/60 font-medium">Manage &amp; Organize Teacher Information</p>
            </div>
          </div>

          <button
            class="addProgram bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md shadow hover:opacity-90 transition">

            <i class="fa-solid fa-plus"></i>
            Add New Program
          </button>

        </div>


        <!-- TABLE -->
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

          <!-- TABLE TOOLBAR -->
          <div class="flex flex-col gap-4 border-b border-slate-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

            <div>
              <h2 class="text-base font-bold text-slate-800">
                Academic Programs
              </h2>

              <p class="mt-1 text-sm text-slate-500">
                Manage and monitor available programs.
              </p>
            </div>

            <!-- FILTER -->
            <div class="relative">

              <select
                id="departmentFilter"
                class="w-full appearance-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-2.5 pr-10 text-sm font-medium text-slate-700 outline-none transition focus:border-indigo-400 focus:bg-white focus:ring-2 focus:ring-indigo-100 sm:w-48">

                <option value="All">Show All</option>
                <option>College</option>
                <option>SHS</option>

              </select>

              <!-- Dropdown Icon -->
              <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400">

                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke="currentColor"
                  stroke-width="2">

                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M19 9l-7 7-7-7" />

                </svg>

              </div>

            </div>

          </div>


          <!-- TABLE -->
          <div class="overflow-x-auto p-4">

            <table id="programTable" class="w-full min-w-[800px] text-sm">

              <!-- HEADER -->
              <thead class="border-b border-slate-100 bg-slate-50/80">

                <tr>

                  <th
                    class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Program Code
                  </th>

                  <th
                    class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Program Name
                  </th>

                  <th
                    class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Department
                  </th>

                  <th
                    class="whitespace-nowrap px-6 py-4 text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Status
                  </th>

                  <th
                    class="whitespace-nowrap px-6 py-4 text-center text-xs font-semibold uppercase tracking-wider text-slate-400">
                    Actions
                  </th>

                </tr>

              </thead>


              <!-- BODY -->
              <tbody class="divide-y divide-slate-100 bg-white">

                <!-- JS fill this line -->

              </tbody>

            </table>

          </div>

        </div>


        </div>

    </main>

    <!-- Modal Content -->
    <?php require __DIR__ . '/../partials/modals/programs_modal/add_program_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/programs_modal/confirmation_modal.php'; ?>
    <?php require __DIR__ . '/../partials/modals/programs_modal/edit_program_modal.php'; ?>

  </div>

<script src="<?= BASE_URL ?> BASE_URL ?>assets/js/admin/manage_program/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/manage_program/action.js" type="module"></script> 
</body>
</html>