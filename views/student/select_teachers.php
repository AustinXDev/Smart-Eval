<?php

require_once __DIR__ . '/../../app/init.php';

use App\middleware\StudentMiddleware;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;

$studentRepo = new StudentRepository($pdo);
$evaluationRepo = new EvaluationRepository($pdo);

$studentMiddleware = new StudentMiddleware($studentRepo, $evaluationRepo);

$student = $studentMiddleware->requireAuth();

$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$studentMiddleware->preventDuplicateTeacherSelection(
    $student,
    basename($requestPath)
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Teachers</title>

<?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>
</head>

<body class="bg-gray-100">

<!-- ============================================================
     SELECT TEACHER PAGE
     Tailwind CSS Only
     Existing JS hooks preserved:
     - teachersGrid
     - summarySection
     - selectedCount
     - selectedList
     - proceedBtn
============================================================ -->

<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">

    <div class="mx-auto w-full max-w-6xl">

        <!-- =====================================================
            ENHANCED TEACHER SELECTION HEADER
        ====================================================== -->
        <div class="mb-6">

            <div
                class="relative overflow-hidden rounded-2xl
                    border border-slate-200 bg-white
                    shadow-sm"
            >

                <!-- =================================================
                    DECORATIVE BACKGROUND
                ================================================== -->
                <div
                    class="pointer-events-none absolute inset-y-0 right-0
                        hidden w-1/3 overflow-hidden sm:block"
                    aria-hidden="true"
                >
                    <div
                        class="absolute -right-16 -top-20
                            h-52 w-52 rounded-full
                            bg-violet-100/60 blur-3xl"
                    ></div>

                    <div
                        class="absolute right-20 -bottom-24
                            h-44 w-44 rounded-full
                            bg-indigo-50 blur-3xl"
                    ></div>

                    <!-- Subtle grid pattern -->
                    <div
                        class="absolute inset-0 opacity-[0.035]"
                        style="
                            background-image:
                            linear-gradient(#7c3aed 1px, transparent 1px),
                            linear-gradient(90deg, #7c3aed 1px, transparent 1px);
                            background-size: 24px 24px;
                        "
                    ></div>
                </div>


                <!-- =================================================
                    MAIN HEADER CONTENT
                ================================================== -->
                <div
                    class="relative px-5 py-6
                        sm:px-7 sm:py-8"
                >

                    <div
                        class="flex flex-col gap-6
                            sm:flex-row sm:items-center
                            sm:justify-between"
                    >

                        <!-- =================================================
                            LEFT CONTENT
                        ================================================== -->
                        <div class="flex min-w-0 items-start gap-4 sm:gap-5">

                            <!-- Brand Icon -->
                            <div class="relative shrink-0">

                                <div
                                    class="flex h-14 w-14 items-center
                                        justify-center rounded-2xl
                                        bg-violet-600 text-white
                                        shadow-lg
                                        shadow-violet-600/20
                                        sm:h-16 sm:w-16"
                                >

                                    <svg
                                        class="h-7 w-7 sm:h-8 sm:w-8"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        viewBox="0 0 24 24"
                                        aria-hidden="true"
                                    >

                                        <!-- Person -->
                                        <circle
                                            cx="9"
                                            cy="7"
                                            r="4"
                                        />

                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M2 21v-2a7 7 0 0 1 14 0v2"
                                        />

                                        <!-- Plus -->
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M19 8v6m3-3h-6"
                                        />

                                    </svg>

                                </div>

                                <!-- Small status indicator -->
                                <span
                                    class="absolute -right-1 -top-1
                                        flex h-5 w-5 items-center
                                        justify-center rounded-full
                                        border-2 border-white
                                        bg-emerald-500"
                                    title="Ready"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full
                                            bg-white"
                                    ></span>
                                </span>

                            </div>


                            <!-- =================================================
                                TEXT CONTENT
                            ================================================== -->
                            <div class="min-w-0">

                                <!-- Eyebrow -->
                                <div
                                    class="mb-2 flex flex-wrap
                                        items-center gap-2"
                                >

                                    <span
                                        class="text-[11px] font-bold
                                            uppercase tracking-[0.14em]
                                            text-violet-600"
                                    >
                                        Teacher Evaluation
                                    </span>

                                    <span
                                        class="h-1 w-1 rounded-full
                                            bg-slate-300"
                                        aria-hidden="true"
                                    ></span>

                                    <!-- Student Type -->
                                    <span
                                        class="inline-flex items-center
                                            gap-1.5 rounded-full
                                            bg-amber-50 px-2.5 py-1
                                            text-[11px] font-semibold
                                            text-amber-700
                                            ring-1 ring-inset
                                            ring-amber-200"
                                    >

                                        <span
                                            class="h-1.5 w-1.5 rounded-full
                                                bg-amber-500"
                                        ></span>

                                        Irregular Student

                                    </span>

                                </div>


                                <!-- Main Heading -->
                                <h1
                                    class="text-2xl font-bold
                                        tracking-tight text-slate-900
                                        sm:text-3xl"
                                >
                                    Select Your Teacher
                                </h1>


                                <!-- Description -->
                                <p
                                    class="mt-2 max-w-xl
                                        text-sm leading-6
                                        text-slate-500 sm:text-[15px]"
                                >
                                    Find the teacher you want to evaluate.
                                    Search by name below and select the appropriate teacher
                                    to continue.
                                </p>

                            </div>

                        </div>


                        <!-- =================================================
                            RIGHT CONTEXT CARD
                        ================================================== -->
                        <div
                            class="relative shrink-0
                                sm:w-[230px]"
                        >

                            <div
                                class="rounded-xl border border-violet-100
                                    bg-violet-50/70 p-4"
                            >

                                <div class="flex items-start gap-3">

                                    <!-- Icon -->
                                    <div
                                        class="flex h-9 w-9 shrink-0
                                            items-center justify-center
                                            rounded-lg bg-white
                                            text-violet-600
                                            shadow-sm ring-1
                                            ring-violet-100"
                                    >

                                        <svg
                                            class="h-4.5 w-4.5"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="1.8"
                                            viewBox="0 0 24 24"
                                            aria-hidden="true"
                                        >

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 14a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z"
                                            />

                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M4 20a8 8 0 0 1 16 0"
                                            />

                                        </svg>

                                    </div>


                                    <div class="min-w-0">

                                        <p
                                            class="text-xs font-semibold
                                                text-violet-900"
                                        >
                                            Teacher Selection
                                        </p>

                                        <p
                                            class="mt-1 text-[11px]
                                                leading-5 text-violet-700/70"
                                        >
                                            Select the correct teacher before
                                            starting the evaluation.
                                        </p>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                    INFORMATION FOOTER
                ================================================== -->
                <div
                    class="relative flex items-center gap-3
                        border-t border-slate-100
                        bg-slate-50/70
                        px-5 py-3.5
                        sm:px-7"
                >

                    <!-- Info Icon -->
                    <div
                        class="flex h-7 w-7 shrink-0
                            items-center justify-center
                            rounded-lg bg-violet-100
                            text-violet-600"
                    >

                        <svg
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >

                            <circle
                                cx="12"
                                cy="12"
                                r="9"
                            />

                            <path
                                stroke-linecap="round"
                                d="M12 11v5"
                            />

                            <path
                                stroke-linecap="round"
                                d="M12 8h.01"
                            />

                        </svg>

                    </div>


                    <div class="min-w-0">

                        <p class="text-xs text-slate-500">

                            <span
                                class="font-semibold text-slate-700"
                            >
                                Before you continue:
                            </span>

                            Make sure you select the teacher assigned to
                            your course or class.

                        </p>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             MAIN CONTENT CARD
        ====================================================== -->
        <div
            class="overflow-hidden rounded-2xl border border-slate-200
                   bg-white shadow-sm"
        >


            <!-- =================================================
                 SEARCH
            ================================================== -->
            <div class="border-b border-slate-200 p-4 sm:p-5">

                <div class="relative w-full">

                    <label
                        for="teacherSearch"
                        class="sr-only"
                    >
                        Search teachers
                    </label>

                    <!-- Search Icon -->
                    <div
                        class="pointer-events-none absolute inset-y-0 left-0
                               flex items-center pl-4 text-slate-400"
                    >
                        <svg
                            class="h-5 w-5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.8"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <circle
                                cx="11"
                                cy="11"
                                r="7"
                            />

                            <path
                                stroke-linecap="round"
                                d="m20 20-4-4"
                            />
                        </svg>
                    </div>

                    <!-- Search Input -->
                    <input
                        id="teacherSearch"
                        type="search"
                        placeholder="Search teacher by name..."
                        autocomplete="off"
                        class="h-12 w-full rounded-xl border
                               border-slate-200 bg-slate-50
                               pl-11 pr-4 text-sm text-slate-900
                               outline-none transition duration-200

                               placeholder:text-slate-400

                               hover:border-slate-300

                               focus:border-violet-500
                               focus:bg-white
                               focus:ring-4
                               focus:ring-violet-500/10"
                    />

                </div>

            </div>


            <!-- =================================================
                 TEACHER SECTION HEADER
            ================================================== -->
            <div
                class="flex items-center justify-between
                       px-4 py-4 sm:px-5"
            >

                <div>

                    <h2 class="text-sm font-semibold text-slate-900">
                        Available Teachers
                    </h2>

                    <p class="mt-0.5 text-xs text-slate-500">
                        Select the teacher you want to evaluate.
                    </p>

                </div>


                <!-- Teacher Count -->
                <div
                    id="teacherCount"
                    class="rounded-full bg-slate-100
                           px-3 py-1 text-xs font-medium
                           text-slate-500"
                >
                    Teachers
                </div>

            </div>


            <!-- =================================================
                 TEACHERS GRID
            ================================================== -->
            <div
                id="teachersGrid"
                class="grid grid-cols-1 gap-4
                       px-4 pb-5
                       sm:grid-cols-2 sm:px-5
                       lg:grid-cols-3
                       xl:grid-cols-4"
            >

                <!--
                    Existing JavaScript-generated teacher cards
                    should continue to be inserted here.

                    Keep your existing:
                    - IDs
                    - data attributes
                    - event listeners
                    - selection logic
                -->

            </div>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->
            <div
                id="emptyTeachersState"
                class="hidden px-6 py-14 text-center"
            >

                <div
                    class="mx-auto flex h-14 w-14 items-center
                           justify-center rounded-2xl
                           bg-slate-100 text-slate-400"
                >

                    <svg
                        class="h-7 w-7"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <circle
                            cx="11"
                            cy="11"
                            r="7"
                        />

                        <path
                            stroke-linecap="round"
                            d="m20 20-4-4"
                        />
                    </svg>

                </div>

                <h3
                    class="mt-4 text-base font-semibold text-slate-900"
                >
                    No teachers found
                </h3>

                <p
                    class="mx-auto mt-1 max-w-sm
                           text-sm leading-6 text-slate-500"
                >
                    We couldn't find a teacher matching your search.
                    Try searching for a different name.
                </p>

                <button
                    id="emptyClearSearchBtn"
                    type="button"
                    class="mt-5 inline-flex items-center
                           justify-center rounded-xl
                           border border-slate-200
                           bg-white px-4 py-2.5
                           text-sm font-semibold text-slate-700
                           shadow-sm transition duration-200

                           hover:border-violet-200
                           hover:bg-violet-50
                           hover:text-violet-700

                           focus:outline-none
                           focus:ring-4
                           focus:ring-violet-500/10"
                >
                    Clear Search
                </button>

            </div>


            <!-- =================================================
                 LOADING SKELETON
            ================================================== -->
            <div
                id="teachersLoading"
                class="hidden grid grid-cols-1 gap-4
                       px-4 pb-5
                       sm:grid-cols-2 sm:px-5
                       lg:grid-cols-3
                       xl:grid-cols-4"
            >

                <!-- Skeleton 1 -->
                <div
                    class="h-[220px] animate-pulse rounded-2xl
                           border border-slate-200
                           bg-white p-5"
                >

                    <div class="flex justify-between">

                        <div
                            class="h-12 w-12 rounded-xl
                                   bg-slate-200"
                        ></div>

                        <div
                            class="h-6 w-16 rounded-full
                                   bg-slate-200"
                        ></div>

                    </div>

                    <div
                        class="mt-5 h-4 w-32 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-2 h-3 w-44 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-4 h-4 w-28 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-7 h-10 w-full rounded-xl
                               bg-slate-200"
                    ></div>

                </div>


                <!-- Skeleton 2 -->
                <div
                    class="h-[220px] animate-pulse rounded-2xl
                           border border-slate-200
                           bg-white p-5"
                >

                    <div class="flex justify-between">

                        <div
                            class="h-12 w-12 rounded-xl
                                   bg-slate-200"
                        ></div>

                        <div
                            class="h-6 w-16 rounded-full
                                   bg-slate-200"
                        ></div>

                    </div>

                    <div
                        class="mt-5 h-4 w-32 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-2 h-3 w-44 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-4 h-4 w-28 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-7 h-10 w-full rounded-xl
                               bg-slate-200"
                    ></div>

                </div>


                <!-- Skeleton 3 -->
                <div
                    class="hidden h-[220px] animate-pulse
                           rounded-2xl border border-slate-200
                           bg-white p-5 lg:block"
                >

                    <div class="flex justify-between">

                        <div
                            class="h-12 w-12 rounded-xl
                                   bg-slate-200"
                        ></div>

                        <div
                            class="h-6 w-16 rounded-full
                                   bg-slate-200"
                        ></div>

                    </div>

                    <div
                        class="mt-5 h-4 w-32 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-2 h-3 w-44 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-4 h-4 w-28 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-7 h-10 w-full rounded-xl
                               bg-slate-200"
                    ></div>

                </div>


                <!-- Skeleton 4 -->
                <div
                    class="hidden h-[220px] animate-pulse
                           rounded-2xl border border-slate-200
                           bg-white p-5 xl:block"
                >

                    <div class="flex justify-between">

                        <div
                            class="h-12 w-12 rounded-xl
                                   bg-slate-200"
                        ></div>

                        <div
                            class="h-6 w-16 rounded-full
                                   bg-slate-200"
                        ></div>

                    </div>

                    <div
                        class="mt-5 h-4 w-32 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-2 h-3 w-44 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-4 h-4 w-28 rounded
                               bg-slate-200"
                    ></div>

                    <div
                        class="mt-7 h-10 w-full rounded-xl
                               bg-slate-200"
                    ></div>

                </div>

            </div>


            <!-- =================================================
                 SELECTION SUMMARY
            ================================================== -->
            <div
                id="summarySection"
                class="hidden border-t border-slate-200
                       bg-slate-50/80 px-4 py-4 sm:px-5"
            >

                <div
                    class="flex flex-col gap-4
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >

                    <div class="flex items-center gap-3">

                        <!-- Selected Icon -->
                        <div
                            class="flex h-10 w-10 shrink-0
                                   items-center justify-center
                                   rounded-xl bg-emerald-100
                                   text-emerald-600"
                        >

                            <svg
                                class="h-5 w-5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="m5 12 4 4L19 6"
                                />
                            </svg>

                        </div>


                        <div>

                            <p
                                class="text-sm font-semibold
                                       text-slate-900"
                            >
                                <span id="selectedCount">0</span>
                                teacher selected
                            </p>

                            <p
                                id="selectedList"
                                class="mt-0.5 text-xs
                                       text-slate-500"
                            ></p>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 BOTTOM ACTION
            ================================================== -->
            <div
                class="sticky bottom-0 border-t border-slate-200
                       bg-white/95 px-4 py-4
                       backdrop-blur-sm sm:px-5"
            >

                <div
                    class="flex flex-col-reverse gap-3
                           sm:flex-row sm:items-center
                           sm:justify-between"
                >

                    <p class="hidden text-xs text-slate-500 sm:block">
                        Select a teacher to continue with the evaluation.
                    </p>


                    <!-- Continue -->
                    <button
                        id="proceedBtn"
                        type="button"
                        disabled
                        class="inline-flex min-h-11 w-full
                               items-center justify-center gap-2
                               rounded-xl bg-violet-600
                               px-6 py-3
                               text-sm font-semibold text-white
                               shadow-sm
                               shadow-violet-600/20
                               transition duration-200 ease-out

                               hover:bg-violet-700

                               active:scale-[0.98]

                               focus:outline-none
                               focus:ring-4
                               focus:ring-violet-500/20

                               disabled:cursor-not-allowed
                               disabled:bg-slate-200
                               disabled:text-slate-400
                               disabled:shadow-none

                               sm:w-auto"
                    >

                        <span id="proceedBtnText">
                            Continue to Evaluation
                        </span>

                        <svg
                            id="proceedBtnIcon"
                            class="h-4 w-4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                            aria-hidden="true"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="m9 18 6-6-6-6"
                            />
                        </svg>

                    </button>

                </div>

            </div>

        </div>

    </div>

</div>

<script>
    window.studentContext = {
        department: <?= json_encode($student['department'] ?? '') ?>
    }
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script>

<script src="<?= BASE_URL ?>assets/js/evaluation/select_teacher/index.js" type="module"></script>
</body>
</html>