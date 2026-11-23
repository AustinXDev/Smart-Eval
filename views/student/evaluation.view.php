<?php

// Prevent back-button caching for protected student routes
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . '/../../app/init.php';

use App\middleware\StudentMiddleware;
use App\Repositories\StudentRepo\StudentRepository;
use App\Repositories\EvaluationRepo\EvaluationRepository;
use App\Repositories\EvaluationRepo\EvaluationStatusRepository;
use App\Repositories\TeacherRepo\TeacherRepository;
use App\Repositories\QuestionRepo\QuestionRepositories;
use App\Services\EvaluationServices\EvaluationStatusService;

$studentRepo = new StudentRepository($pdo);
$evaluationRepo = new EvaluationRepository($pdo);
$studentMiddleware = new StudentMiddleware($studentRepo, $evaluationRepo);

// Authentication, enrollment/teacher-selection checks, and "all done" redirect.
$student = $studentMiddleware->requireAuth();
$studentMiddleware->evaluationProtect($student);

/* ------------------------------------------------------------------
   Page data (same source as api/evaluation/evaluation_status.php)
------------------------------------------------------------------ */

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$department = (string) ($student['department'] ?? '');
$deptLabel  = $department === 'shs' ? 'Senior High School' : ($department === 'college' ? 'College' : ucfirst($department));

$formatYear = static function (string $year) use ($department): string {
    if (!ctype_digit($year)) {
        return $year;
    }
    $n = (int) $year;
    if ($department === 'shs') {
        return 'Grade ' . $n;
    }
    $suffix = ($n % 100 >= 11 && $n % 100 <= 13) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');
    return $n . $suffix . ' Year';
};

$initialsOf = static fn (string $name): string => implode('', array_map(
    static fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)),
    array_slice(preg_split('/\s+/', trim($name)) ?: [], 0, 2)
)) ?: '?';

$term      = $evaluationRepo->findActiveTerm($department);
$termShort = $term ? 'A.Y. ' . $term['academic_year'] : '';
$termLabel = $term ? trim($term['semester'] . ' · A.Y. ' . $term['academic_year']) : 'Current term';

$loadError = null;
$teachers  = [];
$questions = [];
$saved     = [];

try {
    $service = new EvaluationStatusService(
        $evaluationRepo,
        new EvaluationStatusRepository($pdo),
        new TeacherRepository($pdo),
        new QuestionRepositories($pdo)
    );
    $data = $service->getEvaluation($student);

    // Employee ID and class loads for each teacher (badge + metadata line).
    $meta = [];
    foreach ($evaluationRepo->findSelectableTeachers($department) as $row) {
        $meta[(int) $row['teacher_id']] = $row;
    }

    foreach ($data['teachers'] as $row) {
        $id    = (int) $row['teacher_id'];
        $loads = [];
        foreach (array_filter(explode(',', (string) ($meta[$id]['loads'] ?? ''))) as $load) {
            [$code, $year] = array_pad(explode('|', $load, 2), 2, '');
            $loads[] = ['short' => trim($code . ' ' . $year), 'long' => trim($code . ' · ' . $formatYear($year))];
        }
        $image    = trim((string) ($row['image_path'] ?? ''));
        $employee = (string) ($meta[$id]['employee_id'] ?? '');

        $teachers[] = [
            'id'        => $id,
            'evalId'    => (int) $row['eval_id'],
            'submitted' => (int) $row['is_submitted'] === 1,
            'name'      => (string) $row['full_name'],
            'initials'  => $initialsOf((string) $row['full_name']),
            'photo'     => ($image !== '' && $image !== 'default.png') ? BASE_URL . 'uploads/teachers/' . rawurlencode(basename($image)) : null,
            'employee'  => $employee,
            'badge'     => $loads[0]['short'] ?? ($employee !== '' ? $employee : 'Instructor'),
            'meta'      => implode(' · ', array_filter([$deptLabel, $employee])),
            'handles'   => $loads ? 'Handles ' . implode(', ', array_column($loads, 'long')) : '',
        ];
    }

    foreach ($data['questions'] as $row) {
        $questions[] = ['id' => (int) $row['question_id'], 'text' => (string) $row['question_text']];
    }

    foreach ($data['evaluations'] as $teacherId => $row) {
        $saved[(int) $teacherId] = [
            'answers' => (object) array_map('intval', $row['answers'] ?? []),
            'comment' => (string) ($row['comment'] ?? ''),
        ];
    }

    if (!$teachers) {
        $loadError = 'There are no teachers on your evaluation list.';
    } elseif (!$questions) {
        $loadError = 'The evaluation questions are not available yet.';
    }
} catch (\Throwable $ex) {
    error_log('[evaluation view] ' . $ex->getMessage());
    $loadError = 'We could not load your evaluation. Please refresh the page.';
}

