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

<main
  class="min-h-screen w-full bg-slate-50 pt-15 lg:ml-70"
>
  <!-- =========================================================
       DASHBOARD HEADER
  ========================================================== -->
  <div class="border-b border-slate-200 bg-white">
    <div class="mx-auto max-w-[1800px] px-5 py-5 sm:px-6 lg:px-8">

      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

        <!-- Page Identity -->
        <div class="flex items-center gap-3">

          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 shadow-sm">
            <svg
              class="h-5 w-5 text-white"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              stroke-width="1.7"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"
              />
            </svg>
          </div>

          <div>
            <h1 class="text-lg font-semibold tracking-tight text-slate-900 sm:text-xl">
              <?= htmlspecialchars(ucfirst($department)) ?> Dashboard
            </h1>

            <p class="mt-0.5 text-xs font-medium text-slate-500">
              Overview &amp; performance metrics
            </p>
          </div>

        </div>

      </div>
    </div>
  </div>


  <!-- =========================================================
       MAIN DASHBOARD CONTENT
  ========================================================== -->
  <div class="mx-auto max-w-[1800px] space-y-6 px-5 py-6 sm:px-6 lg:px-8">


    <!-- =======================================================
         KPI CARDS
    ======================================================== -->
    <div
      id="card-container"
      class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >

      <!-- Total Students -->
      <div
        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md"
      >
        <div class="flex items-start justify-between">

          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
              Total Students
            </p>

            <div
              id="totalStudents"
              class="mt-2 text-2xl font-bold tracking-tight text-slate-900"
            ></div>

            <p class="mt-1 text-xs text-slate-400">
              Active students
            </p>
          </div>

          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition-colors group-hover:bg-indigo-100"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0ZM15.75 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM2.25 9.75a3 3 0 1 1 6 0 3 3 0 0 1-6 0ZM6.31 15.117A6.745 6.745 0 0 1 12 12a6.745 6.745 0 0 1 6.709 7.498.75.75 0 0 1-.372.568A12.696 12.696 0 0 1 12 21.75c-2.305 0-4.47-.612-6.337-1.684a.75.75 0 0 1-.372-.568 6.787 6.787 0 0 1 1.019-4.38Z"
                clip-rule="evenodd"
              />
              <path
                d="M5.082 14.254a8.287 8.287 0 0 0-1.308 5.135 9.687 9.687 0 0 1-1.764-.44l-.115-.04a.563.563 0 0 1-.373-.487l-.01-.121a3.75 3.75 0 0 1 3.57-4.047ZM20.226 19.389a8.287 8.287 0 0 0-1.308-5.135 3.75 3.75 0 0 1 3.57 4.047l-.01.121a.563.563 0 0 1-.373.486l-.115.04c-.567.2-1.156.349-1.764.441Z"
              />
            </svg>
          </div>

        </div>
      </div>


      <!-- Total Teachers -->
      <div
        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-md"
      >
        <div class="flex items-start justify-between">

          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
              Total Teachers
            </p>

            <div
              id="totalTeachers"
              class="mt-2 text-2xl font-bold tracking-tight text-slate-900"
            ></div>

            <p class="mt-1 text-xs text-slate-400">
              Teaching personnel
            </p>
          </div>

          <div
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600 transition-colors group-hover:bg-amber-100"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 640 640"
              fill="currentColor"
            >
              <path
                d="M192 448C245 448 288 491 288 544C288 561.7 273.7 576 256 576L32 576C14.3 576 0 561.7 0 544C0 491 43 448 96 448L192 448zM544 96C579.3 96 608 124.7 608 160L608 448C608 481.1 582.8 508.4 550.5 511.7L544 512L332.9 512C327.8 487.8 316.6 465.9 300.8 448L352 448L352 416C352 398.3 366.3 384 384 384L480 384C497.7 384 512 398.3 512 416L512 448L544 448L544 160L192 160L192 217.3C177.2 211.3 161 208 144 208C138.6 208 133.2 208.3 128 209L128 160C128 124.7 156.7 96 192 96L544 96zM144 416C99.8 416 64 380.2 64 336C64 291.8 99.8 256 144 256C188.2 256 224 291.8 224 336C224 380.2 188.2 416 144 416z"
              />
            </svg>
          </div>

        </div>
      </div>


      <!-- Academic Period -->
      <div
        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-200 hover:shadow-md"
      >
        <div class="flex items-start justify-between">

          <div id="card-content" class="min-w-0 flex-1">

            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
              <span id="card-name"></span>
            </p>

            <div class="mt-2 text-base font-semibold text-slate-900">
              <span id="academic_year"></span>
              <span class="mx-1 text-slate-300">&mdash;</span>
              <span id="semester"></span>
            </div>

            <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2">

              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wide text-emerald-600">
                  Start Date
                </span>

                <span
                  id="start-date"
                  class="mt-0.5 block text-xs text-slate-500"
                ></span>
              </div>

              <div>
                <span class="block text-[10px] font-semibold uppercase tracking-wide text-rose-500">
                  End Date
                </span>

                <span
                  id="end-date"
                  class="mt-0.5 block text-xs text-slate-500"
                ></span>
              </div>

            </div>

          </div>

          <div
            class="ml-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 640 640"
              fill="currentColor"
            >
              <path
                d="M224 64C206.3 64 192 78.3 192 96L192 128L160 128C124.7 128 96 156.7 96 192L96 240L544 240L544 192C544 156.7 515.3 128 480 128L448 128L448 96C448 78.3 433.7 64 416 64C398.3 64 384 78.3 384 96L384 128L256 128L256 96C256 78.3 241.7 64 224 64zM96 288L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 288L96 288z"
              />
            </svg>
          </div>

        </div>
      </div>


      <!-- Evaluation Completion -->
      <div
        class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md"
      >
        <div class="flex items-start justify-between">

          <div class="min-w-0 flex-1">

            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">
              Evaluation Status
            </p>

            <h1 class="mt-2 text-sm font-semibold text-slate-900">
              Evaluation Completion Rate
            </h1>

            <div
              id="progress-bar"
              class="mt-4 h-2 w-full overflow-hidden rounded-full bg-slate-100"
            >
              <div
                id="progress-fill"
                class="h-2 w-0 rounded-full bg-emerald-500 transition-all duration-1000"
              ></div>
            </div>

            <div
              id="percentage"
              class="mt-2 text-right text-xs font-semibold text-emerald-600"
            ></div>

          </div>

          <div
            class="ml-3 flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600"
          >
            <svg
              class="h-5 w-5"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
              stroke-width="1.7"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.746 3.746 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.745 3.745 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.745 3.745 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z"
              />
            </svg>
          </div>

        </div>
      </div>

    </div>


    <!-- =======================================================
         TEACHER RANKING
    ======================================================== -->
    <section
      class="rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

      <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">

        <div>
          <h2 class="text-sm font-semibold text-slate-900">
            Teacher Performance Ranking
          </h2>

          <p class="mt-1 text-xs text-slate-500">
            Performance overview based on evaluation results
          </p>
        </div>

      </div>

      <div class="p-5">

        <div class="overflow-x-auto rounded-xl border border-slate-200">

          <table class="w-full min-w-[650px] text-sm text-left">

            <colgroup>
              <col style="width: 10%;">
              <col style="width: 40%;">
              <col style="width: 15%;">
              <col style="width: 15%;">
            </colgroup>

            <thead class="bg-slate-50">

              <tr class="border-b border-slate-200">

                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Rank
                </th>

                <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Name
                </th>

                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Evaluated
                </th>

                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide text-slate-500">
                  Score
                </th>

              </tr>

            </thead>

            <tbody
              class="divide-y divide-slate-100"
              id="tbody-ranking"
            >
              <!-- JS fill this line -->
            </tbody>

          </table>

        </div>


        <!-- Highest Rated Teacher -->
        <div
          class="mt-5 flex flex-col gap-4 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between"
        >

          <div class="flex min-w-0 items-center gap-3">

            <div
              id="top_initials"
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-semibold text-indigo-700"
            ></div>

            <div class="min-w-0">

              <p
                id="highest-teacher-name"
                class="truncate text-sm font-semibold text-slate-900"
              ></p>

              <span
                class="mt-1 inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700"
              >
                Highest rating
              </span>

            </div>

          </div>

          <div class="flex items-center gap-2">

            <span class="text-xs text-slate-500">
              Avg. score
            </span>

            <div class="flex items-center gap-1.5 rounded-lg bg-emerald-50 px-3 py-1.5">

              <svg
                class="h-3.5 w-3.5 text-emerald-600"
                viewBox="0 0 16 16"
                fill="none"
              >
                <path
                  d="M8 1l1.8 3.6L14 5.3l-3 2.9.7 4.1L8 10.3l-3.7 2 .7-4.1-3-2.9 4.2-.7z"
                  fill="currentColor"
                />
              </svg>

              <span
                id="avg-score"
                class="text-sm font-semibold text-emerald-700"
              ></span>

            </div>

          </div>

        </div>

      </div>

    </section>


    <!-- =======================================================
         ANALYTICS GRID
    ======================================================== -->
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">


      <!-- Score Distribution -->
      <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      >

        <div class="flex items-start justify-between gap-4">

          <div>
            <h2 class="text-sm font-semibold text-slate-900">
              Score Distribution
            </h2>

            <p class="mt-1 text-xs text-slate-500">
              Total students per rating
            </p>
          </div>

          <div class="flex shrink-0 items-center gap-2">
            <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
            <span class="text-xs text-slate-500">
              Responses
            </span>
          </div>

        </div>

        <div class="my-4 h-px bg-slate-100"></div>

        <div class="flex flex-wrap gap-2">

          <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
            5 — Excellent
          </span>

          <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-medium text-blue-700">
            4 — Good
          </span>

          <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-700">
            3 — Average
          </span>

          <span class="rounded-full bg-orange-50 px-2.5 py-1 text-[11px] font-medium text-orange-700">
            2 — Poor
          </span>

          <span class="rounded-full bg-rose-50 px-2.5 py-1 text-[11px] font-medium text-rose-700">
            1 — Very Poor
          </span>

        </div>

        <div
          id="score-breakdown"
          class="relative mt-5 h-72 w-full"
        >
          <canvas id="scoreChart"></canvas>
        </div>

      </section>


      <!-- Evaluation Participation -->
      <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      >

        <div class="flex items-start justify-between gap-4">

          <div>
            <h2 class="text-sm font-semibold text-slate-900">
              Evaluation Participation
            </h2>

            <p class="mt-1 text-xs text-slate-500">
              Completed vs pending per department
            </p>
          </div>

          <div class="flex flex-wrap items-center gap-3">

            <div class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
              <span class="text-xs text-slate-500">
                Completed
              </span>
            </div>

            <div class="flex items-center gap-1.5">
              <span class="h-2 w-2 rounded-full bg-slate-300"></span>
              <span class="text-xs text-slate-500">
                Pending
              </span>
            </div>

          </div>

        </div>

        <div class="my-4 h-px bg-slate-100"></div>

        <div class="flex flex-wrap gap-2">

          <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
            Completed
          </span>

          <span class="rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
            Pending
          </span>

        </div>

        <div
          id="participation-breakdown"
          class="relative mt-5 h-72 w-full"
        >
          <canvas id="participationChart"></canvas>
        </div>

      </section>


      <!-- Total Evaluated per Program -->
      <section
        class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      >

        <div>
          <h2 class="text-sm font-semibold text-slate-900">
            Total Evaluated per Program
          </h2>

          <p class="mt-1 text-xs text-slate-500">
            Number of students who completed evaluations by program
          </p>
        </div>

        <div class="my-4 h-px bg-slate-100"></div>

        <div
          id="program-breakdown"
          class="relative h-72 w-full"
        >
          <canvas id="programChart"></canvas>
        </div>

      </section>


      <!-- Categorical Breakdown -->
      <section
        class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
      >

        <div>
          <h2 class="text-sm font-semibold text-slate-900">
            Categorical Breakdown of Evaluations
          </h2>

          <p class="mt-1 text-xs text-slate-500">
            Evaluation Category Breakdown
          </p>
        </div>

        <div class="my-4 h-px bg-slate-100"></div>

        <div
          id="categorical-breakdown"
          class="relative min-h-[18rem] w-full"
        ></div>

      </section>

    </div>


    <!-- =======================================================
         STUDENT PARTICIPATION
    ======================================================== -->
    <section
      class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

      <div class="border-b border-slate-200 px-5 py-4">

        <h2 class="text-sm font-semibold text-slate-900">
          Student Participation
        </h2>

        <p class="mt-1 text-xs text-slate-500">
          Evaluation participation and submission overview
        </p>

      </div>


      <div
        class="grid grid-cols-1 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-x sm:divide-y-0 lg:grid-cols-3"
      >

        <!-- Students Who Evaluated -->
        <div class="p-5 transition-colors hover:bg-slate-50">

          <div class="flex items-center gap-3">

            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600"
            >
              <svg
                class="h-5 w-5"
                fill="none"
                stroke-width="1.6"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"
                />
              </svg>
            </div>

            <p class="text-sm font-semibold text-slate-800">
              Students Who Evaluated
            </p>

          </div>

          <div class="mt-5 flex items-end justify-between">

            <div>
              <span
                id="evaluated-total"
                class="text-2xl font-bold tracking-tight text-slate-900"
              ></span>

              <span
                id="evaluated-label"
                class="mt-1 block text-xs text-slate-500"
              >
                Total evaluated
              </span>
            </div>

            <span
              id="evaluated-arrow"
              class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
            >
              <svg
                class="h-3.5 w-3.5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"
                />
              </svg>
              Up
            </span>

          </div>

        </div>


        <!-- Students Who Did Not Evaluate -->
        <div class="p-5 transition-colors hover:bg-slate-50">

          <div class="flex items-center gap-3">

            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600"
            >
              <svg
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  fill-rule="evenodd"
                  d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25Zm-2.625 6c-.54 0-.828.419-.936.634a1.96 1.96 0 0 0-.189.866c0 .298.059.605.189.866.108.215.395.634.936.634.54 0 .828-.419.936-.634.13-.26.189-.568.189-.866 0-.298-.059-.605-.189-.866-.108-.215-.395-.634-.936-.634Zm4.314.634c.108-.215.395-.634.936-.634.54 0 .828.419.936.634.13.26.189.568.189.866 0 .298-.059.605-.189.866-.108.215-.395.634-.936.634-.54 0-.828-.419-.936-.634a1.96 1.96 0 0 1-.189-.866c0-.298.059-.605.189-.866Zm-4.34 7.964a.75.75 0 0 1-1.061-1.06 5.236 5.236 0 0 1 3.73-1.538 5.236 5.236 0 0 1 3.695 1.538.75.75 0 1 1-1.061 1.06 3.736 3.736 0 0 0-2.639-1.098 3.736 3.736 0 0 0-2.664 1.098Z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <p class="text-sm font-semibold text-slate-800">
              Students Who Did Not Evaluate
            </p>

          </div>

          <div class="mt-5 flex items-end justify-between">

            <div>
              <span
                id="not-evaluated-total"
                class="text-2xl font-bold tracking-tight text-slate-900"
              ></span>

              <span
                id="not-evaluated-label"
                class="mt-1 block text-xs text-slate-500"
              >
                Not evaluated
              </span>
            </div>

            <span
              id="not-evaluated-arrow"
              class="inline-flex items-center gap-1 rounded-full bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700"
            >
              <svg
                class="h-3.5 w-3.5"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  fill-rule="evenodd"
                  d="M12 2.25a.75.75 0 0 1 .75.75v16.19l6.22-6.22a.75.75 0 1 1 1.06 1.06l-7.5 7.5a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 1 1 1.06-1.06l6.22 6.22V3a.75.75 0 0 1 .75-.75Z"
                  clip-rule="evenodd"
                />
              </svg>
              Down
            </span>

          </div>

        </div>


        <!-- Total Evaluations Submitted -->
        <div class="p-5 transition-colors hover:bg-slate-50">

          <div class="flex items-center gap-3">

            <div
              class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600"
            >
              <svg
                class="h-5 w-5"
                fill="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  fill-rule="evenodd"
                  d="M7.502 6h7.128A3.375 3.375 0 0 1 18 9.375v9.375a3 3 0 0 0 3-3V6.108c0-1.505-1.125-2.811-2.664-2.94a48.972 48.972 0 0 0-.673-.05A3 3 0 0 0 15 1.5h-1.5a3 3 0 0 0-2.663 1.618c-.225.015-.45.032-.673.05C8.662 3.295 7.554 4.542 7.502 6ZM13.5 3A1.5 1.5 0 0 0 12 4.5h4.5A1.5 1.5 0 0 0 15 3h-1.5Z"
                  clip-rule="evenodd"
                />
                <path
                  fill-rule="evenodd"
                  d="M3 9.375C3 8.339 3.84 7.5 4.875 7.5h9.75c1.036 0 1.875.84 1.875 1.875v11.25c0 1.035-.84 1.875-1.875 1.875h-9.75A1.875 1.875 0 0 1 3 20.625V9.375ZM6 12a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V12Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 15a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V15Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75ZM6 18a.75.75 0 0 1 .75-.75h.008a.75.75 0 0 1 .75.75v.008a.75.75 0 0 1-.75.75H6.75a.75.75 0 0 1-.75-.75V18Zm2.25 0a.75.75 0 0 1 .75-.75h3.75a.75.75 0 0 1 0 1.5H9a.75.75 0 0 1-.75-.75Z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>

            <p class="text-sm font-semibold text-slate-800">
              Total Evaluations Submitted
            </p>

          </div>

          <div class="mt-5 flex items-end justify-between">

            <div>
              <span
                id="submitted-total"
                class="text-2xl font-bold tracking-tight text-slate-900"
              ></span>

              <span
                id="submitted-label"
                class="mt-1 block text-xs text-slate-500"
              >
                Submissions
              </span>
            </div>

            <span
              id="submitted-arrow"
              class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700"
            >
              <svg
                class="h-3.5 w-3.5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"
                />
              </svg>
              Up
            </span>

          </div>

        </div>

      </div>

    </section>

  </div>


  <!-- =========================================================
       CONNECTION ERROR
  ========================================================== -->
  <div
    id="connection-error"
    class="fixed bottom-4 right-4 z-50 hidden max-w-sm rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-medium text-rose-700 shadow-lg"
  >
    Connection error — retrying...
  </div>

</main>

  <div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
    </script> 

    <script
        src="<?= BASE_URL ?>/assets/js/charts/chart-config.js"
        type="module"
    ></script>

    <script
        src="<?= BASE_URL ?>/assets/js/admin/dashboard/dashboard.js"
        type="module"
    ></script>
    <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>

</body>

</html>