<?php $questionCategories = require __DIR__ . '/../shared/question_categories.php'; ?>
<!-- Manage questions of a set (manage_questionnaires JS: #addQuestionForm, #set_id_input,
     questionRender.js fills #questionList inside #questionTable) -->
<div id="manageQuestionsModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="manageQuestionsTitle">
  <div class="se-modal__panel se-modal__panel--xl">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-list-check"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="manageQuestionsTitle" class="se-modal__title">Manage Questions</h2>
        <p class="se-modal__subtitle">Add and edit the questions in this evaluation set.</p>
      </div>
      <button type="button" data-close-modal="manageQuestionsModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">

      <!-- Add question -->
      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Add Question</h3>

        <form id="addQuestionForm">
          <input type="hidden" name="set_id" id="set_id_input">

          <div class="grid grid-cols-1 items-end gap-3 md:grid-cols-[minmax(0,2fr)_minmax(0,1fr)_auto]">
            <div>
              <label for="question_name" class="se-label">Question Text <span class="se-required">*</span></label>
              <input type="text" id="question_name" name="question_name" class="se-input" placeholder="Enter evaluation question..." required>
            </div>

            <div>
              <label for="question_category" class="se-label">Category <span class="se-required">*</span></label>
              <select id="question_category" name="categories" class="se-select" required>
                <option value="" disabled selected>Select Category</option>
                <?php foreach ($questionCategories as $category): ?>
                  <option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <button type="submit" class="se-btn se-btn--primary">
              <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
              Add Question
            </button>
          </div>
        </form>
      </section>

      <!-- Question list -->
      <section class="se-modal__section">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
          <h3 class="se-modal__section-title" style="margin:0">Question List</h3>
          <span id="questionCount" class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600">
            <span id="questionCountNumber">0</span> Questions
          </span>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200">
          <div class="max-h-[50vh] overflow-auto">
            <table id="questionTable" class="w-full min-w-[560px] text-left text-sm">
              <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                  <th class="px-4 py-2.5 font-semibold">Question</th>
                  <th class="px-4 py-2.5 font-semibold">Category</th>
                  <th class="px-4 py-2.5 text-right font-semibold">Actions</th>
                </tr>
              </thead>
              <tbody id="questionList" class="divide-y divide-slate-100 bg-white">
                <!-- JS fills this -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </div>

    <footer class="se-modal__footer">
      <p class="se-push-left text-xs text-slate-500">Changes are saved as you add or edit questions.</p>
      <button type="button" data-close-modal="manageQuestionsModal" class="se-btn se-btn--secondary">Done</button>
    </footer>

  </div>
</div>
