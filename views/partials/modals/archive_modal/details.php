<!-- Archive details (archive_management/components/detailsModal.js fills #archive-details-content) -->
<div
  id="archiveDetailsModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="archive-details-title"
  aria-describedby="archive-details-description"
  class="se-modal hidden"
>
  <div id="archive-details-panel" tabindex="-1" class="se-modal__panel se-modal__panel--lg">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-box-archive"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="archive-details-title" class="se-modal__title">Archived Evaluation Details</h2>
        <p id="archive-details-description" class="se-modal__subtitle">Review archived records and recovery information.</p>
      </div>
      <button type="button" id="close-archive-details" class="se-modal__close" aria-label="Close details">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <main id="archive-details-content" aria-live="polite" aria-busy="false" class="se-modal__body space-y-5">
      <p class="se-modal__message">Select an archived period or recovery activity to view its details.</p>
    </main>

    <footer class="se-modal__footer">
      <button type="button" id="close-archive-details-footer" class="se-btn se-btn--secondary">Close</button>
    </footer>

  </div>
</div>
