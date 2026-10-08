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

$studentMiddleware->handleEnrollmentRedirect($student, basename($requestPath))
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Enrollment Type - Teacher Evaluation</title>

    <?php include_once __DIR__ . '/../../public/assets/includes/head.php' ?>

    <link rel="stylesheet" href="/Smart-Eval/public/assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body class="flex justify-center items-start min-h-screen bg-gray-50 py-4">

    <!-- ============================================================
        SMART-EVAL — CHOOSE ENROLLMENT TYPE
    ============================================================ -->

    <main class="relative min-h-screen w-full overflow-hidden bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">

    <!-- ==========================================================
        SUBTLE BACKGROUND DECORATION
    =========================================================== -->
    <div class="pointer-events-none absolute inset-0 overflow-hidden">

        <!-- Top-left violet glow -->
        <div
        class="absolute -left-32 -top-32 h-80 w-80 rounded-full
                bg-violet-300/10 blur-3xl"
        ></div>

        <!-- Bottom-right indigo glow -->
        <div
        class="absolute -bottom-40 -right-32 h-96 w-96 rounded-full
                bg-indigo-300/10 blur-3xl"
        ></div>

        <!-- Subtle grid -->
        <div
        class="absolute inset-0 opacity-[0.025]"
        style="
            background-image:
            linear-gradient(to right, #64748b 1px, transparent 1px),
            linear-gradient(to bottom, #64748b 1px, transparent 1px);
            background-size: 32px 32px;
        "
        ></div>

    </div>


    <!-- ==========================================================
        CONTENT CONTAINER
    =========================================================== -->
    <div class="relative z-10 mx-auto w-full max-w-5xl">

        <!-- ========================================================
            PAGE HEADER
        ========================================================= -->
        <header class="mb-8 text-center sm:mb-10
                        animate-[fadeSlideDown_0.7s_ease-out_both]">

        <!-- Branded icon -->
        <div class="relative mx-auto mb-5 w-fit">

            <!-- Soft glow -->
            <div
            class="absolute inset-0 rounded-2xl
                    bg-violet-400/20 blur-xl"
            ></div>

            <div
            class="relative flex h-14 w-14 items-center justify-center
                    rounded-2xl
                    bg-gradient-to-br from-violet-600 to-indigo-600
                    text-white
                    shadow-lg shadow-violet-500/20"
            >
            <i class="fas fa-clipboard-check text-xl"></i>
            </div>

        </div>


        <!-- Eyebrow -->
        <p
            class="mb-2 text-[11px] font-bold uppercase
                tracking-[0.18em] text-violet-600"
        >
            Enrollment Setup
        </p>


        <!-- Heading -->
        <h1
            class="text-2xl font-bold tracking-tight text-slate-900
                sm:text-3xl lg:text-4xl"
        >
            Choose your enrollment type
        </h1>


        <!-- Description -->
        <p
            class="mx-auto mt-3 max-w-xl text-sm leading-6 text-slate-500
                sm:text-base"
        >
            Select the option that best matches your enrollment status
            to continue with your teacher evaluation.
        </p>

        </header>



        <!-- ========================================================
            STUDENT CONTEXT CARD
        ========================================================= -->
        <section
        class="group relative mb-9 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-sm transition-shadow duration-300 animate-[fadeSlideUp_0.7s_ease-out_0.15s_both]"
        >

        <!-- Accent line -->
        <div
            class="absolute inset-x-0 top-0 h-0.5
                bg-gradient-to-r from-violet-500
                via-indigo-500 to-violet-500"
        ></div>


        <div
            class="flex flex-col gap-5 p-5
                sm:p-6 md:flex-row md:items-center
                md:justify-between"
        >

            <!-- ====================================================
                STUDENT PROFILE
            ===================================================== -->
            <div class="flex min-w-0 items-center gap-4">

            <!-- Avatar -->
            <div class="relative shrink-0">

                <div
                class="flex h-12 w-12 items-center justify-center
                        rounded-2xl
                        bg-violet-50 text-violet-600
                        ring-1 ring-violet-100"
                >
                <i class="fas fa-user-graduate"></i>
                </div>

                <!-- Online/status indicator -->
                <span
                class="absolute -bottom-0.5 -right-0.5
                        h-3 w-3 rounded-full
                        border-2 border-white bg-emerald-500"
                ></span>

            </div>


            <!-- Student information -->
            <div class="min-w-0">

                <p
                class="text-[10px] font-bold uppercase
                        tracking-[0.16em] text-slate-400"
                >
                Welcome back
                </p>

                <h2
                class="mt-1 truncate text-base font-bold
                        text-slate-900 sm:text-lg"
                >
                <?php echo htmlspecialchars($student['full_name']); ?>
                </h2>

                <p class="mt-0.5 text-xs text-slate-400">
                Your evaluation profile
                </p>

            </div>

            </div>



            <!-- ====================================================
                PROGRAM
            ===================================================== -->
            <div
            id="program"
            data-program-id="<?php echo $student['program_id']?>"
            class="flex w-full items-center gap-3
                    rounded-2xl border border-slate-200
                    bg-slate-50/80 px-3.5 py-3
                    sm:w-auto"
            >

            <span
                class="flex h-9 w-9 shrink-0 items-center justify-center
                    rounded-xl bg-white text-violet-600
                    shadow-sm ring-1 ring-slate-100"
            >
                <i class="fas fa-book-open text-sm"></i>
            </span>

            <div class="min-w-0">

                <p
                class="text-[10px] font-semibold uppercase
                        tracking-wider text-slate-400"
                >
                Academic Program
                </p>

                <p
                id="programName"
                class="mt-0.5 truncate text-sm font-bold text-slate-700"
                >
                <?php echo htmlspecialchars($student['program_name']); ?>
                </p>

            </div>

            </div>

        </div>

        </section>



        <!-- ========================================================
            ENROLLMENT FORM
        ========================================================= -->
        <form id="enrollmentForm" method="POST">

        <input
            type="hidden"
            name="enrollment_type"
            id="selectedTypeInput"
            value=""
        >


        <!-- ======================================================
            SECTION HEADER
        ======================================================= -->
        <div
            class="mb-5 flex flex-col items-center gap-2
                sm:flex-row sm:items-end sm:justify-between"
        >

            <div>

                <div class="flex items-center gap-2">

                    <span
                    class="h-1.5 w-1.5 rounded-full bg-violet-500"
                    ></span>

                    <h2
                    class="text-base font-bold text-slate-900
                            sm:text-lg"
                    >
                    Select enrollment type
                    </h2>

                </div>

                <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                    Choose the option that applies to you.
                </p>

            </div>


            <!-- Requirement indicator -->
            <span
            class="inline-flex w-fit items-center gap-1.5
                    rounded-full bg-white px-3 py-1.5
                    text-[11px] font-semibold text-slate-500
                    shadow-sm ring-1 ring-slate-200"
            >
            <i class="fas fa-circle-info text-violet-500"></i>
            One selection required
            </span>

        </div>



        <!-- ======================================================
            ENROLLMENT OPTIONS
        ======================================================= -->
        <div
            class="mb-7 grid grid-cols-1 gap-5 md:grid-cols-2"
        >


            <!-- ====================================================
                REGULAR STUDENT
            ===================================================== -->
            <div
            class="option-card group relative cursor-pointer overflow-hidden rounded-3xl border-2 border-slate-200 bg-white p-5 shadow-sm outline-none transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/5 focus-visible:ring-4 focus-visible:ring-violet-500/20 sm:p-6 animate-[fadeSlideUp_0.7s_ease-out_0.35s_both]"
            tabindex="0"
            role="button"
            aria-label="Select Regular Student"
            data-enrollment-type="regular"
            >

            <!-- Selected accent -->
            <div
                class="selection-accent absolute inset-x-0 top-0 h-1
                    bg-gradient-to-r from-violet-500 to-indigo-500
                    opacity-0 transition-opacity duration-200"
            ></div>


            <!-- Top row -->
            <div class="flex items-start justify-between">

                <!-- Icon -->
                <div
                class="option-icon flex h-12 w-12 items-center justify-center
                        rounded-2xl bg-violet-50 text-violet-600
                        ring-1 ring-violet-100
                        transition-all duration-200
                        group-hover:bg-violet-100"
                >
                <i class="fas fa-user-check text-lg"></i>
                </div>


                <!-- Selection indicator -->
                <div
                class="selection-indicator flex h-7 w-7 items-center
                        justify-center rounded-full
                        border-2 border-slate-200
                        bg-white text-transparent
                        transition-all duration-200"
                >
                <i class="fas fa-check text-[10px]"></i>
                </div>

            </div>


            <!-- Content -->
            <div class="mt-5">

                <div class="flex items-center gap-2">

                <h3
                    class="text-lg font-bold tracking-tight text-slate-900"
                >
                    Regular Student
                </h3>

                <span
                    class="rounded-full bg-violet-50 px-2 py-0.5
                        text-[9px] font-bold uppercase
                        tracking-wide text-violet-600"
                >
                    Recommended
                </span>

                </div>


                <p
                class="mt-2 max-w-md text-sm leading-6 text-slate-500"
                >
                Your teachers are already assigned through your
                academic program.
                </p>

            </div>


            <!-- Benefits -->
            <div
                class="mt-6 border-t border-slate-100 pt-5"
            >

                <p
                class="mb-3 text-[10px] font-bold uppercase
                        tracking-wider text-slate-400"
                >
                What you'll get
                </p>


                <div class="space-y-2.5">

                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-emerald-50 text-emerald-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Assigned teachers
                    </span>

                </div>


                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-emerald-50 text-emerald-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Automatic teacher list
                    </span>

                </div>


                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-emerald-50 text-emerald-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Faster evaluation setup
                    </span>

                </div>

                </div>

            </div>


            <!-- Bottom helper -->
            <div
                class="mt-5 flex items-center gap-2
                    text-[11px] font-medium text-slate-400"
            >
                <i class="fas fa-bolt text-violet-400"></i>
                Recommended for most students
            </div>

            </div>



            <!-- ====================================================
                IRREGULAR STUDENT
            ===================================================== -->
            <div
            class="option-card group relative cursor-pointer overflow-hidden rounded-3xl border-2 border-slate-200 bg-white p-5 shadow-sm outline-none transition-all duration-200 ease-out hover:-translate-y-0.5 hover:border-violet-300 hover:shadow-lg hover:shadow-violet-500/5 focus-visible:ring-4 focus-visible:ring-violet-500/20 sm:p-6 animate-[fadeSlideUp_0.7s_ease-out_0.45s_both]"
            tabindex="0"
            role="button"
            aria-label="Select Irregular Student"
            data-enrollment-type="irregular"
            >

            <!-- Selected accent -->
            <div
                class="selection-accent absolute inset-x-0 top-0 h-1
                    bg-gradient-to-r from-amber-400 to-orange-400
                    opacity-0 transition-opacity duration-200"
            ></div>


            <!-- Top row -->
            <div class="flex items-start justify-between">

                <!-- Icon -->
                <div
                class="option-icon flex h-12 w-12 items-center justify-center
                        rounded-2xl bg-amber-50 text-amber-600
                        ring-1 ring-amber-100
                        transition-all duration-200
                        group-hover:bg-amber-100"
                >
                <i class="fas fa-pen-to-square text-lg"></i>
                </div>


                <!-- Selection indicator -->
                <div
                class="selection-indicator flex h-7 w-7 items-center
                        justify-center rounded-full
                        border-2 border-slate-200
                        bg-white text-transparent
                        transition-all duration-200"
                >
                <i class="fas fa-check text-[10px]"></i>
                </div>

            </div>


            <!-- Content -->
            <div class="mt-5">

                <h3
                class="text-lg font-bold tracking-tight text-slate-900"
                >
                Irregular Student
                </h3>

                <p
                class="mt-2 max-w-md text-sm leading-6 text-slate-500"
                >
                You need to manually select the teachers you want
                to include in your evaluation.
                </p>

            </div>


            <!-- Benefits -->
            <div
                class="mt-6 border-t border-slate-100 pt-5"
            >

                <p
                class="mb-3 text-[10px] font-bold uppercase
                        tracking-wider text-slate-400"
                >
                What you'll get
                </p>


                <div class="space-y-2.5">

                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-amber-50 text-amber-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Choose your teachers
                    </span>

                </div>


                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-amber-50 text-amber-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Flexible teacher selection
                    </span>

                </div>


                <!-- Benefit -->
                <div class="flex items-center gap-2.5">

                    <span
                    class="flex h-5 w-5 shrink-0 items-center
                            justify-center rounded-full
                            bg-amber-50 text-amber-600"
                    >
                    <i class="fas fa-check text-[8px]"></i>
                    </span>

                    <span class="text-xs font-medium text-slate-600">
                    Custom evaluation list
                    </span>

                </div>

                </div>

            </div>


            <!-- Bottom helper -->
            <div
                class="mt-5 flex items-center gap-2
                    text-[11px] font-medium text-slate-400"
            >
                <i class="fas fa-sliders text-amber-400"></i>
                Best for students with a custom schedule
            </div>

            </div>

        </div>



        <!-- ======================================================
            CONTINUE CTA
        ======================================================= -->
        <div class="flex justify-center pb-8">

            <button
            type="submit"
            id="proceedBtn"
            disabled
            class="group relative inline-flex min-h-12
                    w-full max-w-xs items-center justify-center
                    gap-2.5 overflow-hidden rounded-2xl
                    bg-violet-600 px-7 py-3
                    text-sm font-bold text-white
                    shadow-md shadow-violet-600/15
                    transition-all duration-200
                    hover:-translate-y-0.5
                    hover:bg-violet-700
                    hover:shadow-lg hover:shadow-violet-600/20
                    active:translate-y-0 active:scale-[0.98]
                    focus:outline-none
                    focus-visible:ring-4
                    focus-visible:ring-violet-500/25
                    disabled:cursor-not-allowed
                    disabled:translate-y-0
                    disabled:bg-slate-200
                    disabled:text-slate-400
                    disabled:shadow-none
                    sm:w-auto sm:min-w-[190px]"
            >

            <span id="btnText">
                Continue
            </span>

            <i
                id="btnArrow"
                class="fas fa-arrow-right text-xs
                    transition-transform duration-200
                    group-hover:translate-x-1"
            ></i>


            <!-- Loading spinner -->
            <svg
                id="btnLoader"
                class="hidden h-4 w-4 animate-spin"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                aria-hidden="true"
            >
                <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
                ></circle>

                <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8v8H4z"
                ></path>

            </svg>

            </button>

        </div>

        </form>



        <!-- ========================================================
            DECISION GUIDE
        ========================================================= -->
        <section
        class="mb-8 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

        <!-- Guide header -->
        <div class="flex items-start gap-4">

            <div
            class="flex h-10 w-10 shrink-0 items-center
                    justify-center rounded-xl
                    bg-slate-100 text-slate-600"
            >
            <i class="fas fa-circle-question"></i>
            </div>

            <div>

            <h3 class="text-sm font-bold text-slate-900">
                Not sure which one to choose?
            </h3>

            <p class="mt-1 text-xs leading-5 text-slate-500">
                Use these quick descriptions to find the right option.
            </p>

            </div>

        </div>


        <!-- Decision options -->
        <div
            class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2"
        >

            <!-- Regular guide -->
            <div
            class="flex items-start gap-3 rounded-2xl
                    bg-violet-50/60 p-4 ring-1 ring-violet-100"
            >

            <span
                class="flex h-8 w-8 shrink-0 items-center
                    justify-center rounded-lg
                    bg-white text-violet-600 shadow-sm"
            >
                <i class="fas fa-user-check text-xs"></i>
            </span>

            <div>

                <p class="text-xs font-bold text-slate-800">
                Regular Student
                </p>

                <p class="mt-0.5 text-[11px] leading-5 text-slate-500">
                You are taking the subjects normally scheduled
                for your year level.
                </p>

            </div>

            </div>


            <!-- Irregular guide -->
            <div
            class="flex items-start gap-3 rounded-2xl
                    bg-amber-50/60 p-4 ring-1 ring-amber-100"
            >

            <span
                class="flex h-8 w-8 shrink-0 items-center
                    justify-center rounded-lg
                    bg-white text-amber-600 shadow-sm"
            >
                <i class="fas fa-pen-to-square text-xs"></i>
            </span>

            <div>

                <p class="text-xs font-bold text-slate-800">
                Irregular Student
                </p>

                <p class="mt-0.5 text-[11px] leading-5 text-slate-500">
                You are taking subjects from different year levels
                because you have a changed or delayed schedule.
                </p>

            </div>

            </div>

        </div>

        </section>


        <!-- Bottom spacing -->
        <div class="h-2"></div>

    </div>

    </main>

<script> 
    window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
    window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
</script> 

  <script src="<?= BASE_URL ?>assets/js/evaluation/select_enrollment/index.js" type="module"></script>
  <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
</body>
</html>