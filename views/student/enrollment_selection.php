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

$studentMiddleware->handleEnrollmentRedirect($student, basename($requestPath));

/* ------------------------------------------------------------------
   Page data
------------------------------------------------------------------ */

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$department  = (string) ($student['department'] ?? '');
$yearLevel   = (string) ($student['year_level'] ?? '');
$programId   = (int) ($student['program_id'] ?? 0);
$programCode = trim((string) ($student['program_code'] ?? '')) ?: (string) ($student['program_name'] ?? '');

// "3" -> "3rd Year" (college) / "Grade 11" (SHS).
$yearLabel = $yearLevel;
if (ctype_digit($yearLevel)) {
    $n = (int) $yearLevel;
    if ($department === 'shs') {
        $yearLabel = 'Grade ' . $n;
    } else {
        $suffix = ($n % 100 >= 11 && $n % 100 <= 13) ? 'th' : (['th', 'st', 'nd', 'rd'][$n % 10] ?? 'th');
        $yearLabel = $n . $suffix . ' Year';
    }
}

$blockLabel = trim($programCode . ' ' . $yearLevel);

$assignedTeachers = $evaluationRepo->findAssignedTeachers($programId, $yearLevel);
$teacherCount     = count($assignedTeachers);
$hasRoster        = $teacherCount > 0;

$term      = $department !== '' ? $evaluationRepo->findActiveTerm($department) : null;
$termLabel = $term ? trim($term['semester'] . ' · A.Y. ' . $term['academic_year']) : 'Current term';

