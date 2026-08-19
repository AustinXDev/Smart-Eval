<?php

require_once __DIR__ . '/../../app/init.php';

use App\Controllers\Dashboard\DashboardController;
use App\Middleware\AdminAuthMiddleware;
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

$pageTitle = "Dashboard";

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard <?= htmlspecialchars(ucfirst($department)) ?>
    </title>

    <?php
    require_once __DIR__ . '/../../public/assets/includes/head.php';
    ?>

    <link
        rel="stylesheet"
        href="<?= BASE_URL ?>/assets/css/custom.css"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>

<body>

  <!-- Wrapper -->
  <div class="max-w-screen-2xl h-dvh mx-auto flex  relative"> 

    <?php
    require __DIR__ . '/../partials/header.php';
    ?>
    <?php
    require __DIR__ . '/../partials/sidebar.php';
    ?>

    <main class="
    pt-22 
    lg:ml-70 
    p-6 
    w-full
    min-h-screen 
    bg-white 
    z-1
    overflow-auto" 
    style="
      box-shadow: 
      -12px 0px 32px -8px rgba(108, 92, 231, 0.08), 
      -4px 0px 12px -4px rgba(0, 0, 0, 0.04);
    ">

     <div class="flex items-center gap-3 mb-4 ml-6">
        <div class="bg-[#6010ff] p-2.5 rounded-xl flex-shrink-0">
          <svg class="w-5 h-5 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
          </svg>
        </div>
        <div>
          <h1 class="text-xl font-semibold text-[#2D3748] tracking-tight">
            <?= htmlspecialchars(ucfirst($department)) ?> Dashboard
          </h1>
          <p class="text-xs text-[#6010ff]/60 font-medium">Overview &amp; performance metrics</p>
        </div>
      </div>

      <!-- Card Container -->
      <div id="card-container" class="px-2 mt-3 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:px-5 py-2">

        <!-- Total Students -->
        <div class="group flex items-start gap-4 p-4 rounded-xl border border-purple-100 bg-white hover:-translate-y-1 hover:bg-[#2D1B69] hover:shadow-lg hover:shadow-purple-200 transition-all duration-300">

          <div class="w-12 h-12 flex items-center       justify-center shrink-0
          bg-purple-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300">

            <svg class="w-6 h-6 text-purple-600 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
              <path fill-rule="evenodd" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z" clip-rule="evenodd"/>
              <path d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"/>
            </svg>

          </div>


          <div class="flex-1 min-w-0">

            <p class="text-xs font-medium text-purple-500 group-hover:text-purple-200 tracking-wide mb-1">Total Students</p>

            <div id="totalStudents" class="text-2xl font-bold text-[#1E0F4E] group-hover:text-white">

            </div>

          </div>

        </div>

        <!-- Total Teachers -->
        <div class="group flex items-start gap-4 p-4 rounded-xl border border-amber-100 bg-white hover:-translate-y-1 hover:bg-amber-800 hover:shadow-lg hover:shadow-amber-200 transition-all duration-300">

          <div class="w-12 h-12 flex items-center justify-center shrink-0
          bg-amber-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300">

            <svg class="w-6 h-6 text-amber-600 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">

              <path d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"/>

            </svg>

          </div>

          <div class="flex-1 min-w-0">

            <p class="text-xs font-medium text-amber-500 group-hover:text-amber-200 tracking-wide mb-1">Total Teachers</p>

            <div id="totalTeachers" class="text-2xl font-bold text-[#1E0F4E] group-hover:text-white"></div>

          </div>

        </div>

        <!-- Academic Period -->
        <div class="group flex items-start gap-4 p-4 rounded-xl border border-purple-100 bg-white hover:-translate-y-1 hover:bg-[#2D1B69] hover:shadow-lg hover:shadow-purple-200 transition-all duration-300">

          <div class="w-12 h-12 flex items-center justify-center bg-purple-100 group-hover:bg-white/10 rounded-xl transition-colors duration-300 shrink-0 mt-0.5">

            <svg class="w-6 h-6 text-purple-600 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">
              <path d="M224 64C206.3 64 192 78.3 192 96L192 128L160 128C124.7 128 96 156.7 96 192L96 240L544 240L544 192C544 156.7 515.3 128 480 128L448 128L448 96C448 78.3 433.7 64 416 64C398.3 64 384 78.3 384 96L384 128L256 128L256 96C256 78.3 241.7 64 224 64zM96 288L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 288L96 288z"/>
            </svg>

          </div>


          <div id="card-content" class="flex flex-col flex-1 min-w-0">

            <p class="text-xs font-medium text-purple-500 group-hover:text-purple-200 tracking-wide mb-1">
              <span id="card-name"></span>
            </p>

            <div class="text-xs font-bold text-[#1E0F4E] group-hover:text-white mb-2">
              <span id="academic_year"></span> &mdash; <span id="semester"></span>
            </div>

            <div class="flex gap-5 flex-wrap">

              <div class="flex flex-col gap-0.5">

                <span class="text-xs font-medium text-green-500">Start Date</span>

                <span id="start-date" class="text-sm text-gray-500 group-hover:text-white/80"></span>

              </div>

              <div class="flex flex-col gap-0.5">

                <span class="text-xs font-medium text-red-400">End Date</span>

                <span id="end-date" class="text-sm text-gray-500 group-hover:text-white/80"></span>

              </div>

            </div>

          </div>

        </div>


        <!-- Evaluation Completion Rate -->
        <div class="group flex items-start gap-4 p-4 rounded-xl border border-green-100 bg-white hover:-translate-y-1 hover:bg-green-900 hover:shadow-lg hover:shadow-green-200 transition-all duration-300">

          <div class="bg-green-100 group-hover:bg-white/10 p-3 rounded-xl transition-colors duration-300 flex-shrink-0 mt-0.5">

            <svg class="w-6 h-6 text-green-700 group-hover:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"/>
            </svg>

          </div>

          <div class="flex-1 min-w-0">

            <p class="text-xs font-medium text-green-600 group-hover:text-green-300 tracking-wide mb-1">
              Evaluation Status
            </p>

            <h1 class="text-xs font-semibold text-[#1E0F4E] group-hover:text-white mb-3">
              Evaluation Completion Rate
            </h1>

            <div id="progress-bar" class="w-full bg-green-100 group-hover:bg-white/20 rounded-full h-2.5 overflow-hidden transition-colors duration-300">

              <div id="progress-fill" class="bg-green-700 group-hover:bg-green-300 h-2.5 rounded-full w-0 transition-all duration-1000">
              </div>

            </div>

            <div id="percentage" class="text-right mt-2 text-xs font-medium text-green-700 group-hover:text-green-200">
            </div>

          </div>

        </div>

        </div>

        <!-- Ranking + Overall Score Distribution --->
        <div class="p-0 lg:p-5 mt-2">

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-8">

          <div class="lg:col-span-2 bg-white rounded-lg [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] p-5 hover:-translate-y-2 transition-all duration-300 hover:bg-gray-100  ">

            <!-- Ranking Table -->

            <div class="mb-4"> <!-- Header -->

              <h1 class="text-sm font-semibold text-[#2D3748] tracking-wide">
                Teacher Performance Ranking
              </h1>

            </div>

            <div class="overflow-x-auto">

              <div class="w-full overflow-x-auto rounded-xl border border-gray-200">

                <table class="w-full min-w-[400px] text-sm text-left">

                <colgroup>

                  <col style="width: 10%;">
                  <col style="width: 40%">
                  <col style="width: 15%">
                  <col style="width: 15%">
                  <col style="width: 15%">

                </colgroup>

                <thead>

                  <tr class="bg-[#2D1B69] text-white">

                    <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">
                      Rank
                    </th>

                    <th class="px-4 py-3 font-medium tracking-wide whitespace-nowrap">
                      Name
                    </th>

                    <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">
                      Evaluators
                    </th>

                    <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">
                      Evaluated
                    </th>

                    <th class="px-4 py-3 text-center font-medium tracking-wide whitespace-nowrap">
                      Score
                    </th>

                  </tr>
                  
                </thead>

                <tbody class="divide-y divide-gray-100" id="tbody-ranking">
                  <!-- JS fill this line  -->
                </tbody>

                </table>

              </div>

              <div class="py-4">

                <div class="bg-white border border-gray-100 rounded-xl p-4 flex flex-wrap items-center gap-3">

                <div id="top_initials" class="w-11 h-11 rounded-full bg-purple-100 flex items-center justify-center text-sm font-medium text-purple-800 shrink-0">
                  
                </div>

                <div class="flex-1 min-w-[120px]">

                  <p class="m-0 text-[15px] font-medium text-gray-900" id="highest-teacher-name"></p>

                  <span class="inline-block mt-1 text-[11px] font-medium bg-green-100 text-green-800 px-2 py-0.5 rounded-full">
                    Highest rating
                  </span>

                </div>

                <div class="flex items-center gap-2 shrink-0">

                  <span class="text-[13px] text-gray-500">Avg. score</span>

                  <div class="flex items-center gap-1 bg-green-100 rounded-lg px-2.5 py-1">

                    <svg width="13" height="13" viewBox="0 0 16 16" fill="none"><path d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.3l-3.7 2 .7-4.1-3-2.9 4.2-.7z" fill="#166534"/></svg>
                    <span id="avg-score" class="text-[15px] font-medium text-green-900"></span>

                  </div>

                </div>

                </div>

              </div>

            </div>

          </div>

          <!-- Doughnut Chart -->
          <div class="bg-white rounded-xl p-5 border border-gray-100 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-1 transition-all duration-300">

            <div class="flex items-center justify-between mb-3">

              <div>

                <h1 class="text-sm font-semibold text-[#2D3748] tracking-wide">
                  Score Distribution
                </h1>

                <p class="text-xs text-gray-400 mt-0.5">
                  Total students per rating
                </p>

              </div>

              <div class="flex items-center gap-1.5">

                <span class="w-2 h-2 rounded-full bg-[#16213E]"></span>

                <span class="text-xs text-gray-400">
                  Responses
                </span>

              </div>


            </div>

            <div class="w-full h-px bg-gray-100 mb-4"></div>


            <!-- Rating legend pills -->
            <div class="flex items-center gap-2 mb-4 flex-wrap">

              <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">
                5 — Excellent
              </span>

              <span class="inline-flex items-center gap-1 bg-blue-50 text-blue-700 text-xs font-medium px-2 py-0.5 rounded-full">
                4 — Good
              </span>

              <span class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 text-xs font-medium px-2 py-0.5 rounded-full">
                3 — Average
              </span>

              <span class="inline-flex items-center gap-1 bg-orange-50 text-orange-700 text-xs font-medium px-2 py-0.5 rounded-full">
                2 — Poor
              </span>

              <span class="inline-flex items-center gap-1 bg-red-50 text-red-700 text-xs font-medium px-2 py-0.5 rounded-full">
                1 — Very Poor
              </span>

            </div>

            <div id="score-breakdown" class="relative w-full h-64">

              <canvas id="scoreChart"></canvas>

            </div>

          </div>


          <!-- PIE Chart -->
          <div class="bg-white rounded-xl p-5 border border-gray-100 [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] hover:-translate-y-1 transition-all duration-300">

            <div class="flex items-center justify-between mb-3">

              <div>

                <h1 class="text-sm font-semibold text-[#2D3748] tracking-wide">
                  Evaluation Participation
                </h1>

                <p class="text-xs text-gray-400 mt-0.5">
                  Completed vs pending per department
                </p>

              </div>

              <div class="flex items-center gap-3">

                <div class="flex items-center gap-1.5">

                  <span class="w-2 h-2 rounded-full bg-[#16213E]"></span>
                  <span class="text-xs text-gray-400">
                    Completed
                  </span>

                </div>

                <div class="flex items-center gap-1.5">

                  <span class="w-2 h-2 rounded-full bg-gray-200">

                  </span>

                  <span class="text-xs text-gray-400">
                    Pending
                  </span>

                </div>

              </div>

            </div>

            <div class="w-full h-px bg-gray-100 mb-4">

            </div>

            <div class="flex items-center gap-2 mb-4 flex-wrap">

              <span class="inline-flex items-center gap-1 bg-green-50 text-green-700 text-xs font-medium px-2 py-0.5 rounded-full">
                Completed
              </span>

              <span class="inline-flex items-center gap-1 bg-gray-50 text-[#f87171] text-xs font-medium px-2 py-0.5 rounded-full">
                Pending
              </span>

            </div>

            <div id="participation-breakdown" class="relative w-full h-64">

              <canvas id="participationChart"></canvas>

            </div>

          </div>


          <!-- Bar Chart -->
          <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-gray-100 
                    [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] 
                    hover:-translate-y-1 transition-all duration-300">

            <div class="flex items-center justify-between mb-3">

              <div>

                <h1 class="text-sm font-semibold text-[#2D3748] tracking-wide">
                  Total Evaluated per Program
                </h1>

                <p class="text-xs text-gray-400 mt-0.5">
                  Number of students who completed evaluations by program
                </p>

              </div>

            </div>

            <div class="w-full h-px bg-gray-100 mb-4"></div>

            <div id="program-breakdown" class="relative w-full h-64">

              <canvas id="programChart"></canvas>

            </div>

          </div>


          <!-- Catrgorical Breakdown -->
          <div class="lg:col-span-2 bg-white rounded-xl p-5 border border-gray-100 
                    [box-shadow:rgba(0,0,0,0.02)_0px_1px_3px_0px,rgba(27,31,35,0.15)_0px_0px_0px_1px] 
                    hover:-translate-y-1 transition-all duration-300">

            <div class="flex items-center justify-between mb-3">

              <div>

                <h1 class="text-sm font-semibold text-[#2D3748] tracking-wide">
                  Categorical Breakdown of Evaluations
                </h1>

                <p class="text-xs text-gray-400 mt-0.5">
                Evaluation Category Breakdown
                </p>

              </div>

            </div>

            <div class="w-full h-px bg-gray-100 mb-4"></div>

            <div id="categorical-breakdown" class="relative w-full h-64">
              
            </div>

          </div>

        </div>

        </div>


        <!-- Student Participation Card Container-->
        <div class="p-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-0
                  border border-purple-100 rounded-xl bg-white mt-2 overflow-hidden">

        <!-- Students Who Evaluated -->
        <div class="flex flex-col gap-4 p-4 border-b sm:border-b-0 sm:border-r border-purple-100 hover:bg-purple-50/40 transition-colors duration-200">

          <div class="flex items-center gap-3 pb-4 border-b border-purple-100">

            <div class="bg-purple-100 p-3 rounded-xl flex-shrink-0">

              <svg class="w-6 h-6 text-purple-600" fill="none" stroke-width="1.5" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
              </svg>

            </div>

            <p class="text-xs font-medium text-purple-700">
              Students Who Evaluated
            </p>

          </div>

          <div class="flex items-center justify-between">

            <div class="flex flex-col gap-0.5">

              <span id="evaluated-total" class="text-2xl font-semibold text-[#1E0F4E]">

              </span>

              <span id="evaluated-label" class="text-xs text-purple-400">
                Total evaluated
              </span>

            </div>

            <span id="evaluated-arrow" class="flex items-center gap-1 bg-green-100 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full">

              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
              </svg>
              Up
            </span>

          </div>

        </div>


        <!-- Students Who Did Not Evaluate -->
        <div class="flex flex-col gap-4 p-4 border-b sm:border-b-0 lg:border-r border-purple-100 hover:bg-red-50/40 transition-colors duration-200">

          <div class="flex items-center gap-3 pb-4 border-b border-red-100">

            <div class="bg-red-100 p-3 rounded-xl flex-shrink-0">
              
              <svg class="w-6 h-6" fill="#ef4444" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.625 6c-.54 0-.828.419-.936.634a1.96 1.96 0 0 0-.189.866c0 .298.059.605.189.866.108.215.395.634.936.634.54 0 .828-.419.936-.634.13-.26.189-.568.189-.866 0-.298-.059-.605-.189-.866-.108-.215-.395-.634-.936-.634Zm4.314.634c.108-.215.395-.634.936-.634.54 0 .828.419.936.634.13.26.189.568.189.866 0 .298-.059.605-.189.866-.108.215-.395.634-.936.634-.54 0-.828-.419-.936-.634a1.96 1.96 0 0 1-.189-.866c0-.298.059-.605.189-.866Zm-4.34 7.964a.75.75 0 0 1-1.061-1.06 5.236 5.236 0 0 1 3.73-1.538 5.236 5.236 0 0 1 3.695 1.538.75.75 0 1 1-1.061 1.06 3.736 3.736 0 0 0-2.639-1.098 3.736 3.736 0 0 0-2.664 1.098Z" clip-rule="evenodd"/>
              </svg>

            </div>

            <p class="text-xs font-medium text-red-600">
              Students Who Did Not Evaluate
            </p>

          </div>

          <div class="flex items-center justify-between">

            <div class="flex flex-col gap-0.5">

              <span id="not-evaluated-total" class="text-2xl font-semibold text-red-800"></span>

              <span id="not-evaluated-label" class="text-xs text-red-300">
                Not evaluated
              </span>

            </div>

            <span id="not-evaluated-arrow" class="flex items-center gap-1 bg-red-100 text-red-700 text-xs font-medium px-3 py-1.5 rounded-full">
              
              <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v16.19l6.22-6.22a.75.75 0 1 1 1.06 1.06l-7.5 7.5a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 1 1 1.06-1.06l6.22 6.22V3a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
              </svg>

              Down

            </span>

          </div>

        </div>


        <!-- Total Evaluations Submitted -->
        <div class="flex flex-col gap-4 p-4 hover:bg-blue-50/40 transition-colors duration-200">

          <div class="flex items-center gap-3 pb-4 border-b border-blue-100">

            <div class="bg-blue-100 p-3 rounded-xl flex-shrink-0">

              <svg class="w-6 h-6" fill="#0284c7" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z" clip-rule="evenodd"/>
                <path fill-rule="evenodd" d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z" clip-rule="evenodd"/>
              </svg>

            </div>

            <p class="text-xs font-medium text-blue-700">
              Total Evaluations Submitted
            </p>

          </div>

          <div class="flex items-center justify-between">

            <div class="flex flex-col gap-0.5">

              <span id="submitted-total" class="text-2xl font-semibold text-blue-900"></span>

              <span id="submitted-label" class="text-xs text-blue-300">
                Submissions
              </span>

            </div>

            <span id="submitted-arrow" class="flex items-center gap-1 bg-green-100 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full">

              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">

                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>

              </svg>

              Up
            </span>

          </div>

        </div>

        </div>

      </div>

      <div id="connection-error" class="hidden fixed bottom-4 right-4 bg-red-100 text-red-700 text-sm px-4 py-3 rounded-xl border border-red-200 z-50">
      Connection error — retrying...
      </div>
    
    </main>

  <div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script
        src="<?= BASE_URL ?>/assets/js/charts/chart-config.js"
        type="module"
    ></script>

    <script
        src="<?= BASE_URL ?>/assets/js/admin/dashboard/dashboard.js"
        type="module"
    ></script>

</body>

</html>