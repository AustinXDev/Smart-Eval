<?php
/**
 * Shared "Import from CSV" modals: upload, progress and summary.
 * Driven by assets/js/admin/shared/csv_import.js. One per page.
 *
 * Set $csvImport before including:
 *   title, subtitle      upload modal heading
 *   noun                 plural record name ("teachers", "students")
 *   id_label             label of the ID column in the summary ("Employee ID")
 *   columns              list of [key, label, rule, example]
 *   downloads            list of [label, icon, url] (template, guides)
 *   note_title, note     callout under the columns (note is trusted HTML)
 */
$ci = $csvImport;
$ciE = static fn ($v): string => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
$ciCode = 'rounded bg-slate-100 px-1.5 py-0.5 font-mono text-[11px] text-slate-700';
?>
<!-- ===== Upload ===== -->
<div id="csvImportModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="csvImportTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-file-csv"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="csvImportTitle" class="se-modal__title"><?= $ciE($ci['title']) ?></h2>
        <p class="se-modal__subtitle"><?= $ciE($ci['subtitle']) ?></p>
      </div>
      <button type="button" data-close-modal="csvImportModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="csvImportForm" class="se-modal__form" enctype="multipart/form-data" novalidate>
      <div class="se-modal__body">

        <!-- File -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">CSV File</h3>

          <div id="csvImportDrop" class="flex flex-col items-center rounded-xl border-2 border-dashed border-slate-200 px-4 py-6 text-center transition-colors">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
              <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </span>
            <p class="mt-3 text-sm font-semibold text-slate-900">Drag and drop your CSV here</p>
            <p class="mt-0.5 text-xs text-slate-500">or choose a file. Only .csv files up to 2&nbsp;MB.</p>

            <label for="csvImportInput" class="se-btn se-btn--secondary mt-4 cursor-pointer">
              <i class="fa-regular fa-folder-open text-xs" aria-hidden="true"></i>
              Choose File
              <input type="file" id="csvImportInput" name="csv" accept=".csv,text/csv" class="sr-only">
            </label>

            <div id="csvImportFile" hidden class="mt-3 flex max-w-full items-center gap-2 rounded-lg bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
              <i class="fa-solid fa-file-circle-check" aria-hidden="true"></i>
              <span id="csvImportFileName" class="truncate"></span>
              <button type="button" id="csvImportClear" class="ml-1 rounded p-0.5 text-emerald-700/70 transition hover:text-emerald-900" aria-label="Remove file">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
              </button>
            </div>
          </div>

          <p id="csvImportError" hidden class="se-field-error mt-2" role="alert"></p>
        </section>

        <!-- Format -->
        <section class="se-modal__section">
          <div class="mb-3 flex flex-wrap items-start justify-between gap-3">
            <div>
              <h3 class="se-modal__section-title">Required Columns</h3>
              <p class="se-modal__section-hint">The first row must be exactly these headers, in this order.</p>
            </div>
            <div class="flex shrink-0 flex-wrap gap-2">
              <?php foreach ($ci['downloads'] as [$label, $icon, $url]): ?>
                <a href="<?= $ciE($url) ?>" class="se-btn se-btn--secondary" download>
                  <i class="fa-solid <?= $ciE($icon) ?> text-xs" aria-hidden="true"></i>
                  <?= $ciE($label) ?>
                </a>
              <?php endforeach; ?>
            </div>
          </div>

          <ul class="divide-y divide-slate-100 overflow-hidden rounded-xl border border-slate-200">
            <?php foreach ($ci['columns'] as $i => [$key, $label, $rule, $sample]): ?>
              <li class="flex items-start gap-3 px-3.5 py-2.5">
                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-violet-100 text-[10px] font-bold text-violet-700"><?= $i + 1 ?></span>
                <div class="min-w-0 flex-1">
                  <p class="text-sm font-medium text-slate-900"><?= $ciE($label) ?> <code class="<?= $ciCode ?>"><?= $ciE($key) ?></code></p>
                  <p class="mt-0.5 text-xs text-slate-500"><?= $ciE($rule) ?> · e.g. <?= $ciE($sample) ?></p>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>

          <div class="se-note se-note--info mt-4">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>
              <p class="se-note__title"><?= $ciE($ci['note_title']) ?></p>
              <p><?= $ci['note'] ?></p>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="csvImportModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" id="csvImportSubmit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-file-import text-xs" aria-hidden="true"></i>
          <span>Import <?= $ciE(ucfirst($ci['noun'])) ?></span>
        </button>
      </footer>
    </form>

  </div>
