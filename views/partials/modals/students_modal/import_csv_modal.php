<!-- Import students from CSV (students/components/csvUpload.js: #uploadCsvForm, #csvInput, #dropZone, #fileName) -->
<div id="uploadCSVModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="uploadCSVTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-file-csv"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="uploadCSVTitle" class="se-modal__title">Upload CSV File</h2>
        <p class="se-modal__subtitle">Import multiple students using a CSV file.</p>
      </div>
      <button type="button" data-close-modal="uploadCSVModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="uploadCsvForm" class="se-modal__form" enctype="multipart/form-data">
      <div class="se-modal__body">

        <!-- File -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">CSV File</h3>

          <div id="dropZone" class="flex flex-col items-center rounded-xl border-2 border-dashed border-slate-200 px-4 py-6 text-center transition-colors">
            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500" aria-hidden="true">
              <i class="fa-solid fa-arrow-up-from-bracket"></i>
            </span>
            <p class="mt-3 text-sm font-semibold text-slate-900">Drag and drop your CSV here</p>
            <p class="mt-0.5 text-xs text-slate-500">or choose a file. Only .csv files are accepted.</p>

            <label for="csvInput" class="se-btn se-btn--secondary mt-4">
              <i class="fa-regular fa-folder-open text-xs" aria-hidden="true"></i>
              Choose File
              <input type="file" id="csvInput" name="csv" accept=".csv" class="hidden">
            </label>

            <p id="fileName" class="mt-3 hidden max-w-full truncate rounded-lg bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"></p>
          </div>
        </section>

        <!-- Instructions -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">CSV Upload Instructions</h3>
          <p class="se-modal__section-hint">Make sure your file follows the required format.</p>

          <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            <a href="<?php echo $_ENV['APP_API']; ?>student/download_template.php" class="se-btn se-btn--secondary">
              <i class="fa-solid fa-download text-xs" aria-hidden="true"></i>
              Download Template
            </a>
            <a href="<?php echo $_ENV['APP_API']; ?>student/download_legend.php?department=<?php echo urlencode($department); ?>" class="se-btn se-btn--secondary">
              <i class="fa-solid fa-book text-xs" aria-hidden="true"></i>
              Program Guide
            </a>
          </div>

          <div class="se-note se-note--info mt-4">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>
              <p class="se-note__title">Before uploading</p>
              <p>Use the template, and enter the program number from the Program Guide. Example: <code class="rounded bg-white px-1 py-0.5 font-mono text-[11px]">1 = BSIT</code> <code class="rounded bg-white px-1 py-0.5 font-mono text-[11px]">2 = BSOA</code></p>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="uploadCSVModal" class="se-btn se-btn--secondary">Cancel</button>
        <!-- Text only: csvUpload.js swaps this text while uploading and restores it after. -->
        <button type="submit" class="se-btn se-btn--primary">Upload CSV</button>
      </footer>
    </form>

  </div>
</div>
