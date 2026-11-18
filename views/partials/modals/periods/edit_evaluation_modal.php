<!-- Update evaluation period (evaluation_periods JS fills the update_* fields and submits #updatePeriodForm) -->
<div id="updatePeriodModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="updatePeriodTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-calendar-days"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="updatePeriodTitle" class="se-modal__title">Update Evaluation Period</h2>
        <p class="se-modal__subtitle">Modify the configuration of an existing evaluation cycle.</p>
      </div>
      <button type="button" data-close-modal="updatePeriodModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="updatePeriodForm" class="se-modal__form">
      <input type="hidden" name="period_id" id="update_period_id">

      <div class="se-modal__body">

        <!-- Academic identity -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Academic Identity</h3>

          <div class="se-stack">
            <div>
              <label for="update_academic_year" class="se-label">Academic Year <span class="se-required">*</span></label>
              <input type="text" id="update_academic_year" name="update_academic_year" class="se-input" placeholder="e.g. 2025-2026" required>
            </div>

            <fieldset>
              <legend class="se-label">Semester <span class="se-required">*</span></legend>
              <div class="se-choices">
                <label class="se-choice">
                  <input type="radio" name="update_semester" value="1st Semester" required>
                  <span class="se-choice__card">1st Semester</span>
                </label>
                <label class="se-choice">
                  <input type="radio" name="update_semester" value="2nd Semester">
                  <span class="se-choice__card">2nd Semester</span>
                </label>
              </div>
            </fieldset>
          </div>
        </section>

        <!-- Department -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Department</h3>
          <p class="se-modal__section-hint">Which department will use this evaluation.</p>

          <div class="se-choices se-choices--stack">
            <label class="se-choice">
              <input type="radio" name="update_department" value="college">
              <span class="se-choice__card">
                <span class="se-choice__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                <span class="se-choice__text">
                  <span class="se-choice__title">College</span>
                  <span class="se-choice__hint">Higher education programs</span>
                </span>
              </span>
            </label>
            <label class="se-choice">
              <input type="radio" name="update_department" value="shs">
              <span class="se-choice__card">
                <span class="se-choice__icon" aria-hidden="true"><i class="fa-solid fa-building-columns"></i></span>
                <span class="se-choice__text">
                  <span class="se-choice__title">Senior High School</span>
                  <span class="se-choice__hint">SHS academic programs</span>
                </span>
              </span>
            </label>
          </div>
        </section>

        <!-- Schedule -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Evaluation Schedule</h3>
          <p class="se-modal__section-hint">The end must be later than the start.</p>

          <div class="se-grid">
            <div>
              <label for="update_start_date" class="se-label">Start Date &amp; Time</label>
              <input type="datetime-local" id="update_start_date" name="update_start_date" class="se-input">
            </div>
            <div>
              <label for="update_end_date" class="se-label">End Date &amp; Time</label>
              <input type="datetime-local" id="update_end_date" name="update_end_date" class="se-input">
            </div>
          </div>
        </section>

        <!-- Question set -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Question Set</h3>

          <label for="updateQuestionSetSelect" class="se-label">Evaluation Form</label>
          <select name="update_question_set" id="updateQuestionSetSelect" class="se-select">
            <option disabled selected>Select Question Bank</option>
          </select>
          <p class="se-help">The question bank assigned to this evaluation period.</p>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="updatePeriodModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          Save Changes
        </button>
      </footer>
    </form>

  </div>
</div>
