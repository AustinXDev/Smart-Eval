<?php

require_once __DIR__ . '/../../app/init.php';

use App\middleware\StudentMiddleware;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;

$studentRepo = new StudentRepository($pdo);
$evaluationRepo = new EvaluationRepository($pdo);

$studentMiddleware = new StudentMiddleware($studentRepo, $evaluationRepo);

$student = $studentMiddleware->requireAuth();

?>

<?php
$student = $_SESSION['student'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Teachers</title>

<?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>

<!-- Custom CSS -->
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
<link rel="stylesheet"  href="<?= BASE_URL ?>assets/css/receipt.css">

<!-- Icons cdn --->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body class="bg-[#F8F6F0] min-h-screen flex items-center justify-center p-4">

  
    <div class="min-h-dvh">

        <!-- =========================================================
            BACKGROUND DECORATIONS
        ========================================================== -->
        <div
            class="pointer-events-none fixed inset-0 overflow-hidden"
            aria-hidden="true"
        >
            <div class="absolute -left-24 -top-20 h-64 w-64 rounded-full bg-violet-100/50 blur-3xl"></div>
            <div class="absolute -right-24 top-24 h-72 w-72 rounded-full bg-indigo-100/50 blur-3xl"></div>
            <div class="absolute bottom-[-120px] left-1/3 h-64 w-64 rounded-full bg-amber-100/30 blur-3xl"></div>
        </div>


        <!-- =========================================================
            PAGE WRAPPER
        ========================================================== -->
        <div class="relative z-10 mx-auto flex min-h-dvh w-full max-w-3xl flex-col px-4 sm:px-6">


            <!-- =====================================================
                MAIN
            ====================================================== -->
            <main class="flex flex-1 items-center justify-center pb-10 sm:pb-14">

                <section
                    class="
                        w-full
                        max-w-xl
                        overflow-hidden
                        rounded-3xl
                        border
                        border-slate-200/80
                        bg-white
                        shadow-[0_20px_60px_rgba(22,33,62,0.08)]
                        animate-page-enter
                    "
                >

                    <!-- =================================================
                        SUCCESS HEADER
                    ================================================== -->
                    <div class="px-6 pt-8 text-center sm:px-10 sm:pt-10">

                        <!-- Success Indicator -->
                        <div 
                            class="
                                mx-auto
                                flex
                                h-20
                                w-20
                                items-center
                                justify-center
                                rounded-full
                                bg-violet-100
                                animate-[successPop_0.6s_ease-out]
                            "
                        >
                            <div 
                                class="
                                    flex
                                    h-14
                                    w-14
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-violet-600
                                    text-white
                                    shadow-lg
                                    shadow-violet-600/20
                                    animate-success-pop
                                "
                            >
                                <i class="fa-solid fa-check text-2xl"></i>
                            </div>
                        </div>


                        <!-- Heading -->
                        <h1
                            class="
                                mt-4
                                text-xl
                                font-semibold
                                tracking-tight
                                text-[#16213E]
                                sm:text-2xl
                                animate-fade-up
                            "
                            style="animation-delay: 0.35s;"
                        >
                            Evaluation Completed!
                        </h1>


                        <!-- Supporting Text -->
                        <p
                            class="
                                mx-auto
                                mt-4
                                max-w-md
                                text-sm
                                leading-6
                                text-slate-600
                                sm:text-base
                                animate-fade-up
                            "
                            style="animation-delay: 0.45s;"
                        >
                            Thank you for completing your teacher evaluations.
                            Your responses have been successfully recorded.
                        </p>

                    </div>


                    <!-- =================================================
                        CONTENT
                    ================================================== -->
                    <div class="p-6 sm:p-10">

                        <!-- =============================================
                            LOADING STATE
                        ============================================== -->
                        <div
                            id="loading"
                            class="py-10 text-center"
                        >

                            <div
                                class="
                                    mx-auto
                                    flex
                                    h-12
                                    w-12
                                    items-center
                                    justify-center
                                    rounded-full
                                    bg-violet-50
                                "
                            >
                                <div
                                    class="
                                        h-7
                                        w-7
                                        animate-spin
                                        rounded-full
                                        border-2
                                        border-violet-100
                                        border-t-violet-600
                                    "
                                ></div>
                            </div>

                            <p class="mt-4 text-sm text-slate-500">
                                Loading your evaluation results...
                            </p>

                        </div>


                        <!-- =============================================
                            DATA LOADED STATE
                        ============================================== -->
                        <div
                            id="content"
                            style="display: none;"
                        >

                            <!-- =========================================
                                COMPLETION SUMMARY
                            ========================================== -->
                            <div
                                class="
                                    grid
                                    grid-cols-1
                                    gap-3
                                    sm:grid-cols-3
                                "
                            >

                                <!-- Teachers -->
                                <div
                                    class="
                                        rounded-2xl
                                        border
                                        border-slate-200
                                        bg-slate-50
                                        p-4
                                        text-center
                                        animate-fade-up
                                        opacity-0
                                    "
                                    style="animation-delay: 0.55s;"
                                >

                                    <div
                                        class="
                                            mx-auto
                                            flex
                                            h-9
                                            w-9
                                            items-center
                                            justify-center
                                            rounded-xl
                                            bg-violet-100
                                            text-violet-600
                                        "
                                    >
                                        <i class="fa-regular fa-user"></i>
                                    </div>

                                    <p
                                        class="
                                            mt-3
                                            text-[10px]
                                            font-bold
                                            uppercase
                                            tracking-wider
                                            text-slate-400
                                        "
                                    >
                                        Teachers
                                    </p>

                                    <p
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-[#16213E]
                                        "
                                    >
                                        <span id="total-count">0</span>
                                        Teacher<span id="plural-s">s</span>
                                    </p>

                                </div>


                                <!-- Status -->
                                <div
                                    class="
                                        rounded-2xl
                                        border
                                        border-slate-200
                                        bg-slate-50
                                        p-4
                                        text-center
                                        animate-fade-up
                                        opacity-0
                                    "
                                    style="animation-delay: 0.63s;"
                                >

                                    <div
                                        class="
                                            mx-auto
                                            flex
                                            h-9
                                            w-9
                                            items-center
                                            justify-center
                                            rounded-xl
                                            bg-emerald-50
                                            text-emerald-600
                                        "
                                    >
                                        <i class="fa-regular fa-circle-check"></i>
                                    </div>

                                    <p
                                        class="
                                            mt-3
                                            text-[10px]
                                            font-bold
                                            uppercase
                                            tracking-wider
                                            text-slate-400
                                        "
                                    >
                                        Status
                                    </p>

                                    <p
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-emerald-600
                                        "
                                    >
                                        Completed
                                    </p>

                                </div>


                                <!-- Submission -->
                                <div
                                    class="
                                        rounded-2xl
                                        border
                                        border-slate-200
                                        bg-slate-50
                                        p-4
                                        text-center
                                        animate-fade-up
                                        opacity-0
                                    "
                                    style="animation-delay: 0.71s;"
                                >

                                    <div
                                        class="
                                            mx-auto
                                            flex
                                            h-9
                                            w-9
                                            items-center
                                            justify-center
                                            rounded-xl
                                            bg-indigo-50
                                            text-indigo-600
                                        "
                                    >
                                        <i class="fa-regular fa-paper-plane"></i>
                                    </div>

                                    <p
                                        class="
                                            mt-3
                                            text-[10px]
                                            font-bold
                                            uppercase
                                            tracking-wider
                                            text-slate-400
                                        "
                                    >
                                        Submission
                                    </p>

                                    <p
                                        class="
                                            mt-1
                                            text-sm
                                            font-bold
                                            text-[#16213E]
                                        "
                                    >
                                        Successfully Recorded
                                    </p>

                                </div>

                            </div>


                            <!-- =========================================
                                PERIOD
                            ========================================== -->
                            <div
                                class="
                                    mt-4
                                    flex
                                    items-center
                                    gap-3
                                    rounded-2xl
                                    border
                                    border-slate-200
                                    bg-white
                                    px-4
                                    py-3
                                    animate-fade-up
                                    opacity-0
                                "
                                style="animation-delay: 0.80s;"
                            >

                                <div
                                    class="
                                        flex
                                        h-9
                                        w-9
                                        shrink-0
                                        items-center
                                        justify-center
                                        rounded-xl
                                        bg-slate-100
                                        text-slate-500
                                    "
                                >
                                    <i class="fa-regular fa-calendar"></i>
                                </div>

                                <div class="min-w-0 flex-1">

                                    <p
                                        class="
                                            text-[10px]
                                            font-bold
                                            uppercase
                                            tracking-wider
                                            text-slate-400
                                        "
                                    >
                                        Evaluation Period
                                    </p>

                                    <p
                                        id="period-name"
                                        class="
                                            mt-0.5
                                            truncate
                                            text-sm
                                            font-semibold
                                            text-[#16213E]
                                        "
                                    >
                                        Loading...
                                    </p>

                                </div>

                            </div>


                            <!-- =========================================
                                PRIVACY / TRUST MESSAGE
                            ========================================== -->
                            <div
                                class="
                                    mt-5
                                    flex
                                    items-start
                                    gap-3
                                    rounded-2xl
                                    border
                                    border-violet-100
                                    bg-violet-50/60
                                    p-4
                                    animate-fade-up
                                    opacity-0
                                "
                                style="animation-delay: 0.88s;"
                            >

                                <div
                                    class="
                                        flex
                                        h-8
                                        w-8
                                        shrink-0
                                        items-center
                                        justify-center
                                        rounded-lg
                                        bg-white
                                        text-violet-600
                                        shadow-sm
                                    "
                                >
                                    <i class="fa-solid fa-shield-halved text-xs"></i>
                                </div>

                                <div>

                                    <p
                                        class="
                                            text-xs
                                            font-semibold
                                            text-violet-900
                                        "
                                    >
                                        Your responses are securely recorded.
                                    </p>

                                    <p
                                        class="
                                            mt-1
                                            text-[11px]
                                            leading-5
                                            text-violet-700/70
                                        "
                                    >
                                        Your feedback will be used to support
                                        teacher evaluation and academic improvement.
                                    </p>

                                </div>

                            </div>


                            <!-- =========================================
                                TEACHERS LIST
                            ========================================== -->
                            <div
                                id="teachers-list"
                                style="display: none;"
                                class="
                                teacher-list-scrollbar
                                mt-6"
                            >

                                <div
                                    class="
                                        mb-6
                                        max-h-64
                                        space-y-2
                                        overflow-y-auto
                                        border-b
                                        border-slate-100
                                        pb-4
                                    "
                                >
                                    <!-- Teachers will be inserted here -->
                                </div>


                                <!-- PDF ACTIONS -->
                                <div
                                    id="pdf-actions"
                                    class="
                                        flex
                                        flex-col
                                        gap-2
                                        sm:flex-row
                                    "
                                >

                                    <button
                                        id="download"
                                        class="
                                            inline-flex
                                            flex-1
                                            items-center
                                            justify-center
                                            gap-2
                                            rounded-xl
                                            bg-[#7C3AED]
                                            px-4
                                            py-3
                                            text-sm
                                            font-semibold
                                            text-white
                                            shadow-lg
                                            shadow-violet-600/20
                                            transition
                                            duration-200
                                            hover:bg-[#5B21B6]
                                            hover:shadow-xl
                                            focus:outline-none
                                            focus:ring-4
                                            focus:ring-violet-500/20
                                            active:scale-[0.99]
                                        "
                                    >
                                        <i class="fa-regular fa-file-pdf"></i>
                                        <span>
                                          Download
                                        </span>
                                    </button>


                                    <a
                                        href="../../app/auth/logout.php"
                                        class="
                                            inline-flex
                                            flex-1
                                            items-center
                                            justify-center
                                            gap-2
                                            rounded-xl
                                            border
                                            border-slate-200
                                            bg-white
                                            px-4
                                            py-3
                                            text-sm
                                            font-semibold
                                            text-slate-600
                                            transition
                                            duration-200
                                            hover:bg-slate-50
                                            hover:text-slate-800
                                            focus:outline-none
                                            focus:ring-4
                                            focus:ring-slate-200
                                        "
                                    >
                                        <i class="fa-solid fa-arrow-left text-xs"></i>
                                        Go Back
                                    </a>

                                </div>

                            </div>

                        </div>


                        <!-- =============================================
                            EMPTY STATE
                        ============================================== -->
                        <div
                            id="empty-state"
                            style="display: none;"
                            class="py-8 text-center"
                        >

                            <div
                                class="
                                    mx-auto
                                    flex
                                    h-16
                                    w-16
                                    items-center
                                    justify-center
                                    rounded-2xl
                                    bg-violet-50
                                    text-violet-500
                                "
                            >
                                <i class="fa-regular fa-clipboard text-2xl"></i>
                            </div>

                            <p
                                class="
                                    mt-5
                                    text-xl
                                    font-bold
                                    text-[#16213E]
                                "
                            >
                                No Evaluations Yet
                            </p>

                            <p
                                class="
                                    mx-auto
                                    mt-2
                                    max-w-sm
                                    text-sm
                                    leading-6
                                    text-slate-500
                                "
                            >
                                You haven't evaluated any teachers in the
                                current period.
                            </p>

                            <div class="mt-6">

                                <button
                                    onclick="window.location.href='/Smart-Eval/views/student/evaluation.view.php'"
                                    class="
                                        inline-flex
                                        items-center
                                        justify-center
                                        gap-2
                                        rounded-xl
                                        bg-[#7C3AED]
                                        px-5
                                        py-3
                                        text-sm
                                        font-semibold
                                        text-white
                                        shadow-lg
                                        shadow-violet-600/20
                                        transition
                                        duration-200
                                        hover:bg-[#5B21B6]
                                        focus:outline-none
                                        focus:ring-4
                                        focus:ring-violet-500/20
                                    "
                                >
                                    <span>Start Evaluation</span>
                                    <i class="fa-solid fa-arrow-right text-xs"></i>
                                </button>

                            </div>

                        </div>


                        <!-- =============================================
                            ERROR STATE
                        ============================================== -->
                        <div
                            id="error-state"
                            style="display: none;"
                            class="py-8 text-center"
                        >

                            <div
                                class="
                                    mx-auto
                                    flex
                                    h-16
                                    w-16
                                    items-center
                                    justify-center
                                    rounded-2xl
                                    bg-red-50
                                    text-red-500
                                "
                            >
                                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                            </div>

                            <p
                                class="
                                    mt-5
                                    text-lg
                                    font-bold
                                    text-[#16213E]
                                "
                                id="error-message"
                            >
                                Error Loading Data
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                We couldn't load your evaluation information.
                            </p>

                            <button
                                onclick="location.reload()"
                                class="
                                    mt-6
                                    inline-flex
                                    items-center
                                    justify-center
                                    gap-2
                                    rounded-xl
                                    bg-[#16213E]
                                    px-5
                                    py-3
                                    text-sm
                                    font-semibold
                                    text-white
                                    transition
                                    duration-200
                                    hover:bg-[#1A1A2E]
                                    focus:outline-none
                                    focus:ring-4
                                    focus:ring-slate-400/20
                                "
                            >
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                Retry
                            </button>

                        </div>

                    </div>


                    <!-- =================================================
                        FOOTER
                    ================================================== -->
                    <div
                        class="
                            border-t
                            border-slate-100
                            px-6
                            py-4
                            text-center
                            sm:px-10
                        "
                    >
                        <p class="text-[10px] text-slate-400">
                            SMART-EVAL · Academic Evaluation System
                        </p>
                    </div>

                </section>

            </main>

        </div>

    </div>



    <script> 
        window.BASE_URL = <?= json_encode(BASE_URL) ?>; 
        window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>
    </script> 

    <script src="<?= BASE_URL ?>assets/js/evaluation/evaluation_summary/index.js" type="module"></script>
</body>

</html>