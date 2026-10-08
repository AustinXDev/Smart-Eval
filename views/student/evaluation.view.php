<?php

// Prevent back-button caching for protected student routes
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Core initialization & database configuration
require_once __DIR__ . '/../../app/init.php';

use App\middleware\StudentMiddleware;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;

// Instantiate Repositories & Middleware
$studentRepo = new StudentRepository($pdo);
$evaluationRepo = new EvaluationRepository($pdo);
$studentMiddleware = new StudentMiddleware($studentRepo, $evaluationRepo);

// Enforce authentication & route protection
$student = $studentMiddleware->requireAuth();
$studentMiddleware->evaluationProtect($student);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Teacher Evaluation - Smart-Eval</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Google Font -->
    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >
    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        html {
            font-family: "Inter", sans-serif;
        }

        body {
            overflow-x: hidden;
        }

        .evaluation-scroll::-webkit-scrollbar {
            width: 9px;
        }

        .evaluation-scroll::-webkit-scrollbar-track {
            background: transparent;
        }

        .evaluation-scroll::-webkit-scrollbar-thumb {
            background: #b9c7e4;
            border-radius: 999px;
        }

        .evaluation-scroll::-webkit-scrollbar-thumb:hover {
            background: #9eafd3;
        }

        .evaluation-scroll {
            scrollbar-width: thin;
            scrollbar-color: #b9c7e4 transparent;
        }
    </style>
</head>

