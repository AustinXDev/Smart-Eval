<?php $questionCategories = require __DIR__ . '/../shared/question_categories.php'; ?>
<!-- Edit question (manage_questionnaires JS fills #question_id_input, #set_input, #editQuestionText,
     #editQuestionCategory and submits #editQuestionForm) -->
<div id="editQuestionModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editQuestionTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-pen-to-square"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editQuestionTitle" class="se-modal__title">Edit Question</h2>
        <p class="se-modal__subtitle">Update the evaluation question and category.</p>
      </div>
      <button type="button" data-close-modal="editQuestionModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="editQuestionForm" class="se-modal__form">
      <input type="hidden" id="question_id_input" name="question_id">
      <input type="hidden" id="set_input" name="set_id">

      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Question Details</h3>

          <div class="se-stack">
            <div>
              <label for="editQuestionText" class="se-label">Question Text <span class="se-required">*</span></label>
              <input id="editQuestionText" type="text" name="question_text" class="se-input" placeholder="Enter the evaluation question..." required>
              <p class="se-help">Keep the question clear, concise, and appropriate for faculty evaluation.</p>
            </div>

            <div>
              <label for="editQuestionCategory" class="se-label">Category <span class="se-required">*</span></label>
              <select id="editQuestionCategory" name="category" class="se-select" required>
                <option value="" disabled>Select Category</option>
                <?php foreach ($questionCategories as $category): ?>
                  <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
              <p class="se-help">Choose the category that best represents this question.</p>
            </div>
          </div>
        </section>

        <section class="se-modal__section">
          <div class="se-note se-note--info">
            <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
            <p>Changes update this question within the selected set.</p>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editQuestionModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          Update Question
        </button>
      </footer>
    </form>

  </div>
</div>
