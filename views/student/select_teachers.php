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

/* ------------------------------------------------------------------
   Page data
------------------------------------------------------------------ */

$e = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');

$department = (string) ($student['department'] ?? '');
$deptLabel  = $department === 'shs' ? 'Senior High School' : ($department === 'college' ? 'College' : ucfirst($department));

// "3" -> "3rd Year" (college) / "Grade 11" (SHS).
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

$term      = $department !== '' ? $evaluationRepo->findActiveTerm($department) : null;
$termLabel = $term ? trim($term['semester'] . ' · A.Y. ' . $term['academic_year']) : 'Current term';

$teachers = [];
foreach ($department !== '' ? $evaluationRepo->findSelectableTeachers($department) : [] as $row) {
    $loads = [];
    foreach (array_filter(explode(',', (string) ($row['loads'] ?? ''))) as $load) {
        [$code, $year] = array_pad(explode('|', $load, 2), 2, '');
        $loads[] = ['short' => trim($code . ' ' . $year), 'long' => trim($code . ' · ' . $formatYear($year))];
    }

    $image = trim((string) ($row['image_path'] ?? ''));
    // Built from BASE_URL only (no disk check), because in production public_html and the
    // app folder are separate. A missing file falls back to initials in the browser (img onerror).
    $hasPhoto = $image !== '' && $image !== 'default.png';

    $teachers[] = [
        'id'       => (int) $row['teacher_id'],
        'employee' => (string) $row['employee_id'],
        'name'     => (string) $row['full_name'],
        'initials' => $initialsOf((string) $row['full_name']),
        'photo'    => $hasPhoto ? BASE_URL . 'uploads/teachers/' . rawurlencode(basename($image)) : null,
        'loads'    => $loads,
    ];
}
$teacherTotal = count($teachers);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Select Your Teachers - Teacher Evaluation</title>

    <?php include_once __DIR__ . '/../../public/assets/includes/head.php' ?>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@500;600&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/custom.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        /* Page fonts (custom.css sets Roboto on body). */
        body.select-page { font-family: "Inter", ui-sans-serif, system-ui, sans-serif; }
        .ff-display { font-family: "Plus Jakarta Sans", "Inter", ui-sans-serif, system-ui, sans-serif; }
        .ff-mono { font-family: "JetBrains Mono", ui-monospace, SFMono-Regular, Menlo, monospace; }

        /* Teacher cards: the selected state is driven by aria-checked. */
        .teacher-card { transition: border-color 150ms ease, background-color 150ms ease, box-shadow 150ms ease; }
        .teacher-card[aria-checked="true"] { border-color: #4f46e5; background-color: rgb(238 242 255 / 0.5); box-shadow: 0 0 0 1px #4f46e5; }
        .teacher-card .check-box { transition: background-color 150ms ease, border-color 150ms ease; }
        .teacher-card .check-box i { opacity: 0; transition: opacity 120ms ease; }
        .teacher-card[aria-checked="true"] .check-box { border-color: #4f46e5; background-color: #4f46e5; }
        .teacher-card[aria-checked="true"] .check-box i { opacity: 1; }
        .teacher-card .state-on { display: none; }
        .teacher-card[aria-checked="true"] .state-on { display: inline-flex; }
        .teacher-card[aria-checked="true"] .state-off { display: none; }
        .teacher-card[aria-checked="true"] .state-pill { border-color: #c7d2fe; background-color: #4f46e5; color: #fff; }

        /* Continue button "ready" pulse (once, when it becomes enabled). */
        @keyframes ready-pulse {
            0%   { box-shadow: 0 0 0 0 rgb(79 70 229 / 0.45); }
            100% { box-shadow: 0 0 0 10px rgb(79 70 229 / 0); }
        }
        .is-ready-pulse { animation: ready-pulse 900ms ease-out 2; }

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

        /* Hide the browser's own clear button; the page has its own. */
        #teacherSearch::-webkit-search-cancel-button { display: none; }

        @media (prefers-reduced-motion: reduce) {
            .is-ready-pulse, .dot-ping { animation: none; }
            .sheet-backdrop, .sheet-panel, .teacher-card { transition: none; }
        }
    </style>
</head>

<body class="select-page min-h-dvh bg-[#F8FAFC] text-[#0F172A] antialiased">

    <!-- ============================================================
         HEADER: portal, term, 2-step progress
    ============================================================ -->
    <header class="sticky top-0 z-30 border-b border-[#E2E8F0] bg-white/95 backdrop-blur-md">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-x-6 gap-y-3 px-4 py-3 sm:px-6">
            <div class="flex min-w-0 items-center gap-3">
                <img src="<?= BASE_URL ?>assets/images/aite-logo.webp" alt="" class="h-9 w-9 shrink-0 rounded-lg border border-[#E2E8F0] bg-white object-contain p-0.5">
                <div class="min-w-0">
                    <p class="ff-display truncate text-sm font-bold leading-5 text-[#0F172A]">Smart-Eval Student Portal</p>
                    <p class="truncate text-xs text-[#475569]"><?= $e($termLabel) ?></p>
                </div>
            </div>

            <ol class="flex w-full items-center gap-2 text-xs sm:w-auto" aria-label="Progress">
                <li class="flex items-center gap-2">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#EEF2FF] text-[11px] text-[#4F46E5] ring-1 ring-[#C7D2FE]">
                        <i class="fa-solid fa-check" aria-hidden="true"></i>
                        <span class="sr-only">Completed:</span>
                    </span>
                    <span class="text-[#475569]">Irregular Student</span>
                </li>
                <li aria-hidden="true" class="h-px w-6 shrink-0 bg-[#C7D2FE]"></li>
                <li class="flex items-center gap-2" aria-current="step">
                    <span class="ff-mono flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-[#4F46E5] text-[11px] font-semibold text-white">2</span>
                    <span class="font-semibold text-[#0F172A]">Select Your Teachers</span>
                </li>
            </ol>
        </div>
    </header>

    <!-- Bottom padding keeps the last cards clear of the fixed action bar. -->
    <main class="mx-auto max-w-6xl px-4 pb-56 pt-8 sm:px-6 sm:pb-40 sm:pt-10">

        <!-- ============================================================
             PAGE HEADER + COR CALLOUT
        ============================================================ -->
        <section class="max-w-3xl">
            <p class="ff-mono text-[11px] font-semibold uppercase tracking-[0.14em] text-[#4F46E5]">Step 2 of 2</p>
            <div class="mt-2 flex flex-wrap items-center gap-3">
                <h1 class="ff-display text-2xl font-extrabold tracking-tight text-[#0F172A] sm:text-3xl">Select Your Teachers</h1>
                <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-800">
                    <i class="fa-solid fa-shuffle text-[10px] text-[#D97706]" aria-hidden="true"></i> Irregular Student
                </span>
            </div>
            <p class="mt-3 text-sm leading-6 text-[#475569] sm:text-base">
                Tick every instructor who teaches you this semester. You can select as many as you need. Each one you
                select will be on your evaluation list.
            </p>
        </section>

        <div class="mt-6 flex gap-3 rounded-xl border border-[#C7D2FE] bg-[#EEF2FF] px-4 py-3.5 text-sm leading-6 text-[#3730A3]" role="note">
            <i class="fa-regular fa-file-lines mt-1 text-[#4F46E5]" aria-hidden="true"></i>
            <p>
                <strong class="font-semibold">Have your COR ready.</strong>
                Select <em class="not-italic font-semibold">all</em> the instructors listed on your Certificate of Registration
                (COR). Anyone you leave out won't be on your evaluation list.
            </p>
        </div>

        <!-- ============================================================
             SEARCH + TOTAL FACULTY
        ============================================================ -->
        <div class="mt-6 flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <label for="teacherSearch" class="sr-only">Search instructors by name, employee ID or program</label>
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs text-[#64748B]" aria-hidden="true"></i>
                <input id="teacherSearch" type="search" autocomplete="off" maxlength="100" enterkeyhint="search" disabled
                    placeholder="Search name, employee ID or program…"
                    aria-describedby="searchHint"
                    class="h-12 w-full rounded-lg border border-[#E2E8F0] bg-white pl-10 pr-20 text-sm text-[#0F172A] outline-none transition placeholder:text-[#94A3B8] hover:border-[#CBD5E1] focus:border-[#4F46E5] focus:ring-2 focus:ring-[#4F46E5]/20">
                <button type="button" id="clearSearch" class="absolute right-2 top-1/2 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-md text-[#64748B] hover:bg-[#F1F5F9] hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]" aria-label="Clear search">
                    <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                </button>
                <kbd id="searchKbd" class="ff-mono pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-[#E2E8F0] bg-[#F8FAFC] px-1.5 py-0.5 text-[11px] text-[#64748B] lg:block" aria-hidden="true">/</kbd>
                <span id="searchHint" class="sr-only">Press slash to jump to search.</span>
            </div>

            <span id="facultyBadge" class="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-lg border border-[#E2E8F0] bg-white px-4 text-sm text-[#475569]" aria-live="polite">
                <i class="fa-solid fa-chalkboard-user text-xs text-[#4F46E5]" aria-hidden="true"></i>
                <span><span id="facultyCount" class="ff-mono font-semibold text-[#0F172A]"><?= $teacherTotal ?></span> <span id="facultyLabel">Total Faculty</span></span>
            </span>
        </div>

        <!-- ============================================================
             ROSTER
        ============================================================ -->
        <section class="mt-4 rounded-xl border border-[#E2E8F0] bg-white" aria-labelledby="rosterTitle">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#E2E8F0] px-4 py-3 sm:px-5">
                <div class="flex items-center gap-3">
                    <h2 id="rosterTitle" class="ff-display text-base font-bold text-[#0F172A]">Available Teachers</h2>
                    <span class="hidden text-xs text-[#64748B] lg:inline">
                        Press <kbd class="ff-mono rounded border border-[#E2E8F0] bg-white px-1.5 py-0.5 text-[11px]">1</kbd>–<kbd class="ff-mono rounded border border-[#E2E8F0] bg-white px-1.5 py-0.5 text-[11px]">9</kbd> to toggle
                    </span>
                </div>
                <?php if ($teacherTotal > 0): ?>
                    <button type="button" id="toggleAllBtn" disabled
                        class="min-h-11 rounded-lg border border-[#E2E8F0] bg-white px-3.5 text-sm font-semibold text-[#4F46E5] transition-colors hover:border-[#C7D2FE] hover:bg-[#EEF2FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] disabled:cursor-not-allowed disabled:opacity-50">
                        Select all
                    </button>
                <?php endif; ?>
            </div>

            <!-- Selected chips (shown when 1+ selected) -->
            <div id="chipStrip" class="hidden border-b border-[#C7D2FE] bg-[#EEF2FF] px-4 py-3 sm:px-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[#4338CA]">
                        Selected <span id="chipCount" class="ff-mono">0</span>
                    </p>
                    <button type="button" id="chipClearAll" class="min-h-9 rounded px-1 text-xs font-semibold text-[#4338CA] underline underline-offset-2 hover:text-[#312E81] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">Clear all</button>
                </div>
                <ul id="chipList" class="mt-2 flex flex-wrap gap-2" aria-label="Selected teachers"></ul>
            </div>

            <?php if ($teacherTotal === 0): ?>
                <div class="px-5 py-14 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[#F1F5F9] text-[#64748B]" aria-hidden="true">
                        <i class="fa-solid fa-user-slash"></i>
                    </span>
                    <p class="ff-display mt-3 text-sm font-bold text-[#0F172A]">No instructors available yet</p>
                    <p class="mx-auto mt-1 max-w-sm text-sm text-[#64748B]">No active <?= $e($deptLabel) ?> instructors are set up. Please contact your administrator or dean's office.</p>
                </div>
            <?php else: ?>
                <!-- Loading: shown until the page script is ready (slow connections).
                     The real cards below stay hidden until then, so taps aren't lost. -->
                <div id="rosterLoading" role="status" aria-live="polite" class="p-4 sm:p-5">
                    <p class="flex items-center gap-2 text-sm text-[#475569]">
                        <svg class="h-4 w-4 animate-spin text-[#4F46E5] motion-reduce:animate-none" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" class="opacity-25"></circle><path d="M4 12a8 8 0 018-8v8H4z" fill="currentColor" class="opacity-75"></path></svg>
                        Loading teacher list…
                    </p>
                    <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3" aria-hidden="true">
                        <?php for ($i = 0; $i < min(max($teacherTotal, 1), 3); $i++): ?>
                            <div class="animate-pulse rounded-xl border border-[#E2E8F0] p-4 motion-reduce:animate-none">
                                <div class="flex justify-between"><span class="h-5 w-14 rounded-md bg-[#EEF2FF]"></span><span class="h-5 w-5 rounded border-2 border-[#E2E8F0]"></span></div>
                                <div class="mt-4 flex items-center gap-3">
                                    <span class="h-11 w-11 rounded-lg bg-[#F1F5F9]"></span>
                                    <div class="flex-1 space-y-2"><span class="block h-3 w-3/4 rounded bg-[#E2E8F0]"></span><span class="block h-2.5 w-1/2 rounded bg-[#F1F5F9]"></span></div>
                                </div>
                                <span class="mt-4 block h-2.5 w-2/3 rounded bg-[#F1F5F9]"></span>
                                <span class="mt-4 block h-10 rounded-lg bg-[#F8FAFC]"></span>
                            </div>
                        <?php endfor; ?>
                    </div>
                    <div id="rosterSlow" class="mt-4 hidden flex-col gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 sm:flex-row sm:items-center sm:justify-between">
                        <p class="flex gap-2"><i class="fa-solid fa-signal mt-1 text-xs" aria-hidden="true"></i><span>This is taking longer than usual. Your connection may be slow.</span></p>
                        <button type="button" onclick="location.reload()" class="min-h-11 shrink-0 rounded-lg border border-amber-300 bg-white px-4 font-semibold text-amber-900 hover:bg-amber-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500">Reload page</button>
                    </div>
                </div>
                <script>
                    // Plain script (not the module) so it still runs if the module is slow or fails to load.
                    setTimeout(function () {
                        var box = document.getElementById('rosterLoading');
                        var slow = document.getElementById('rosterSlow');
                        if (box && !box.hidden && slow) { slow.classList.remove('hidden'); slow.classList.add('flex'); }
                    }, 10000);
                </script>

                <div id="roster" role="group" aria-labelledby="rosterTitle" aria-describedby="rosterHelp" hidden
                     class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-2 sm:p-5 lg:grid-cols-3">
                    <p id="rosterHelp" class="sr-only">Each instructor is a checkbox. Press Space or Enter to add or remove.</p>
                    <?php foreach ($teachers as $t):
                        $search = mb_strtolower(implode(' ', array_merge(
                            [$t['name'], $t['employee'], $deptLabel],
                            array_column($t['loads'], 'short'),
                            array_column($t['loads'], 'long')
                        )));
                        ?>
                        <div class="teacher-card relative flex cursor-pointer flex-col rounded-xl border border-[#E2E8F0] bg-white p-4 outline-none hover:border-[#CBD5E1] focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-2"
                             role="checkbox" aria-checked="false" tabindex="0"
                             data-id="<?= $t['id'] ?>"
                             data-name="<?= $e($t['name']) ?>"
                             data-employee="<?= $e($t['employee']) ?>"
                             data-initials="<?= $e($t['initials']) ?>"
                             data-photo="<?= $e($t['photo'] ?? '') ?>"
                             data-badge="<?= $e($t['loads'][0]['short'] ?? $t['employee']) ?>"
                             data-search="<?= $e($search) ?>"
                             aria-labelledby="t<?= $t['id'] ?>-name" aria-describedby="t<?= $t['id'] ?>-meta">

                            <div class="flex items-start justify-between gap-3">
                                <div class="flex min-h-6 flex-wrap gap-1.5">
                                    <?php if ($t['loads']): ?>
                                        <?php foreach (array_slice($t['loads'], 0, 3) as $load): ?>
                                            <span class="ff-mono rounded-md border border-[#C7D2FE] bg-[#EEF2FF] px-2 py-0.5 text-[11px] font-semibold text-[#4338CA]" title="<?= $e($load['long']) ?>"><?= $e($load['short']) ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($t['loads']) > 3): ?>
                                            <span class="ff-mono rounded-md border border-[#E2E8F0] px-2 py-0.5 text-[11px] text-[#64748B]">+<?= count($t['loads']) - 3 ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="rounded-md border border-[#E2E8F0] px-2 py-0.5 text-[11px] text-[#64748B]">No class load listed</span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="ff-mono hidden text-[11px] text-[#94A3B8] lg:inline" data-shortcut aria-hidden="true"></span>
                                    <span class="check-box flex h-5 w-5 items-center justify-center rounded border-2 border-[#CBD5E1] bg-white text-[10px] text-white" aria-hidden="true">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center gap-3">
                                <?php if ($t['photo']): ?>
                                    <img src="<?= $e($t['photo']) ?>" alt="" loading="lazy" class="h-11 w-11 shrink-0 rounded-lg border border-[#E2E8F0] object-cover"
                                         onerror="this.onerror=null;this.closest('[data-photo]').dataset.photo='';this.nextElementSibling.hidden=false;this.remove();">
                                <?php endif; ?>
                                <span class="ff-display flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-[#EEF2FF] text-sm font-bold text-[#4338CA]" aria-hidden="true"<?= $t['photo'] ? ' hidden' : '' ?>><?= $e($t['initials']) ?></span>
                                <div class="min-w-0">
                                    <p id="t<?= $t['id'] ?>-name" class="ff-display truncate text-[15px] font-bold text-[#0F172A]"><?= $e($t['name']) ?></p>
                                    <p id="t<?= $t['id'] ?>-meta" class="truncate text-xs text-[#64748B]">
                                        <?= $e($deptLabel) ?> · <span class="ff-mono"><?= $e($t['employee']) ?></span>
                                    </p>
                                </div>
                            </div>

                            <p class="mt-3 line-clamp-2 text-xs leading-5 text-[#475569]">
                                <?php if ($t['loads']): ?>
                                    Handles <?= $e(implode(', ', array_column($t['loads'], 'long'))) ?>
                                <?php else: ?>
                                    No program or year assigned yet.
                                <?php endif; ?>
                            </p>

                            <div class="mt-auto pt-4">
                                <span class="state-pill flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-[#E2E8F0] bg-[#F8FAFC] text-sm font-semibold text-[#4F46E5]">
                                    <span class="state-off items-center gap-2"><i class="fa-solid fa-plus text-xs" aria-hidden="true"></i> Add Teacher</span>
                                    <span class="state-on items-center gap-2"><i class="fa-solid fa-check text-xs" aria-hidden="true"></i> Added to List</span>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div id="noResults" class="hidden px-5 py-14 text-center" role="status">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-[#F1F5F9] text-[#64748B]" aria-hidden="true">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <p class="ff-display mt-3 text-sm font-bold text-[#0F172A]">No instructors match "<span id="noResultsQuery"></span>"</p>
                    <p class="mt-1 text-sm text-[#64748B]">Check the spelling on your COR, or search by employee ID.</p>
                    <button type="button" id="noResultsClear" class="mt-4 inline-flex min-h-11 items-center gap-2 rounded-lg border border-[#E2E8F0] bg-white px-4 text-sm font-semibold text-[#0F172A] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">
                        <i class="fa-solid fa-xmark text-xs" aria-hidden="true"></i> Clear search
                    </button>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <!-- ============================================================
         FIXED ACTION BAR
    ============================================================ -->
    <div class="fixed inset-x-0 bottom-0 z-40 border-t border-[#E2E8F0] bg-white/95 backdrop-blur-md" style="padding-bottom: env(safe-area-inset-bottom);">
        <div class="mx-auto flex max-w-6xl flex-col gap-3 px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="min-w-0">
                <div class="flex items-center gap-2 text-sm">
                    <span class="relative flex h-2.5 w-2.5 shrink-0" aria-hidden="true">
                        <span id="statusPing" class="absolute inline-flex h-full w-full rounded-full opacity-60"></span>
                        <span id="statusDot" class="relative inline-flex h-2.5 w-2.5 rounded-full bg-[#CBD5E1]"></span>
                    </span>
                    <span id="statusLabel" class="truncate font-semibold text-[#0F172A]" aria-live="polite">0 teachers selected</span>
                    <button type="button" id="clearBtn" class="hidden min-h-11 rounded px-1 text-xs font-medium text-[#475569] underline underline-offset-2 hover:text-[#0F172A] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">Clear all</button>
                </div>
                <p class="mt-0.5 text-xs text-[#64748B]">Nothing is saved until you confirm on the summary screen.</p>
            </div>

            <button type="button" id="continueBtn" disabled
                class="inline-flex h-12 w-full shrink-0 items-center justify-center gap-2 rounded-lg bg-[#4F46E5] px-6 text-sm font-semibold text-white transition-colors hover:bg-[#4338CA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:bg-[#E2E8F0] disabled:text-[#94A3B8] sm:w-auto sm:min-w-64">
                <span id="continueText">Select teachers to continue</span>
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
            <div class="sheet-panel pointer-events-auto flex max-h-[88dvh] w-full max-w-lg flex-col rounded-t-2xl border border-[#E2E8F0] bg-white sm:rounded-2xl"
                 role="dialog" aria-modal="true" aria-labelledby="sheetTitle" aria-describedby="sheetDesc" tabindex="-1"
                 style="padding-bottom: env(safe-area-inset-bottom);">

                <div class="flex justify-center pt-3 sm:hidden" aria-hidden="true">
                    <span class="h-1.5 w-10 rounded-full bg-[#CBD5E1]"></span>
                </div>

                <div class="flex items-start justify-between gap-3 px-5 pt-4 sm:pt-5">
                    <div>
                        <h2 id="sheetTitle" class="ff-display text-lg font-bold text-[#0F172A]">Confirm your teachers</h2>
                        <p id="sheetDesc" class="mt-1 text-sm text-[#475569]">
                            You'll evaluate these <span id="sheetCount" class="ff-mono font-semibold text-[#0F172A]">0</span> instructors.
                            This list can't be changed after you continue.
                        </p>
                    </div>
                    <button type="button" class="-mr-2 flex h-11 w-11 shrink-0 items-center justify-center rounded-lg text-[#475569] hover:bg-[#F1F5F9] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]" data-close-sheet aria-label="Close">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <ul id="sheetList" class="mx-5 mt-4 min-h-0 flex-1 divide-y divide-[#E2E8F0] overflow-y-auto rounded-lg border border-[#E2E8F0]"></ul>

                <p class="mx-5 mt-3 flex gap-2 rounded-lg border border-[#C7D2FE] bg-[#EEF2FF] px-3 py-2.5 text-xs leading-5 text-[#3730A3]">
                    <i class="fa-regular fa-file-lines mt-0.5" aria-hidden="true"></i>
                    <span>Compare this list with your COR. If an instructor is missing, go back and add them first.</span>
                </p>

                <div class="flex flex-col-reverse gap-2 px-5 pb-5 pt-4 sm:flex-row sm:justify-end">
                    <button type="button" class="h-12 rounded-lg border border-[#E2E8F0] bg-white px-5 text-sm font-semibold text-[#0F172A] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]" data-close-sheet>Go back</button>
                    <button type="button" id="confirmBtn" class="inline-flex h-12 items-center justify-center gap-2 rounded-lg bg-[#4F46E5] px-5 text-sm font-semibold text-white hover:bg-[#4338CA] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-2 disabled:cursor-wait disabled:opacity-70">
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
    </script>

    <script src="<?= BASE_URL ?>assets/js/evaluation/select_teacher/index.js" type="module"></script>
    <script src="<?= BASE_URL ?>assets/js/common/modal.js"></script>
<?php $autoLogoutRole = 'student';
require __DIR__ . '/../partials/auto_logout.php'; ?>
</body>
</html>
