<?php
/**
 * Shared confirmation dialog.
 * JS (assets/js/modal/modal.js showConfirmation, and page scripts) fills
 * #modalTitle / #modalMessage and wires #confirmBtn / #cancelBtn.
 */
?>
<div id="confirmationModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalMessage">
  <div class="se-modal__panel se-modal__panel--sm">

    <header class="se-modal__header">
      <span class="se-modal__icon se-modal__icon--danger" aria-hidden="true">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="modalTitle" class="se-modal__title">Confirm Action</h2>
        <p class="se-modal__subtitle">Please review before continuing.</p>
      </div>
    </header>

    <div class="se-modal__body">
      <p id="modalMessage" class="se-modal__message">Are you sure you want to proceed?</p>
    </div>

    <footer class="se-modal__footer">
      <button id="cancelBtn" type="button" class="se-btn se-btn--secondary">Cancel</button>
      <button id="confirmBtn" type="button" class="se-btn se-btn--danger">Yes</button>
    </footer>

  </div>
</div>
