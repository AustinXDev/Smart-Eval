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

    <!-- DASHBOARD OVERVIEW -->
    <section class="w-full min-w-0">

      <div
        id="card-container"
        class="grid grid-cols-1 items-stretch gap-4 sm:grid-cols-2 xl:grid-cols-4"
      >

        <!-- TOTAL STUDENTS -->
        <article
          class="group flex min-h-[205px] min-w-0 flex-col justify-between
                rounded-2xl border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-indigo-200 hover:shadow-md"
        >

          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Total Students
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Student records overview
              </p>
            </div>

            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-indigo-50 px-2.5 py-1
                    text-[11px] font-medium text-indigo-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
              Students
            </span>
          </div>

          <div class="mt-6">
            <div
              id="totalStudents"
              class="text-4xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></div>

            <div class="mt-4 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-indigo-500"></span>
              <p class="text-xs text-slate-500">
                Registered active students
              </p>
            </div>
          </div>

        </article>


        <!-- TOTAL TEACHERS -->
        <article
          class="group flex min-h-[205px] min-w-0 flex-col justify-between
                rounded-2xl border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-amber-200 hover:shadow-md"
        >

          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Total Teachers
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Faculty records overview
              </p>
            </div>

            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-amber-50 px-2.5 py-1
                    text-[11px] font-medium text-amber-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
              Faculty
            </span>
          </div>

          <div class="mt-6">
            <div
              id="totalTeachers"
              class="text-4xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></div>

            <div class="mt-4 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>
              <p class="text-xs text-slate-500">
                Teaching personnel
              </p>
            </div>
          </div>

        </article>


        <!-- ACADEMIC PERIOD -->
        <article
          class="group flex min-h-[205px] min-w-0 flex-col
                rounded-2xl border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-violet-200 hover:shadow-md"
        >

          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Academic Period
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Current academic schedule
              </p>
            </div>

            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-violet-50 px-2.5 py-1
                    text-[11px] font-medium text-violet-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
              Period
            </span>
          </div>

          <!-- Academic Information -->
          <div id="card-content" class="mt-6 min-w-0 flex-1">

            <p
              id="card-name"
              class="text-xs font-medium text-slate-500"
            ></p>

            <div class="mt-2 flex flex-wrap items-baseline gap-x-2 gap-y-1">
              <span
                id="academic_year"
                class="break-words text-xl font-semibold
                      tracking-tight text-slate-900"
              ></span>

              <span class="text-slate-300">&mdash;</span>

              <span
                id="semester"
                class="text-sm font-medium text-violet-600"
              ></span>
            </div>

          </div>

          <!-- Dates -->
          <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-3">

            <div class="min-w-0">
              <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                Start Date
              </p>

              <p
                id="start-date"
                class="mt-1 break-words text-xs font-medium text-slate-700"
              ></p>
            </div>

            <div class="min-w-0">
              <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
                End Date
              </p>

              <p
                id="end-date"
                class="mt-1 break-words text-xs font-medium text-slate-700"
              ></p>
            </div>

          </div>

        </article>


        <!-- EVALUATION COMPLETION -->
        <article
          class="group flex min-h-[205px] min-w-0 flex-col
                rounded-2xl border border-slate-200 bg-white p-5
                shadow-sm transition-all duration-200
                hover:border-emerald-200 hover:shadow-md"
        >

          <div class="flex items-start justify-between gap-2">
            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Evaluation Completion
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Overall evaluation progress
              </p>
            </div>

            <span
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-emerald-50 px-2.5 py-1
                    text-[11px] font-medium text-emerald-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Progress
            </span>
          </div>

          <!-- Completion Percentage -->
          <div class="mt-auto pt-6">

            <div
              id="percentage"
              class="text-4xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></div>

            <!-- Progress Bar -->
            <div
              id="progress-bar"
              class="mt-5 h-2 w-full overflow-hidden rounded-full bg-slate-100"
              role="progressbar"
              aria-label="Evaluation completion rate"
              aria-valuemin="0"
              aria-valuemax="100"
              aria-valuenow="0"
            >
              <div
                id="progress-fill"
                class="h-full w-0 rounded-full bg-emerald-500
                      transition-all duration-1000 ease-out"
              ></div>
            </div>

            <div class="mt-3 flex items-center gap-2">
              <span class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"></span>

              <p class="text-xs text-slate-500">
                Student evaluation participation
              </p>
            </div>

          </div>

        </article>

      </div>

    </section>





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



    <!-- ==========================================
        STUDENT PARTICIPATION
    ========================================== -->
    <section
      class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >

      <!-- Section Header -->
      <div class="border-b border-slate-100 px-5 py-5 sm:px-6">

        <h2 class="text-sm font-semibold text-slate-900">
          Student Participation
        </h2>

        <p class="mt-1 text-xs text-slate-500">
          Evaluation participation and submission overview
        </p>

      </div>

      <!-- Participation Grid -->
      <div
        class="grid grid-cols-1 divide-y divide-slate-100
              sm:grid-cols-2 sm:divide-y-0
              lg:grid-cols-3"
      >

        <!-- STUDENTS WHO EVALUATED -->
        <article
          class="group flex min-h-[185px] min-w-0 flex-col
                justify-between p-5 transition-colors
                hover:bg-slate-50/60 sm:p-6
                sm:border-r sm:border-slate-100"
        >

          <!-- Header -->
          <div class="flex items-start justify-between gap-3">

            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Students Who Evaluated
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Student participation
              </p>
            </div>

            <span
              id="evaluated-arrow"
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-emerald-50 px-2.5 py-1
                    text-[11px] font-medium text-emerald-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
              Evaluated
            </span>

          </div>

          <!-- KPI Value -->
          <div class="mt-8">

            <p
              id="evaluated-total"
              class="text-5xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">

              <span
                class="h-1.5 w-1.5 shrink-0 rounded-full bg-emerald-500"
              ></span>

              <p
                id="evaluated-label"
                class="text-xs text-slate-500"
              >
                Total evaluated
              </p>

            </div>

          </div>

        </article>


        <!-- STUDENTS WHO DID NOT EVALUATE -->
        <article
          class="group flex min-h-[185px] min-w-0 flex-col
                justify-between p-5 transition-colors
                hover:bg-slate-50/60 sm:p-6
                lg:border-r lg:border-slate-100"
        >

          <!-- Header -->
          <div class="flex items-start justify-between gap-3">

            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Students Who Did Not Evaluate
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Pending participation
              </p>
            </div>

            <span
              id="not-evaluated-arrow"
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-rose-50 px-2.5 py-1
                    text-[11px] font-medium text-rose-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
              Pending
            </span>

          </div>

          <!-- KPI Value -->
          <div class="mt-8">

            <p
              id="not-evaluated-total"
              class="text-5xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">

              <span
                class="h-1.5 w-1.5 shrink-0 rounded-full bg-rose-500"
              ></span>

              <p
                id="not-evaluated-label"
                class="text-xs text-slate-500"
              >
                Not evaluated
              </p>

            </div>

          </div>

        </article>


        <!-- TOTAL EVALUATIONS SUBMITTED -->
        <article
          class="group flex min-h-[185px] min-w-0 flex-col
                justify-between p-5 transition-colors
                hover:bg-slate-50/60 sm:p-6
                sm:col-span-2 sm:border-t sm:border-slate-100
                lg:col-span-1 lg:border-t-0"
        >

          <!-- Header -->
          <div class="flex items-start justify-between gap-3">

            <div class="min-w-0">
              <h3 class="text-sm font-medium text-slate-700">
                Total Evaluations Submitted
              </h3>

              <p class="mt-1 text-xs text-slate-400">
                Evaluation submissions
              </p>
            </div>

            <span
              id="submitted-arrow"
              class="inline-flex shrink-0 items-center gap-1.5
                    rounded-full bg-sky-50 px-2.5 py-1
                    text-[11px] font-medium text-sky-700"
            >
              <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
              Submitted
            </span>

          </div>

          <!-- KPI Value -->
          <div class="mt-8">

            <p
              id="submitted-total"
              class="text-5xl font-light leading-none tracking-tight
                    tabular-nums text-slate-900"
            ></p>

            <div class="mt-4 flex items-center gap-2">

              <span
                class="h-1.5 w-1.5 shrink-0 rounded-full bg-sky-500"
              ></span>

              <p
                id="submitted-label"
                class="text-xs text-slate-500"
              >
                Submissions
              </p>

            </div>

          </div>

        </article>

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

<?php $autoLogoutRole = 'admin';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>

</html>