<body class="min-h-screen bg-[#f7f9ff] text-slate-900">



    <!-- ============================================================
     SMART-EVAL BACKGROUND
    ============================================================= -->

    <div
        class="pointer-events-none fixed inset-0 -z-10 overflow-hidden bg-[#f8faff]"
        aria-hidden="true"
    >

        <!-- ========================================================
            TOP LEFT
            Large pale-blue curved shape
        ========================================================= -->

        <div
            class="absolute -left-[105px] top-[105px]
                  h-[175px] w-[185px]
                  rounded-[48%]
                  bg-[#eaf2ff]"
        ></div>

        <div
            class="absolute -left-[115px] top-[155px]
                  h-[145px] w-[155px]
                  rounded-[50%]
                  bg-[#e5e9ff]"
        ></div>


        <!-- ========================================================
            TOP RIGHT
            Large pale-violet flowing shape
        ========================================================= -->

        <div
            class="absolute -right-[105px] -top-[5px]
                  h-[285px] w-[285px]
                  rounded-[48%]
                  bg-[#f0f0ff]"
        ></div>

        <div
            class="absolute -right-[125px] top-[80px]
                  h-[205px] w-[190px]
                  rounded-[48%]
                  bg-[#e9e9ff]"
        ></div>


        <!-- ========================================================
            BOTTOM LEFT
        ========================================================= -->

        <div
            class="absolute -bottom-[115px] -left-[110px]
                  h-[270px] w-[280px]
                  rounded-[48%]
                  bg-[#eef1ff]"
        ></div>

        <div
            class="absolute -bottom-[75px] -left-[80px]
                  h-[175px] w-[180px]
                  rounded-[50%]
                  bg-[#e7eaff]"
        ></div>


        <!-- ========================================================
            BOTTOM RIGHT
        ========================================================= -->

        <div
            class="absolute -bottom-[115px] -right-[115px]
                  h-[275px] w-[285px]
                  rounded-[48%]
                  bg-[#edf0ff]"
        ></div>

        <div
            class="absolute -bottom-[70px] -right-[70px]
                  h-[175px] w-[185px]
                  rounded-[50%]
                  bg-[#e5e9ff]"
        ></div>

    </div>


    <!-- ============================================================
         HEADER
    ============================================================= -->

    <header class="sticky top-0 z-40 border-b border-slate-100 bg-white/95 backdrop-blur">

        <div class="mx-auto flex h-[70px] w-full max-w-[1080px] items-center justify-between px-4 sm:px-6">

            <!-- LOGO -->

            <a
                href="#"
                class="flex items-center gap-2.5"
            >
                <div
                    class="flex h-11 w-11 items-center justify-center"
                >
                    <img src="<?= BASE_URL ?>assets/images/aite-logo.png" alt="aite-logo">
                </div>

                <div class="leading-none">
                    <div class="text-[20px] font-extrabold tracking-tight text-[#17346b]">
                        Smart-Eval
                    </div>

                    <div class="mt-1 text-[8px] font-medium tracking-wide text-slate-400 sm:text-[9px]">
                        Better Feedback. Better Learning.
                    </div>
                </div>
            </a>


            <!-- STUDENT PROFILE -->

            <button
                id="studentMenuButton"
                type="button"
                class="group flex items-center gap-2.5 rounded-xl px-2 py-1.5 transition hover:bg-slate-50"
                aria-expanded="false"
                aria-haspopup="true"
            >

                <div
                    class="flex h-9 w-9 items-center justify-center overflow-hidden rounded-full border border-slate-200 bg-slate-100"
                >
                    <i class="fa-solid fa-user text-sm text-slate-500"></i>
                </div>

                <div class="hidden text-left sm:block">
                    <p class="text-xs font-bold text-[#17346b]">
                        <?= htmlspecialchars($student['full_name']) ?>
                    </p>

                    <p class="mt-0.5 text-[11px] text-slate-500">
                      <?= htmlspecialchars($student['program_code']) ?>
                      -
                      <?= htmlspecialchars($student['year_level']) ?> 
                    </p>
                </div>

                <i
                    class="fa-solid fa-chevron-down ml-1 text-[10px] text-[#17346b] transition-transform group-aria-expanded:rotate-180"
                ></i>
            </button>

        </div>

    </header>


    <!-- ============================================================
         MAIN
    ============================================================= -->

    <main class="mx-auto w-full max-w-[1080px] px-4 pb-8 pt-7 sm:px-6 lg:pt-8">

        <!-- ========================================================
             PAGE HEADER
        ========================================================= -->

        <section class="mb-5 flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-3">

                <div
                    class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-violet-100"
                >
                    <i
                        class="fa-solid fa-graduation-cap text-xl text-violet-600"
                    ></i>
                </div>

                <div>
                    <h1 class="text-2xl font-extrabold tracking-tight text-[#17346b] sm:text-[25px]">
                        Teacher Evaluation
                    </h1>

                    <p class="mt-1 text-sm text-[#5872a4]">
                        Your feedback helps us improve teaching quality and the learning experience.
                    </p>
                </div>

            </div>


            <!-- TEACHER COUNTER -->

            <div
                id="teacherProgress"
                class="flex w-fit items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 shadow-sm"
            >
                <span
                    id="teacherProgressText"
                    class="text-sm font-bold text-[#17346b]"
                >
                    Teacher 1 of 3
                </span>

                <span
                    class="flex items-center gap-1.5 rounded-full bg-violet-100 px-3 py-1 text-[11px] font-bold text-violet-600"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                    In Progress
                </span>
            </div>

        </section>


        <!-- ========================================================
             TEACHER PROFILE
        ========================================================= -->

        <section
            id="teacherProfile"
            class="mb-4 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        >

            <div class="flex flex-col gap-6 p-4 sm:p-5 lg:flex-row lg:items-center lg:justify-between">

                <!-- LEFT -->

                <div class="flex min-w-0 items-center gap-4 sm:gap-5">

                    <!-- ============================================================
                        TEACHER PROFILE IMAGE
                    ============================================================= -->

                    <div class="relative shrink-0">

                        <!-- Outer violet ring -->
                        <div
                            class="absolute -inset-[6px] rounded-full border-[3px] border-[#d8d1ff]"
                        ></div>

                        <!-- Subtle inner ring -->
                        <div
                            class="absolute -inset-[2px] rounded-full border border-[#8978ed]/40"
                        ></div>

                        <!-- Profile container -->
                        <div
                            class="
                                relative
                                flex
                                h-[105px]
                                w-[105px]
                                items-center
                                justify-center
                                overflow-hidden
                                rounded-full
                                border-[4px]
                                border-white
                                bg-[#eeecff]
                                shadow-[0_3px_12px_rgba(70,60,150,0.10)]
                                sm:h-[116px]
                                sm:w-[116px]
                            "
                        >

                            <!-- Teacher photo -->
                            <img
                                id="teacherImage"
                                src=""
                                alt="Teacher profile"
                                class="hidden h-full w-full object-cover"
                            >

                            <!-- Fallback -->
                            <div
                                id="teacherImageFallback"
                                class="flex h-full w-full items-center justify-center bg-[#eeecff]"
                            >
                                <i
                                    class="fa-solid fa-user-tie text-[38px] text-[#8172dc]"
                                ></i>
                            </div>

                        </div>

                    </div>


                    <!-- TEACHER INFORMATION -->

                    <div class="min-w-0">

                        <h2
                            id="teacherName"
                            class="truncate text-lg font-extrabold text-[#17346b] sm:text-xl"
                        >
                            Dr. Maria Santos
                        </h2>

                        <p
                            id="teacherDepartment"
                            class="mt-0.5 text-sm text-[#5872a4]"
                        >
                            Computer Science Department
                        </p>

                        <div
                            class="mt-2 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-[11px] font-semibold text-emerald-700"
                        >
                            <i class="fa-solid fa-circle-check text-emerald-500"></i>
                            Evaluation in progress
                        </div>

                        <p
                            class="mt-3 max-w-[470px] text-xs italic leading-relaxed text-[#7185aa]"
                        >
                            “Thank you for taking the time to evaluate this teacher.
                            Your responses will remain confidential.”
                        </p>

                    </div>

                </div>


                <!-- RIGHT ILLUSTRATION -->
                <div
                    class="relative hidden h-[132px] w-full max-w-[315px] items-center gap-5 overflow-hidden rounded-xl border border-[#edf0ff] bg-[#f3f5ff] px-5 lg:flex"
                >

                    <!-- Decorative background shapes -->
                    <div
                        class="pointer-events-none absolute -right-10 -top-14 h-32 w-32 rounded-full bg-[#e8eaff]"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="pointer-events-none absolute -bottom-16 left-20 h-28 w-28 rounded-full bg-[#eceeff]"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="pointer-events-none absolute right-20 top-5 h-2 w-2 rounded-full bg-[#8c7bea]"
                        aria-hidden="true"
                    ></div>

                    <div
                        class="pointer-events-none absolute right-[92px] top-8 h-1.5 w-1.5 rotate-45 rounded-sm bg-[#f2c94c]"
                        aria-hidden="true"
                    ></div>


                    <!-- Illustration -->
                    <div
                        class="relative z-10 flex h-20 w-20 shrink-0 items-center justify-center"
                    >

                        <!-- Graduation cap -->
                        <img src="<?= BASE_URL ?>/assets/icons/book.png" alt="graduation-cap">

                    </div>


                    <!-- Description -->
                    <p
                        class="relative z-10 text-[11px] font-medium leading-[17px] text-[#5872a4]"
                    >
                        Your feedback helps<br>
                        us improve teaching quality<br>
                        and the learning experience.
                    </p>

                </div>

            </div>


        </section>

        <!-- ========================================================
             Reminder
        ========================================================= -->
        <section class="mb-4 flex items-center gap-6 overflow-hidden rounded-xl border border-slate-200 bg-[#f3f5ff] p-6 shadow-sm">

          <!-- Icon Wrapper -->
          <div class="self-start rounded-full bg-[#eeecff] p-4 text-indigo-600">

            <!-- Info Icon SVG -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-6 w-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
            </svg>
          </div>

          <!-- Content -->
          <div> 
            <h1 class="text-lg font-bold text-slate-900">
              Evaluate Your Experience
            </h1>

            <p class="mt-1 max-w-[650px] text-sm text-slate-600 lg:text-base">
              Please rate each statement based on your experience with this teacher. Your honest feedback helps improve teaching effectiveness and student learning.
            </p>
          </div>

        </section>


        <!-- ========================================================
             QUESTIONS CARD
        ========================================================= -->

        <section
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        >

            <!-- QUESTIONS HEADER -->

            <div class="px-5 pb-3 pt-5 sm:px-6">

                <div class="flex items-center gap-3">

                    <h2 class="text-lg font-extrabold text-[#17346b]">
                        Questions
                    </h2>

                    <span
                        id="questionCounter"
                        class="rounded-full bg-violet-50 px-3 py-1 text-[11px] font-bold text-violet-600"
                    >
                        3 / 10
                    </span>

                </div>

                <p class="mt-1 text-xs text-[#7185aa]">
                    Rate each statement based on your experience with this teacher.
                </p>

            </div>


            <!-- ====================================================
                 QUESTIONS SCROLL AREA
            ===================================================== -->

            <div
                id="questionsContainer"
                class="evaluation-scroll max-h-[735px] overflow-y-auto px-5 pb-2 sm:px-6"
            >

                <!-- Questions are rendered by JavaScript -->

                <div
                    id="questionsLoader"
                    class="flex min-h-[350px] items-center justify-center"
                >
                    <div class="text-center">

                        <div
                            class="mx-auto h-8 w-8 animate-spin rounded-full border-2 border-slate-200 border-t-violet-600"
                        ></div>

                        <p class="mt-3 text-sm text-slate-500">
                            Loading questions...
                        </p>

                    </div>
                </div>

            </div>


            <!-- ====================================================
                 FOOTER
            ===================================================== -->

            <div class="border-t border-slate-100 px-5 py-4 sm:px-6">

                <div class="flex flex-col-reverse gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <!-- PREVIOUS -->

                    <button
                        id="previousBtn"
                        type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-6 text-xs font-semibold text-[#5872a4] transition hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        Previous
                    </button>


                    <!-- CENTER PROGRESS -->

                    <div class="flex flex-col items-center">

                        <div
                            id="questionDots"
                            class="flex items-center gap-2"
                        >
                        </div>

                        <p
                            id="questionProgressText"
                            class="mt-2 text-[10px] font-semibold text-[#7185aa]"
                        >
                            Question 3 of 10
                        </p>

                    </div>


                    <!-- NEXT -->

                    <button
                        id="nextBtn"
                        type="button"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-violet-600 px-7 text-xs font-bold text-white shadow-md shadow-violet-600/20 transition hover:bg-violet-700 active:scale-[0.98] disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400 disabled:shadow-none"
                    >
                        <span id="nextBtnText">
                            Next
                        </span>

                        <i
                            id="nextBtnIcon"
                            class="fa-solid fa-arrow-right text-[10px]"
                        ></i>

                        <span
                            id="nextBtnLoader"
                            class="hidden h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/40 border-t-white"
                        ></span>
                    </button>

                </div>

            </div>

        </section>

    </main>


    <!-- ============================================================
         JAVASCRIPT
    ============================================================= -->

    <script>
        window.SMART_EVAL = {
            studentId: <?= json_encode($student['student_id']) ?>,
        };
        window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
        window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
    </script>

    <script
        type="module"
        src="<?= BASE_URL ?>assets/js/evaluation/evaluation/index.js"
    ></script>

</body>

</html>