$itemTotal = count($questions) + 1; // questions + required comment

$ratings = [
    1 => ['😠', 'Strongly Disagree'],
    2 => ['🙁', 'Disagree'],
    3 => ['😐', 'Neutral'],
    4 => ['🙂', 'Agree'],
    5 => ['🤩', 'Strongly Agree'],
];

// Quick starters: chip label => text placed in the comment box (one at a time; picking another replaces it).
$starters = [
    'Clear explanations'        => 'Explains complex topics clearly and provides helpful examples during class.',
    'Supportive & approachable' => 'Very approachable, patient, and responsive when students need guidance.',
    'Organized & fair'          => 'Well-organized lessons and fair grading criteria throughout the semester.',
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Teacher Evaluation - Smart-Eval</title>

    <?php include_once __DIR__ . '/../../public/assets/includes/head.php' ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        body.eval-page { font-family: "Inter", ui-sans-serif, system-ui, sans-serif; }
        .ff-display { font-family: "Plus Jakarta Sans", "Inter", ui-sans-serif, system-ui, sans-serif; }
        .ff-mono { font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace; }

        /* Rating options: native radios, styled from the checked state. */
        .opt { transition: border-color 150ms ease, background-color 150ms ease, color 150ms ease; }
        .opt:hover { border-color: #cbd5e1; }
        .opt:has(input:focus-visible) { outline: 2px solid #4f46e5; outline-offset: 2px; }
        .opt .opt-badge { transition: background-color 150ms ease; }
        .opt[data-score="1"]:has(input:checked) { border-color: #f43f5e; background: rgb(255 241 242 / .8); color: #9f1239; }
        .opt[data-score="2"]:has(input:checked) { border-color: #f97316; background: rgb(255 247 237 / .8); color: #9a3412; }
        .opt[data-score="3"]:has(input:checked) { border-color: #f59e0b; background: rgb(255 251 235 / .8); color: #92400e; }
        .opt[data-score="4"]:has(input:checked) { border-color: #0284c7; background: rgb(240 249 255 / .8); color: #075985; }
        .opt[data-score="5"]:has(input:checked) { border-color: #059669; background: rgb(236 253 245 / .8); color: #065f46; }
        .opt[data-score="1"]:has(input:checked) .opt-badge { background: #f43f5e; }
        .opt[data-score="2"]:has(input:checked) .opt-badge { background: #f97316; }
        .opt[data-score="3"]:has(input:checked) .opt-badge { background: #f59e0b; }
        .opt[data-score="4"]:has(input:checked) .opt-badge { background: #0284c7; }
        .opt[data-score="5"]:has(input:checked) .opt-badge { background: #059669; }

        /* Question icon: clipboard until answered, then a check. */
        .q-card .icon-done { display: none; }
        .q-card[data-answered="true"] .icon-todo { display: none; }
        .q-card[data-answered="true"] .icon-done { display: block; }
        .q-card[data-answered="true"] .q-icon { background: #eef2ff; color: #4f46e5; border-color: #c7d2fe; }
        .q-card.is-flagged { border-color: #f59e0b; box-shadow: 0 0 0 1px #f59e0b; }

        /* Proportional stars: a filled row clipped to the score's width. */
        .stars { position: relative; display: inline-block; line-height: 1; letter-spacing: 2px; white-space: nowrap; }
        .stars::before { content: "★★★★★"; color: #e2e8f0; }
        .stars > span { position: absolute; inset: 0 auto 0 0; overflow: hidden; color: #f59e0b; }
        .stars > span::before { content: "★★★★★"; }

        @keyframes ready-pulse {
            0%   { box-shadow: 0 0 0 0 rgb(79 70 229 / 0.45); }
            100% { box-shadow: 0 0 0 10px rgb(79 70 229 / 0); }
        }
        .is-ready-pulse { animation: ready-pulse 900ms ease-out 2; }

        .sheet-backdrop { transition: opacity 200ms ease; }
        .sheet-panel { transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 200ms ease; }
        #reviewSheet[data-open="false"] .sheet-backdrop { opacity: 0; }
        #reviewSheet[data-open="false"] .sheet-panel { transform: translateY(100%); }
        @media (min-width: 640px) {
            #reviewSheet[data-open="false"] .sheet-panel { transform: translateY(12px) scale(0.98); opacity: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .is-ready-pulse { animation: none; }
            .sheet-backdrop, .sheet-panel, .opt { transition: none; }
            html { scroll-behavior: auto; }
        }
    </style>
</head>

<body class="eval-page min-h-dvh bg-[#F8FAFC] text-[#0F172A] antialiased">

    <!-- ============================================================
         HEADER: portal, student badge, overall progress bar
    ============================================================ -->
    <header class="sticky top-0 z-30 border-b border-[#E2E8F0] bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#4F46E5] text-white" aria-hidden="true">
                    <i class="fa-solid fa-book-open text-sm"></i>
                </span>
                <div class="min-w-0">
                    <p class="ff-display truncate text-[15px] font-bold leading-5 text-[#0F172A]">Smart-Eval Student Portal</p>
                    <p class="hidden truncate text-xs text-[#64748B] min-[420px]:block">Better Feedback. Better Learning.</p>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] p-1.5 sm:min-w-0 sm:shrink sm:pr-3.5" title="<?= $e(($student['full_name'] ?? '') . ' · ' . ($student['program_code'] ?? '') . ' - ' . ($student['year_level'] ?? '')) ?>">
                <span class="ff-display flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-xs font-bold text-white" aria-hidden="true"><?= $e($initialsOf((string) ($student['full_name'] ?? ''))) ?></span>
                <div class="hidden min-w-0 text-left sm:block">
                    <p class="max-w-56 truncate text-sm font-semibold text-[#0F172A] sm:block"><?= $e($student['full_name'] ?? '') ?></p>
                    <p class="ff-mono truncate text-[11px] text-[#64748B]">
                        <?= $e(($student['program_code'] ?? '') . ' - ' . ($student['year_level'] ?? '')) ?><?php if ($termShort): ?><span class="hidden sm:inline"> · <?= $e(str_replace('A.Y.', 'AY', $termShort)) ?></span><?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="h-1 bg-[#EEF2FF]" role="progressbar" aria-label="Overall evaluation progress" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" id="overallBar">
            <div id="overallFill" class="h-full w-0 bg-[#4F46E5] transition-[width] duration-500 ease-out motion-reduce:transition-none"></div>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 pb-64 pt-8 sm:px-6 sm:pb-44 sm:pt-12 lg:px-8">

        <?php if ($loadError): ?>
            <div class="rounded-2xl border border-[#E2E8F0] bg-white px-6 py-14 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[#F1F5F9] text-[#64748B]" aria-hidden="true"><i class="fa-solid fa-circle-exclamation"></i></span>
                <p class="ff-display mt-3 text-base font-bold text-[#0F172A]"><?= $e($loadError) ?></p>
                <p class="mt-1 text-sm text-[#64748B]">If this keeps happening, contact your administrator or dean's office.</p>
                <button type="button" onclick="location.reload()" class="mt-5 inline-flex min-h-11 items-center gap-2 rounded-lg border border-[#E2E8F0] bg-white px-4 text-sm font-semibold hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">
                    <i class="fa-solid fa-rotate-right text-xs" aria-hidden="true"></i> Refresh
                </button>
            </div>
        <?php else: ?>

        <!-- Loading: until the page script is ready (slow connections). -->
        <div id="evalLoading" role="status" aria-live="polite" class="space-y-4">
            <p class="flex items-center gap-2 text-sm text-[#475569]">
                <svg class="h-4 w-4 animate-spin text-[#4F46E5] motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" class="opacity-75"></path></svg>
                Loading your evaluation…
            </p>
            <div class="h-20 animate-pulse rounded-2xl bg-white ring-1 ring-[#E2E8F0] motion-reduce:animate-none" aria-hidden="true"></div>
            <div class="h-36 animate-pulse rounded-2xl bg-white ring-1 ring-[#E2E8F0] motion-reduce:animate-none" aria-hidden="true"></div>
            <div class="h-40 animate-pulse rounded-2xl bg-white ring-1 ring-[#E2E8F0] motion-reduce:animate-none" aria-hidden="true"></div>
            <div id="evalSlow" class="hidden flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                <p class="flex gap-2"><i class="fa-solid fa-signal mt-1 text-xs" aria-hidden="true"></i><span>This is taking longer than usual. Your connection may be slow.</span></p>
                <button type="button" onclick="location.reload()" class="min-h-11 shrink-0 rounded-lg border border-amber-300 bg-white px-4 font-semibold hover:bg-amber-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">Reload page</button>
            </div>
        </div>
        <script>
            setTimeout(function () {
                var box = document.getElementById('evalLoading'), slow = document.getElementById('evalSlow');
                if (box && !box.hidden && slow) { slow.classList.remove('hidden'); slow.classList.add('flex'); }
            }, 10000);
        </script>

        <div id="evalApp" hidden>

            <!-- ========================================================
                 PAGE HEADING + TEACHER SWITCHER
            ========================================================= -->
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <div class="min-w-0">
                    <p class="ff-mono flex items-center gap-2 text-[11px] font-semibold uppercase tracking-[0.14em] text-[#4F46E5]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#4F46E5]" aria-hidden="true"></span>
                        Faculty Performance Evaluation
                    </p>
                    <h1 class="ff-display mt-2 text-3xl font-extrabold tracking-tight text-[#0F172A] sm:text-[34px]">Teacher Evaluation</h1>
                    <p class="mt-1.5 text-[15px] text-[#475569]">Rate each statement honestly based on your classroom experience this semester.</p>
                </div>
                <nav aria-label="Teachers to evaluate" class="max-w-full shrink-0">
                    <ol id="teacherTabs" class="flex max-w-full gap-1 overflow-x-auto rounded-xl border border-[#E2E8F0] bg-white p-1 [scrollbar-width:none]"></ol>
                </nav>
            </div>

            <!-- ========================================================
                 ACTIVE INSTRUCTOR
            ========================================================= -->
            <section class="mt-6 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" aria-labelledby="teacherName">
                <div class="flex flex-col gap-5 md:flex-row md:items-center">
                    <div class="flex min-w-0 flex-1 items-center gap-4 sm:gap-5">
                        <span id="teacherAvatar" class="ff-display flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[#1E293B] text-lg font-bold text-white sm:h-[70px] sm:w-[70px] sm:text-xl" aria-hidden="true"></span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span id="teacherBadge" class="ff-mono rounded-md border border-[#C7D2FE] bg-[#EEF2FF] px-2.5 py-1 text-xs font-semibold text-[#4338CA]"></span>
                                <span id="teacherStatus" class="inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold"></span>
                            </div>
                            <h2 id="teacherName" class="ff-display mt-2 truncate text-xl font-bold tracking-tight text-[#0F172A] sm:text-[22px]"></h2>
                            <p id="teacherMeta" class="mt-0.5 truncate text-sm text-[#475569]"></p>
                        </div>
                    </div>
                    <div class="flex gap-3 rounded-xl border border-[#E2E8F0] bg-[#F8FAFC] px-5 py-4 md:w-[400px]">
                        <i class="fa-solid fa-shield-halved mt-0.5 text-emerald-600" aria-hidden="true"></i>
                        <div class="text-sm">
                            <p class="font-semibold text-[#0F172A]">Your name stays private</p>
                            <p class="mt-1 leading-6 text-[#475569]">Instructors only see combined scores and unsigned comments, never who wrote them.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ========================================================
                 QUESTIONS
            ========================================================= -->
            <section class="mt-6" aria-labelledby="statementsTitle">
                <div class="flex flex-col gap-4 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-3">
                            <h2 id="statementsTitle" class="ff-display text-lg font-bold text-[#0F172A] sm:text-xl">Evaluation Questions</h2>
                            <span id="itemsDone" class="ff-mono rounded-md border border-[#C7D2FE] bg-[#EEF2FF] px-2.5 py-1 text-xs font-semibold text-[#4338CA]">0 / <?= $itemTotal ?> Completed</span>
                        </div>
                        <p class="mt-1.5 text-sm text-[#475569]">
                            Select an emote from <strong class="font-semibold text-[#0F172A]">Strongly Disagree</strong> to
                            <strong class="font-semibold text-[#0F172A]">Strongly Agree</strong> for all <?= count($questions) ?> items, plus your written feedback.
                        </p>
                    </div>
                    <button type="button" id="jumpBtn" class="inline-flex min-h-11 shrink-0 items-center justify-center gap-2 rounded-xl border border-[#C7D2FE] bg-white px-4 text-sm font-semibold text-[#4F46E5] transition-colors hover:bg-[#EEF2FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">
                        Jump to Next Unanswered <i class="fa-solid fa-arrow-down text-xs" aria-hidden="true"></i>
                    </button>
                </div>

                <ol class="mt-5 space-y-5" id="questionList">
                    <?php foreach ($questions as $i => $q): $n = $i + 1; ?>
                        <li class="q-card scroll-mt-28 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" data-item="<?= $i ?>" data-question-id="<?= $q['id'] ?>" data-answered="false">
                            <fieldset>
                                <legend class="flex w-full items-start gap-3.5">
                                    <span class="q-icon flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] text-[#475569]" aria-hidden="true">
                                        <svg class="icon-todo h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="4" width="12" height="17" rx="2"/><path d="M9 4.5V3.5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v1"/><path d="M9.5 10.5h5M9.5 14h5"/></svg>
                                        <svg class="icon-done h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                                    </span>
                                    <span class="pt-1 text-base font-semibold leading-7 text-[#0F172A] sm:text-[17px]">
                                        <span class="sr-only">Statement <?= $n ?> of <?= count($questions) ?>: </span><?= $e($q['text']) ?>
                                    </span>
                                </legend>

                                <div class="mt-5 grid grid-cols-1 gap-2.5 sm:grid-cols-5">
                                    <?php foreach ($ratings as $score => [$emoji, $label]): ?>
                                        <label class="opt flex min-h-[46px] cursor-pointer items-center gap-3 rounded-xl border border-[#E2E8F0] bg-white px-3 py-2 text-[#475569] sm:flex-col sm:justify-center sm:gap-2 sm:px-2 sm:py-3.5 sm:text-center" data-score="<?= $score ?>">
                                            <input type="radio" class="sr-only" name="q<?= $q['id'] ?>" value="<?= $score ?>">
                                            <span class="opt-badge flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#F1F5F9] text-[22px] leading-none" aria-hidden="true"><?= $emoji ?></span>
                                            <span class="text-sm font-medium leading-5"><?= $e($label) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </fieldset>
                        </li>
                    <?php endforeach; ?>

                    <!-- Required comment (last item) -->
                    <li class="q-card scroll-mt-28 rounded-2xl border border-[#E2E8F0] bg-white p-5 sm:p-6" data-item="<?= count($questions) ?>" data-answered="false" id="commentCard">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div class="flex min-w-0 items-start gap-3.5">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#4F46E5] text-white" aria-hidden="true">
                                    <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3h11A2.5 2.5 0 0 1 20 5.5v8a2.5 2.5 0 0 1-2.5 2.5H10l-4.5 4v-4h0A1.5 1.5 0 0 1 4 14.5z"/><path d="M8 8h8M8 11.5h5"/></svg>
                                </span>
                                <div class="min-w-0">
                                    <div class="flex flex-wrap items-center gap-2.5">
                                        <label for="commentInput" class="ff-display text-[17px] font-bold text-[#0F172A]">Additional Comment</label>
                                        <span class="ff-mono rounded-md border border-amber-200 bg-amber-50 px-2 py-0.5 text-[10px] font-semibold tracking-[0.12em] text-amber-800">REQUIRED</span>
                                    </div>
                                    <p class="mt-0.5 text-sm text-[#475569]">Share constructive feedback about this instructor's teaching strengths or areas for improvement.</p>
                                </div>
                            </div>
                            <span id="commentStatus" class="inline-flex shrink-0 items-center gap-1.5 rounded-full border border-[#E2E8F0] bg-[#F8FAFC] px-3 py-1 text-xs font-semibold text-[#64748B]">Pending</span>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center gap-2" id="starterChips">
                            <span class="mr-1 text-sm text-[#64748B]">Quick starters:</span>
                            <?php foreach ($starters as $starterLabel => $starterText): ?>
                                <button type="button" data-starter="<?= $e($starterText) ?>" aria-pressed="false"
                                    class="min-h-9 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] px-3 text-sm text-[#475569] transition-colors hover:border-[#C7D2FE] hover:text-[#4338CA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] aria-pressed:border-[#C7D2FE] aria-pressed:bg-[#EEF2FF] aria-pressed:text-[#4338CA]">
                                    + <?= $e($starterLabel) ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <textarea id="commentInput" rows="4" maxlength="500" aria-describedby="commentHelp"
                            placeholder="Write a short, respectful comment (at least 10 characters)…"
                            class="mt-4 block min-h-[136px] w-full resize-y rounded-xl border border-[#E2E8F0] bg-white px-4 py-3.5 text-[15px] leading-6 text-[#0F172A] outline-none transition placeholder:text-[#94A3B8] hover:border-[#CBD5E1] focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/20"></textarea>
                        <div class="mt-3 flex items-center justify-between gap-3 text-sm">
                            <p id="commentHelp" class="text-[#64748B]" aria-live="polite">At least 10 characters.</p>
                            <p class="ff-mono shrink-0 text-xs text-[#64748B]"><span id="commentCount">0</span> / 500</p>
                        </div>
                    </li>
                </ol>
            </section>
        </div>
        <?php endif; ?>
    </main>

    <?php if (!$loadError): ?>
    <!-- ============================================================
         FIXED ACTION BAR
    ============================================================ -->
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#E2E8F0] bg-white/95 backdrop-blur-md" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto max-w-6xl px-4 py-3.5 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <p class="flex items-center gap-2 text-sm font-semibold text-[#0F172A]">
                        <span id="barDot" class="h-2 w-2 shrink-0 rounded-full bg-amber-500" aria-hidden="true"></span>
                        <span id="barStatus" class="truncate" aria-live="polite"></span>
                    </p>
                    <ol id="barDots" class="mt-1.5 flex flex-wrap" aria-label="Items for this teacher"></ol>
                </div>
                <div class="grid grid-cols-[auto_1fr] gap-3 sm:flex">
                    <button type="button" id="prevBtn"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] transition-colors hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] disabled:cursor-not-allowed disabled:text-[#94A3B8] disabled:hover:bg-white">
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i><span class="hidden sm:inline">Previous</span><span class="sr-only sm:hidden">Previous teacher</span>
                    </button>
                    <button type="button" id="nextBtn" aria-disabled="true"
                        class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#4F46E5] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#4338CA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-2 aria-disabled:cursor-not-allowed aria-disabled:border aria-disabled:border-[#E2E8F0] aria-disabled:bg-[#F1F5F9] aria-disabled:text-[#94A3B8] sm:min-w-40">
                        <svg id="nextSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" class="opacity-75"></path></svg>
                        <span id="nextText">Loading…</span>
                        <i id="nextIcon" class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================================
         FINAL REVIEW (bottom sheet on phones, centred dialog on desktop)
    ============================================================ -->
    <div id="reviewSheet" class="fixed inset-0 z-50 hidden" data-open="false">
        <div class="sheet-backdrop absolute inset-0 bg-[#0F172A]/50" data-close-sheet></div>
        <div class="pointer-events-none absolute inset-0 flex items-end justify-center sm:items-center sm:p-6">
            <div class="sheet-panel pointer-events-auto flex max-h-[90dvh] w-full max-w-2xl flex-col rounded-t-2xl border border-[#E2E8F0] bg-white sm:rounded-2xl"
                 role="dialog" aria-modal="true" aria-labelledby="reviewTitle" aria-describedby="reviewDesc" tabindex="-1"
                 style="padding-bottom: env(safe-area-inset-bottom);">

                <div class="flex justify-center pt-3 sm:hidden" data-drag-handle aria-hidden="true">
                    <span class="h-1.5 w-10 rounded-full bg-[#CBD5E1]"></span>
                </div>

                <div class="flex items-start justify-between gap-3 px-5 pt-4 sm:pt-5" data-drag-handle>
                    <div>
                        <h2 id="reviewTitle" class="ff-display text-lg font-bold text-[#0F172A]">Review &amp; submit</h2>
                        <p id="reviewDesc" class="mt-1 text-sm text-[#475569]">Check your ratings for every instructor. After you submit, your evaluation is final and can't be edited.</p>
                    </div>
                    <button type="button" class="-mr-2 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-[#475569] hover:bg-[#F1F5F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]" data-close-sheet aria-label="Close">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div id="reviewBody" class="mt-4 min-h-0 flex-1 overflow-y-auto px-5"></div>

                <div class="flex flex-col-reverse gap-2 border-t border-[#E2E8F0] px-5 pb-5 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" class="h-12 rounded-lg border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]" data-close-sheet>Keep editing</button>
                    <button type="button" id="submitAllBtn" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#4F46E5] px-5 text-sm font-semibold text-white hover:bg-[#4338CA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-70">
                        <svg id="submitSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" class="opacity-75"></path></svg>
                        <span id="submitText">Submit all evaluations</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = <?= json_encode(BASE_URL) ?>;
        window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>;
        window.EVAL_PAGE = <?= json_encode([
            'teachers'  => $teachers,
            'questions' => $questions,
            'saved'     => (object) $saved,
            'ratings'   => array_map(static fn ($r) => ['emoji' => $r[0], 'label' => $r[1]], $ratings),
            'comment'   => ['min' => 10, 'max' => 500],
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
    </script>
    <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
    <script type="module" src="<?= BASE_URL ?>assets/js/evaluation/evaluation/index.js"></script>
    <?php endif; ?>

<?php $autoLogoutRole = 'student';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