$initials = implode('', array_map(
    static fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)),
    array_slice(preg_split('/\s+/', trim((string) ($student['full_name'] ?? ''))) ?: [], 0, 2)
)) ?: '?';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Enrollment Setup - Teacher Evaluation</title>

    <?php include_once __DIR__ . '/../../public/assets/includes/head.php' ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Page fonts (custom.css sets Roboto on body). */
        body.enroll-page { 
            font-family: "Inter", ui-sans-serif, system-ui, sans-serif; 
        }

        .ff-display { 
            font-family: "Plus Jakarta Sans", "Inter", ui-sans-serif, system-ui, sans-serif; 
        }

        .ff-mono { font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace; font-feature-settings: "zero" 0; }

        /* Radio cards: selected state is driven by aria-checked. */
        .type-card[aria-checked="true"][data-type="regular"] { border-color: #4f46e5; background-color: rgb(238 242 255 / 0.45); box-shadow: 0 0 0 1px #4f46e5; }
        .type-card[aria-checked="true"][data-type="irregular"] { border-color: #d97706; background-color: rgb(255 251 235 / 0.55); box-shadow: 0 0 0 1px #d97706; }
        .type-card .radio-dot { transition: border-color 150ms ease, background-color 150ms ease; }
        .type-card[aria-checked="true"][data-type="regular"] .radio-dot { border-color: #4f46e5; background: radial-gradient(circle, #4f46e5 0 4px, #fff 5px); }
        .type-card[aria-checked="true"][data-type="irregular"] .radio-dot { border-color: #d97706; background: radial-gradient(circle, #d97706 0 4px, #fff 5px); }
        .type-card[aria-disabled="true"] { cursor: not-allowed; opacity: 0.6; }

        /* Continue button "ready" pulse (once, when it becomes enabled). */
        @keyframes ready-pulse {
            0%   { box-shadow: 0 0 0 0 rgb(79 70 229 / 0.45); }
            100% { box-shadow: 0 0 0 10px rgb(79 70 229 / 0); }
        }
        .is-ready-pulse { animation: ready-pulse 900ms ease-out 2; }

        /* Status dot in the action bar. */
        @keyframes dot-ping { 75%, 100% { transform: scale(2.2); opacity: 0; } }
        .dot-ping { animation: dot-ping 1.4s cubic-bezier(0, 0, 0.2, 1) infinite; }

        /* Confirmation sheet */
        .sheet-backdrop { transition: opacity 200ms ease; }
        .sheet-panel { transition: transform 260ms cubic-bezier(0.22, 1, 0.36, 1), opacity 200ms ease; }
        #confirmSheet[data-open="false"] .sheet-backdrop { opacity: 0; }
        #confirmSheet[data-open="false"] .sheet-panel { transform: translateY(100%); }
        @media (min-width: 640px) {
            #confirmSheet[data-open="false"] .sheet-panel { transform: translateY(12px) scale(0.98); opacity: 0; }
        }

        @media (prefers-reduced-motion: reduce) {
            .is-ready-pulse, .dot-ping { animation: none; }
            .sheet-backdrop, .sheet-panel { transition: none; }
        }
    </style>
</head>

<body class="enroll-page min-h-dvh bg-[#F8FAFC] text-[#0F172A] antialiased">

    <!-- ============================================================
         HEADER: portal, term, 2-step progress
    ============================================================ -->
    <header class="sticky top-0 z-30 border-b border-[#E2E8F0] bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <img src="<?= BASE_URL ?>assets/images/aite-logo.webp" alt="" class="h-9 w-9 shrink-0 rounded-lg border border-[#E2E8F0] bg-white object-contain p-0.5">
                <div class="min-w-0">
                    <p class="ff-display truncate text-sm font-bold leading-5 text-[#0F172A]">Smart-Eval Student Portal</p>
                    <p class="truncate text-xs text-[#475569]"><?= $e($termLabel) ?></p>
                </div>
            </div>

            <ol class="flex w-full items-center gap-2 text-xs sm:w-auto" aria-label="Progress">
                <li class="flex items-center gap-2" aria-current="step">
                    <span class="ff-mono flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-indigo-600 text-[11px] font-semibold text-white">1</span>
                    <span class="font-semibold text-[#0F172A]">Confirm Enrollment Type</span>
                </li>
                <li aria-hidden="true" class="h-px w-6 shrink-0 bg-[#CBD5E1]"></li>
                <li class="flex items-center gap-2">
                    <span class="ff-mono flex h-6 w-6 shrink-0 items-center justify-center rounded-md border border-[#E2E8F0] bg-white text-[11px] font-semibold text-[#94A3B8]">2</span>
                    <span class="text-[#64748B]">Evaluate Your Instructors</span>
                </li>
            </ol>
        </div>
    </header>

    <!-- Bottom padding keeps the last section clear of the fixed action bar. -->
    <main class="mx-auto max-w-5xl px-4 pb-56 pt-8 sm:px-6 sm:pb-40 sm:pt-10">

        <!-- ============================================================
             QUESTION
        ============================================================ -->
        <section class="max-w-2xl">
            <p class="ff-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-indigo-600">Step 1 of 2</p>
            <h1 class="ff-display mt-2 text-2xl font-extrabold tracking-tight text-[#0F172A] sm:text-3xl">
                How are your subjects enrolled this semester?
            </h1>
            <p class="mt-3 text-sm leading-6 text-[#475569] sm:text-base">
                Your answer decides which instructors appear on your evaluation list. Pick the one that matches
                your current Enrollment Type.
            </p>
        </section>

        <div class="mt-8 grid grid-cols-1 gap-4 lg:grid-cols-5">

            <!-- ========================================================
                 STUDENT RECORD
            ========================================================= -->
            <section class="rounded-xl border border-[#E2E8F0] bg-white lg:col-span-3" aria-labelledby="recordTitle">
                <div class="flex items-center justify-between gap-3 border-b border-[#E2E8F0] px-5 py-3">
                    <h2 id="recordTitle" class="ff-display text-sm font-bold text-[#0F172A]">Your student record</h2>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700">
                        <i class="fa-solid fa-circle-check text-[11px]" aria-hidden="true"></i> Verified
                    </span>
                </div>

                <div class="flex items-center gap-4 px-5 pt-4">
                    <span class="ff-display flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#0F172A] text-sm font-bold text-white" aria-hidden="true"><?= $e($initials) ?></span>
                    <div class="min-w-0">
                        <p class="ff-display truncate text-base font-bold text-[#0F172A]"><?= $e($student['full_name'] ?? '') ?></p>
                        <p class="truncate text-xs text-[#475569]"><?= $e($student['program_name'] ?? '') ?></p>
                    </div>
                </div>

                <dl class="grid grid-cols-2 gap-px overflow-hidden px-5 pb-5 pt-4 sm:grid-cols-3">
                    <div class="py-2 pr-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Student ID</dt>
                        <dd class="ff-mono mt-1 text-sm font-semibold text-[#0F172A]"><?= $e($student['student_id'] ?? '') ?></dd>
                    </div>
                    <div class="py-2 pr-3">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Year Level</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#0F172A]"><?= $e($yearLabel) ?></dd>
                    </div>
                    <div class="col-span-2 py-2 sm:col-span-1">
                        <dt class="text-[11px] font-medium uppercase tracking-wide text-[#64748B]">Assigned instructors</dt>
                        <dd class="mt-1 text-sm font-semibold text-[#0F172A]">
                            <span class="ff-mono"><?= $teacherCount ?></span>
                            <span class="font-normal text-[#475569]">for <?= $e($blockLabel) ?></span>
                        </dd>
                    </div>
                </dl>
            </section>

            <!-- ========================================================
                 HELP ME CHOOSE
            ========================================================= -->
            <section class="rounded-xl border border-[#E2E8F0] bg-white p-5 lg:col-span-2" aria-labelledby="helperTitle">
                <h2 id="helperTitle" class="ff-display flex items-center gap-2 text-sm font-bold text-[#0F172A]">
                    <i class="fa-regular fa-circle-question text-indigo-600" aria-hidden="true"></i>
                    Not sure? Answer one question
                </h2>
                <p class="mt-2 text-sm leading-6 text-[#475569]">
                    Are <strong class="font-semibold text-[#0F172A]">all</strong> your subjects this semester from the standard
                    <span class="ff-mono rounded bg-[#F1F5F9] px-1.5 py-0.5 text-xs font-semibold text-[#0F172A]"><?= $e($blockLabel) ?></span>
                    block schedule?
                </p>
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <button type="button" data-helper-answer="regular"
                        class="helper-btn min-h-11 rounded-lg border border-[#E2E8F0] bg-white px-3 text-sm font-semibold text-[#0F172A] transition-colors hover:border-indigo-300 hover:bg-indigo-50/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 aria-pressed:border-indigo-600 aria-pressed:bg-indigo-50"
                        aria-pressed="false">Yes, all of them</button>
                    <button type="button" data-helper-answer="irregular"
                        class="helper-btn min-h-11 rounded-lg border border-[#E2E8F0] bg-white px-3 text-sm font-semibold text-[#0F172A] transition-colors hover:border-amber-300 hover:bg-amber-50/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 aria-pressed:border-amber-600 aria-pressed:bg-amber-50"
                        aria-pressed="false">No, some differ</button>
                </div>
                <p id="helperMessage" class="mt-3 hidden rounded-lg border px-3 py-2.5 text-xs leading-5" role="status" aria-live="polite"></p>
            </section>
        </div>

        <!-- ============================================================
             CLASSIFICATION CARDS
        ============================================================ -->
        <section class="mt-8" aria-labelledby="typeTitle">
            <div class="flex flex-wrap items-end justify-between gap-2">
                <h2 id="typeTitle" class="ff-display text-base font-bold text-[#0F172A]">Choose your enrollment type</h2>
                <p class="hidden text-xs text-[#64748B] lg:block">
                    Shortcut: press <kbd class="ff-mono rounded border border-[#E2E8F0] bg-white px-1.5 py-0.5 text-[11px]">1</kbd>
                    or <kbd class="ff-mono rounded border border-[#E2E8F0] bg-white px-1.5 py-0.5 text-[11px]">2</kbd>
                </p>
            </div>

            <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2" role="radiogroup" aria-labelledby="typeTitle" id="typeGroup">

                <!-- Regular -->
                <div class="type-card group relative cursor-pointer rounded-xl border border-[#E2E8F0] bg-white p-5 outline-none transition-colors hover:border-[#CBD5E1] focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 sm:p-6"
                     role="radio" aria-checked="false" tabindex="0" data-type="regular" data-value="Regular"
                     <?= $hasRoster ? '' : 'aria-disabled="true"' ?>
                     aria-labelledby="regularTitle" aria-describedby="regularDesc">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-50 text-indigo-700" aria-hidden="true">
                            <i class="fa-solid fa-users-rectangle"></i>
                        </span>
                        <span class="radio-dot mt-1 h-5 w-5 shrink-0 rounded-full border-2 border-[#CBD5E1] bg-white" aria-hidden="true"></span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <h3 id="regularTitle" class="ff-display text-lg font-bold text-[#0F172A]">Regular Student</h3>
                        <span class="rounded-md bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-700">Most common</span>
                        <span class="ff-mono ml-auto hidden text-[11px] text-[#94A3B8] lg:inline" aria-hidden="true">[1]</span>
                    </div>
                    <p id="regularDesc" class="mt-2 text-sm leading-6 text-[#475569]">
                        You follow the standard block schedule for <?= $e($blockLabel) ?>, with no back subjects or advanced subjects.
                    </p>

                    <div class="mt-5 border-t border-[#E2E8F0] pt-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#64748B]">What happens next</p>
                        <ul class="mt-2.5 space-y-2 text-sm text-[#334155]">
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-indigo-600" aria-hidden="true"></i>
                                <span><span class="ff-mono font-semibold"><?= $teacherCount ?></span> instructor<?= $teacherCount === 1 ? '' : 's' ?> load automatically</span></li>
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-indigo-600" aria-hidden="true"></i><span>No searching or manual picking</span></li>
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-indigo-600" aria-hidden="true"></i><span>Ready in about <span class="ff-mono">10</span> seconds</span></li>
                        </ul>
                    </div>

                    <?php if (!$hasRoster): ?>
                        <p class="mt-4 flex gap-2 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2.5 text-xs leading-5 text-amber-900">
                            <i class="fa-solid fa-triangle-exclamation mt-0.5" aria-hidden="true"></i>
                            <span>No instructors are assigned to <?= $e($blockLabel) ?> yet, so this option is unavailable. Choose Irregular, or contact your administrator/dean office.</span>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Irregular -->
                <div class="type-card group relative cursor-pointer rounded-xl border border-[#E2E8F0] bg-white p-5 outline-none transition-colors hover:border-[#CBD5E1] focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 sm:p-6"
                     role="radio" aria-checked="false" tabindex="-1" data-type="irregular" data-value="Irregular"
                     aria-labelledby="irregularTitle" aria-describedby="irregularDesc">
                    <div class="flex items-start justify-between gap-3">
                        <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-50 text-[#D97706]" aria-hidden="true">
                            <i class="fa-solid fa-shuffle"></i>
                        </span>
                        <span class="radio-dot mt-1 h-5 w-5 shrink-0 rounded-full border-2 border-[#CBD5E1] bg-white" aria-hidden="true"></span>
                    </div>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <h3 id="irregularTitle" class="ff-display text-lg font-bold text-[#0F172A]">Irregular Student</h3>
                        <span class="rounded-md bg-amber-50 px-2 py-0.5 text-[11px] font-semibold text-amber-800">Custom schedule</span>
                        <span class="ff-mono ml-auto hidden text-[11px] text-[#94A3B8] lg:inline" aria-hidden="true">[2]</span>
                    </div>
                    <p id="irregularDesc" class="mt-2 text-sm leading-6 text-[#475569]">
                        Your subjects mix year levels or sections, for example retaken, back or advanced subjects.
                    </p>

                    <div class="mt-5 border-t border-[#E2E8F0] pt-4">
                        <p class="text-[11px] font-semibold uppercase tracking-wide text-[#64748B]">What happens next</p>
                        <ul class="mt-2.5 space-y-2 text-sm text-[#334155]">
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-[#D97706]" aria-hidden="true"></i><span>Pick your instructors using your COR</span></li>
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-[#D97706]" aria-hidden="true"></i><span>Mix instructors across sections and year levels</span></li>
                            <li class="flex gap-2.5"><i class="fa-solid fa-check mt-1 text-xs text-[#D97706]" aria-hidden="true"></i><span>Takes about <span class="ff-mono">1–2</span> minutes</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================================
             PREVIEW OF STEP 2
        ============================================================ -->
        <section class="mt-8 rounded-xl border border-[#E2E8F0] bg-white" aria-labelledby="previewTitle">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-[#E2E8F0] px-5 py-3">
                <h2 id="previewTitle" class="ff-display text-sm font-bold text-[#0F172A]">Preview of Step 2</h2>
                <span class="ff-mono rounded-md bg-[#F1F5F9] px-2 py-0.5 text-[11px] font-semibold text-[#475569]">2 / 2</span>
            </div>

            <div class="p-5" aria-live="polite">
                <!-- Nothing chosen -->
                <div data-preview="none" class="flex items-center gap-3 rounded-lg border border-dashed border-[#CBD5E1] px-4 py-6 text-sm text-[#64748B]">
                    <i class="fa-regular fa-hand-pointer" aria-hidden="true"></i>
                    Choose an enrollment type to see what your instructor list will look like.
                </div>

                <!-- Regular: the real auto-loaded roster -->
                <div data-preview="regular" hidden>
                    <p class="text-sm text-[#475569]">
                        These <span class="ff-mono font-semibold text-[#0F172A]"><?= $teacherCount ?></span>
                        instructors from <?= $e($blockLabel) ?> will be on your list automatically:
                    </p>
                    <ul class="mt-3 flex flex-wrap gap-2">
                        <?php foreach ($assignedTeachers as $teacher): ?>
                            <li class="inline-flex min-h-9 items-center gap-2 rounded-lg border border-indigo-100 bg-indigo-50/40 px-3 py-1.5 text-sm">
                                <span class="font-medium text-[#0F172A]"><?= $e($teacher['full_name']) ?></span>
                                <span class="ff-mono text-[11px] text-[#64748B]"><?= $e($teacher['employee_id']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Irregular: what the picker looks like (illustration only) -->
                <div data-preview="irregular" hidden>
                    <p class="text-sm text-[#475569]">
                        On the next screen you'll search for and tick each instructor listed on your COR. It looks like this:
                    </p>
                    <div class="mt-3 rounded-lg border border-amber-200 bg-amber-50/30 p-3" aria-hidden="true" inert>
                        <div class="flex h-10 items-center gap-2 rounded-md border border-[#E2E8F0] bg-white px-3 text-sm text-[#94A3B8]">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i> Search instructor name or ID…
                        </div>
                        <ul class="mt-2 space-y-1.5 text-sm">
                            <li class="flex min-h-10 items-center gap-3 rounded-md border border-amber-200 bg-white px-3">
                                <span class="flex h-4 w-4 items-center justify-center rounded border border-[#D97706] bg-[#D97706] text-[9px] text-white"><i class="fa-solid fa-check"></i></span>
                                <span class="h-2.5 w-36 rounded bg-[#CBD5E1]"></span>
                                <span class="ff-mono ml-auto text-[11px] text-[#94A3B8]">Selected</span>
                            </li>
                            <li class="flex min-h-10 items-center gap-3 rounded-md border border-[#E2E8F0] bg-white px-3">
                                <span class="h-4 w-4 rounded border border-[#CBD5E1]"></span>
                                <span class="h-2.5 w-28 rounded bg-[#E2E8F0]"></span>
                            </li>
                            <li class="flex min-h-10 items-center gap-3 rounded-md border border-[#E2E8F0] bg-white px-3">
                                <span class="h-4 w-4 rounded border border-[#CBD5E1]"></span>
                                <span class="h-2.5 w-44 rounded bg-[#E2E8F0]"></span>
                            </li>
                        </ul>
                    </div>
                    <p class="mt-2 text-xs text-[#64748B]">Illustration only. Your real choices are made on the next screen.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- ============================================================
         FIXED ACTION BAR
    ============================================================ -->
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#E2E8F0] bg-white/95 backdrop-blur-md" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto flex max-w-5xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-sm">
                    <span class="relative flex h-2.5 w-2.5 shrink-0" aria-hidden="true">
                        <span id="statusPing" class="absolute inline-flex h-full w-full rounded-full opacity-60"></span>
                        <span id="statusDot" class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#CBD5E1]"></span>
                    </span>
                    <span id="statusLabel" class="truncate font-semibold text-[#0F172A]" aria-live="polite">No option selected</span>
                    <button type="button" id="clearBtn" class="hidden min-h-11 rounded px-1 text-xs font-medium text-[#475569] underline underline-offset-2 hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">Clear</button>
                </div>
                <p class="mt-0.5 text-xs text-[#64748B]">Nothing is saved until you confirm on the summary screen.</p>
            </div>

            <button type="button" id="continueBtn" disabled
                class="inline-flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 text-sm font-semibold text-white transition-colors hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-[#E2E8F0] disabled:text-[#94A3B8] sm:w-auto sm:min-w-64">
                <span id="continueText">Select an option to continue</span>
                <i class="fa-solid fa-arrow-right text-xs" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <!-- ============================================================
         CONFIRMATION (bottom sheet on phones, centred dialog on desktop)
    ============================================================ -->
    <div id="confirmSheet" class="fixed inset-0 z-50 hidden" data-open="false">
        <div class="sheet-backdrop absolute inset-0 bg-[#0F172A]/50" data-close-sheet></div>

        <div class="pointer-events-none absolute inset-0 flex items-end justify-center sm:items-center sm:p-6">
            <div class="sheet-panel pointer-events-auto w-full max-w-md rounded-t-2xl border border-[#E2E8F0] bg-white sm:rounded-2xl"
                 role="dialog" aria-modal="true" aria-labelledby="sheetTitle" aria-describedby="sheetDesc" tabindex="-1"
                 style="padding-bottom: env(safe-area-inset-bottom);">

                <div class="flex justify-center pt-3 sm:hidden" aria-hidden="true">
                    <span class="h-1.5 w-10 rounded-full bg-[#CBD5E1]"></span>
                </div>

                <div class="flex items-start justify-between gap-3 px-5 pt-4 sm:pt-5">
                    <div>
                        <h2 id="sheetTitle" class="ff-display text-lg font-bold text-[#0F172A]">Confirm your enrollment type</h2>
                        <p id="sheetDesc" class="mt-1 text-sm text-[#475569]">This sets up your instructor list for this term and can't be changed afterwards.</p>
                    </div>
                    <button type="button" class="-mr-2 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-[#475569] hover:bg-[#F1F5F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-close-sheet aria-label="Close">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <dl class="mx-5 mt-4 divide-y divide-[#E2E8F0] rounded-lg border border-[#E2E8F0] text-sm">
                    <div class="flex justify-between gap-4 px-4 py-2.5"><dt class="text-[#64748B]">Name</dt><dd class="text-right font-medium text-[#0F172A]"><?= $e($student['full_name'] ?? '') ?></dd></div>
                    <div class="flex justify-between gap-4 px-4 py-2.5"><dt class="text-[#64748B]">Student ID</dt><dd class="ff-mono text-right font-semibold text-[#0F172A]"><?= $e($student['student_id'] ?? '') ?></dd></div>
                    <div class="flex justify-between gap-4 px-4 py-2.5"><dt class="text-[#64748B]">Program · Year</dt><dd class="text-right font-medium text-[#0F172A]"><?= $e($programCode) ?> · <?= $e($yearLabel) ?></dd></div>
                    <div class="flex justify-between gap-4 px-4 py-2.5"><dt class="text-[#64748B]">Enrollment type</dt><dd id="sheetType" class="text-right font-semibold text-[#0F172A]">—</dd></div>
                    <div class="flex justify-between gap-4 px-4 py-2.5"><dt class="text-[#64748B]">Instructor list</dt><dd id="sheetRoster" class="text-right font-medium text-[#0F172A]">—</dd></div>
                </dl>

                <div class="flex flex-col-reverse gap-2 px-5 pb-5 pt-5 sm:flex-row sm:justify-end">
                    <button type="button" class="h-12 rounded-lg border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500" data-close-sheet>Go back</button>
                    <button type="button" id="confirmBtn" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-5 text-sm font-semibold text-white hover:bg-indigo-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-70">
                        <svg id="confirmSpinner" class="hidden h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" class="opacity-75"></path></svg>
                        <span id="confirmText">Confirm and continue</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.BASE_URL = <?= json_encode(BASE_URL) ?>;
        window.API_URL = <?= json_encode($_ENV['APP_API'] ?? '') ?>;
        window.ENROLLMENT_PAGE = <?= json_encode([
            'teacherCount' => $teacherCount,
            'hasRoster'    => $hasRoster,
            'blockLabel'   => $blockLabel,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>

    <script src="<?= BASE_URL ?>assets/js/evaluation/select_enrollment/index.js" type="module"></script>
    <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
<?php $autoLogoutRole = 'student';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