</div>

<!-- ===== Uploading ===== -->
<div id="csvImportLoading" class="se-modal hidden" role="status" aria-live="polite" aria-label="Importing <?= $ciE($ci['noun']) ?>">
  <div class="se-modal__panel se-modal__panel--sm">
    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
      </span>
      <div class="se-modal__heading">
        <h2 class="se-modal__title">Importing <?= $ciE($ci['noun']) ?></h2>
        <p class="se-modal__subtitle">Checking each row for duplicates and saving.</p>
      </div>
    </header>
    <div class="se-modal__body">
      <div class="h-1.5 overflow-hidden rounded-full bg-slate-100" aria-hidden="true">
        <div class="h-full w-1/3 animate-pulse rounded-full bg-violet-500"></div>
      </div>
      <p class="se-help">Keep this page open until the import finishes.</p>
    </div>
  </div>
</div>

<!-- ===== Summary ===== -->
<div id="csvImportSummary" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="csvImportSummaryTitle">
  <div class="se-modal__panel se-modal__panel--lg">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-clipboard-check"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="csvImportSummaryTitle" class="se-modal__title">Import Summary</h2>
        <p id="csvImportSummaryText" class="se-modal__subtitle"></p>
      </div>
      <button type="button" data-close-modal="csvImportSummary" class="se-modal__close" aria-label="Close summary">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">
      <section class="se-modal__section">
        <div class="grid grid-cols-3 gap-2 sm:gap-3">
          <div class="rounded-xl border border-slate-200 px-3 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Rows</p>
            <p id="csvImportTotal" class="mt-1 text-2xl font-bold tabular-nums text-slate-900">0</p>
          </div>
          <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-emerald-700">Imported</p>
            <p id="csvImportSuccess" class="mt-1 text-2xl font-bold tabular-nums text-emerald-700">0</p>
          </div>
          <div class="rounded-xl border border-rose-200 bg-rose-50 px-3 py-3">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-rose-700">Skipped</p>
            <p id="csvImportFailed" class="mt-1 text-2xl font-bold tabular-nums text-rose-700">0</p>
          </div>
        </div>
        <p id="csvImportExtra" hidden class="se-help mt-2"></p>
      </section>

      <section id="csvImportErrors" hidden class="se-modal__section">
        <div class="mb-2 flex items-center justify-between gap-3">
          <h3 class="se-modal__section-title">Rows not imported</h3>
          <button type="button" id="csvImportErrorsDownload" class="se-btn se-btn--secondary">
            <i class="fa-solid fa-download text-xs" aria-hidden="true"></i>
            Download list
          </button>
        </div>
        <div class="max-h-72 overflow-auto rounded-xl border border-slate-200">
          <table class="w-full text-left text-xs">
            <thead class="sticky top-0 bg-slate-50 text-[10px] uppercase tracking-wider text-slate-500">
              <tr>
                <th class="px-3 py-2 font-semibold">Row</th>
                <th class="px-3 py-2 font-semibold"><?= $ciE($ci['id_label']) ?></th>
                <th class="px-3 py-2 font-semibold">Name</th>
                <th class="px-3 py-2 font-semibold">Reason</th>
              </tr>
            </thead>
            <tbody id="csvImportErrorRows" class="divide-y divide-slate-100"></tbody>
          </table>
        </div>
        <p class="se-help mt-2">Fix these rows in your file and import it again. Rows already imported will be reported as duplicates.</p>
      </section>
    </div>

    <footer class="se-modal__footer">
      <button type="button" data-close-modal="csvImportSummary" class="se-btn se-btn--primary">
        <i class="fa-solid fa-check text-xs" aria-hidden="true"></i>
        Done
      </button>
    </footer>

  </div>
</div>
