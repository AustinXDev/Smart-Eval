<!-- CSV upload in progress (opened/closed by students/components/csvUpload.js) -->
<div id="uploadLoading" class="se-modal hidden" role="status" aria-live="polite" aria-label="Uploading CSV file">
  <div class="se-modal__panel se-modal__panel--sm">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-circle-notch fa-spin"></i>
      </span>
      <div class="se-modal__heading">
        <h2 class="se-modal__title">Uploading CSV</h2>
        <p class="se-modal__subtitle">Please wait while we import your students.</p>
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
