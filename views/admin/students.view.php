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

$pageTitle = "Manage Students";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Students <?php echo strtoupper($department); ?></title>

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
  <div class="max-w-screen-2xl h-dvh mx-auto flex  relative"> 

    <!-- header -->
    <?php require __DIR__ . '/../partials/header.php'; ?>
    
    <!-- Sidebar -->
    <?php require __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="pt-22 lg:ml-80 lg:mr-10 p-6 w-full  min-h-screen">
      
      
      <!-- Header --->
      <div class="flex flex-col  sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">

        <div class="flex items-center gap-3">
          <div class="bg-[#6010ff] p-2.5 rounded-xl flex-shrink-0">
            <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
            </svg>
          </div>
          <div>
            <h1 class="text-md lg:text-xl font-semibold text-[#2D3748] tracking-tight">
              <?= htmlspecialchars(ucfirst($department)) ?> Students
            </h1>
            <p class="text-xs text-[#6010ff]/60 font-medium">Manage &amp; Organize Student Information</p>
          </div>
        </div>

        <div class="flex justify-center items-center flex-wrap gap-2">

          <button class="add-btn bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-4 py-2 rounded-md shadow hover:opacity-90 transition">
            <i class="fas fa-plus"></i>
            <span>Add Student</span>
          </button>

          <button class="csv-btn flex items-center gap-2 bg-green-600 px-4 py-2 rounded-md text-white text-sm hover:opacity-90 transition">
            <i class="fas fa-file-csv"></i>
            <span>Import CSV</span>
          </button>

        </div>
      </div>

      <!-- Card info container -->
      <div class="mt-6 overflow-x-auto pt-2" id="card-container">

        <div class="flex gap-4 min-w-max">

          <!-- Total Students -->
          <div class="group flex flex-shrink-0 items-center gap-4 w-72 sm:w-80 lg:flex-1 px-6 py-5
                      bg-white border border-indigo-100 rounded-xl
                      hover:-translate-y-1 hover:bg-[#16213E] hover:shadow-lg hover:shadow-indigo-200
                      transition-all duration-300 cursor-pointer">
            <div class="w-12 h-12 flex items-center justify-center shrink-0
                        bg-indigo-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300">
              <svg class="w-6 h-6 text-[#16213E] group-hover:text-white transition-transform duration-300 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">
                <path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-indigo-500 group-hover:text-indigo-200 tracking-wide mb-1">Total Students</p>
              <p class="text-2xl font-bold text-[#1E0F4E] group-hover:text-white" id="total-students"></p>
            </div>
          </div>

          <!-- Active Students -->
          <div class="group flex flex-shrink-0 items-center gap-4 w-72 sm:w-80 lg:flex-1 px-6 py-5
                      bg-white border border-green-100 rounded-xl
                      hover:-translate-y-1 hover:bg-green-600 hover:shadow-lg hover:shadow-green-200
                      transition-all duration-300 cursor-pointer">
            <div class="w-12 h-12 flex items-center justify-center shrink-0
                        bg-green-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300">
              <svg class="w-6 h-6 text-green-600 group-hover:text-white transition-transform duration-300 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">
                <path d="M286 368C384.5 368 464.3 447.8 464.3 546.3C464.3 562.7 451 576 434.6 576L78 576C61.6 576 48.3 562.7 48.3 546.3C48.3 447.8 128.1 368 226.6 368L286 368zM585.7 169.9C593.5 159.2 608.5 156.8 619.2 164.6C629.9 172.4 632.3 187.4 624.5 198.1L522.1 338.9C517.9 344.6 511.4 348.3 504.4 348.7C497.4 349.1 490.4 346.5 485.5 341.4L439.1 293.4C429.9 283.9 430.1 268.7 439.7 259.5C449.2 250.3 464.4 250.6 473.6 260.1L500.1 287.5L585.7 169.8zM256.3 312C190 312 136.3 258.3 136.3 192C136.3 125.7 190 72 256.3 72C322.6 72 376.3 125.7 376.3 192C376.3 258.3 322.6 312 256.3 312z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-green-500 group-hover:text-green-100 tracking-wide mb-1">Active Students</p>
              <p class="text-2xl font-bold text-[#1E0F4E] group-hover:text-white" id="total-active"></p>
            </div>
          </div>

          <!-- Inactive Students -->
          <div class="group flex flex-shrink-0 items-center gap-4 w-72 sm:w-80 lg:flex-1 px-6 py-5
                      bg-white border border-red-100 rounded-xl
                      hover:-translate-y-1 hover:bg-red-500 hover:shadow-lg hover:shadow-red-200
                      transition-all duration-300 cursor-pointer">
            <div class="w-12 h-12 flex items-center justify-center shrink-0
                        bg-red-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300">
              <svg class="w-6 h-6 text-red-500 group-hover:text-white transition-transform duration-300 group-hover:scale-110" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">
                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 405.8 61.8 344 138.3 344l91.4 0C306.2 344 368 405.8 368 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM484.7 246.7L440.1 202c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l84.7 84.7c9.4 9.4 9.4 24.6 0 33.9l-84.7 84.7c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l44.6-44.7L416 292.7c-13.3 0-24-10.7-24-24s10.7-24 24-24l68.7 0z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-xs font-medium text-red-500 group-hover:text-red-100 tracking-wide mb-1">Inactive Students</p>
              <p class="text-2xl font-bold text-[#1E0F4E] group-hover:text-white" id="total-inactive"></p>
            </div>
          </div>

        </div>

      </div>

      <!-- Student List Table--->

      <div class="p-5 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] rounded-md mt-7 ">

        <div id="tableWrapper" class="overflow-x-auto w-full" data-department="<?php echo htmlspecialchars($department); ?>">
          <table id="studentsTable" class="w-full text-left text-sm sm:text-base" >

            <div class="flex flex-col gap-4 md:flex-row md:gap-8  mb-4 mt-2">
              <div class="flex items-center gap-2">
                <label class="text-gray-900 text-sm whitespace-nowrap"><?php echo ($department === 'college') ? 'Filter Course:': 'Filter Program:';?></label>
                <select id="courseFilter" class="border border-gray-200 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer w-[200px]">
                  <option value="All">All </option>
                </select>
              </div>
            </div>

            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
              <tr>
                <th class="px-4 py-2 font-bold w-20 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Student ID</th>
                <th class="px-4 py-2 font-bold w-80 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Student Name</th>
                <th class="px-4 py-2 font-bold w-30 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Department</th>
                <th class="px-4 py-2 font-bold w-30 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Course</th>
                <th class="px-4 py-2 font-bold w-25 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Status</th>
                <th class="px-4 py-2 font-bold w-40 whitespace-nowrap" style="font-family: roboto, 'sans-serif';">Actions</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
              <!-- LoadData Fucntion fills this section -->
            </tbody>

          </table>
        </div>

      </div>

    </main>

    <!-- Modal Content -->
    <?php require_once __DIR__ . '/../partials/modals/students_modal/add_student_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/confirmation_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/import_csv_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/summary_report_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/loader_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/view_student_modal.php'; ?>
    <?php require_once __DIR__ . '/../partials/modals/students_modal/edit_student_modal.php'; ?>

  </div>


<script src="<?= BASE_URL ?>assets/js/admin/students/table.js" type="module"></script>
<script src="<?= BASE_URL ?>assets/js/admin/students/actions.js" type="module"></script> 
</body>
</html>