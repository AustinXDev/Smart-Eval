<!-- Edit question set (manage_questionnaires JS fills #edit_set_id / #set_name_input) -->
<div id="editQuestionSetModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editQuestionSetTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-layer-group"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editQuestionSetTitle" class="se-modal__title">Edit Question Set</h2>
        <p class="se-modal__subtitle">Update the name of this question set.</p>
      </div>
      <button type="button" data-close-modal="editQuestionSetModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="editQuestionSetForm" class="se-modal__form">
      <input type="hidden" id="edit_set_id" name="set_id">

      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Set Details</h3>

          <label for="set_name_input" class="se-label">Set Name <span class="se-required">*</span></label>
          <div class="se-input-icon">
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            <input type="text" name="set_name" id="set_name_input" class="se-input" autocomplete="off" required>
          </div>
          <p class="se-help">Choose a clear and recognizable name for this question set.</p>
        </section>

        <section class="se-modal__section">
          <div class="se-note se-note--warn">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>
              <p class="se-note__title">Important</p>
              <p>Renaming the set does not affect its questions or any existing evaluation results.</p>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editQuestionSetModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          <span>Save Changes</span>
        </button>
      </footer>
    </form>

  </div>
</div>
