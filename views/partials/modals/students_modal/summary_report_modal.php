<!-- CSV upload summary (students/components/csvUpload.js fills #csvSummaryContent) -->
<div id="csvSummaryModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="csvSummaryTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-chart-pie"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="csvSummaryTitle" class="se-modal__title">CSV Upload Summary</h2>
        <p class="se-modal__subtitle">Import results and processing details.</p>
      </div>
      <button type="button" data-close-modal="csvSummaryModal" class="se-modal__close" aria-label="Close summary">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">
      <section class="se-modal__section">
        <div id="csvSummaryContent" class="text-left text-sm text-slate-800">
          <!-- JS: total, successful, failed, errors -->
        </div>
      </section>

      <section class="se-modal__section">
        <div class="se-note se-note--info">
          <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
          <div>
            <p class="se-note__title">Import completed</p>
            <p>Review the results above to check that every student record was processed correctly.</p>
          </div>
        </div>
      </section>
    </div>

    <footer class="se-modal__footer">
      <button type="button" data-close-modal="csvSummaryModal" class="se-btn se-btn--primary">
        <i class="fa-solid fa-check text-xs" aria-hidden="true"></i>
        Done
      </button>
    </footer>

  </div>
</div>
