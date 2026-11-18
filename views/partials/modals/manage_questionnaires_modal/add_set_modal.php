<!-- Create question set (manage_questionnaires JS submits #createQuestionSetForm) -->
<div id="createQuestionSetModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="createQuestionSetTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-layer-group"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="createQuestionSetTitle" class="se-modal__title">Create New Question Set</h2>
        <p class="se-modal__subtitle">Create a new set for your evaluation questions.</p>
      </div>
      <button type="button" data-close-modal="createQuestionSetModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="createQuestionSetForm" class="se-modal__form">
      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Set Details</h3>

          <label for="create_set_name" class="se-label">Set Name <span class="se-required">*</span></label>
          <div class="se-input-icon">
            <i class="fa-regular fa-file-lines" aria-hidden="true"></i>
            <input type="text" name="set_name" id="create_set_name" class="se-input"
              placeholder="Enter question set name" autocomplete="off" required>
          </div>
          <p class="se-help">Give your question set a clear and recognizable name.</p>
        </section>

        <section class="se-modal__section">
          <div class="se-note se-note--warn">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <div>
              <p class="se-note__title">Before you continue</p>
              <p>Once created, you can add questions and categories to this set.</p>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="createQuestionSetModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
          <span>Create Set</span>
        </button>
      </footer>
    </form>

  </div>
</div>
