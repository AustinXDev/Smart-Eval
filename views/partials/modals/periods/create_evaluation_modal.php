<!-- Create evaluation period (evaluation_periods/components/periodForm.js submits #createPeriodForm;
     actions.js fills #questionSetSelect) -->
<div id="createPeriodModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="createPeriodTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-calendar-plus"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="createPeriodTitle" class="se-modal__title">Create Evaluation Period</h2>
        <p class="se-modal__subtitle">Configure and schedule a new evaluation cycle.</p>
      </div>
      <button type="button" data-close-modal="createPeriodModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="createPeriodForm" class="se-modal__form">
      <div class="se-modal__body">

        <!-- Academic identity -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Academic Identity</h3>

          <div class="se-stack">
            <div>
              <label for="academic_year" class="se-label">Academic Year <span class="se-required">*</span></label>
              <input type="text" id="academic_year" name="academic_year" class="se-input" placeholder="e.g. 2025-2026" required>
            </div>

            <fieldset>
              <legend class="se-label">Semester <span class="se-required">*</span></legend>
              <div class="se-choices">
                <label class="se-choice">
                  <input type="radio" name="semester" value="1st Semester" required>
                  <span class="se-choice__card">1st Semester</span>
                </label>
                <label class="se-choice">
                  <input type="radio" name="semester" value="2nd Semester">
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
              <input type="radio" name="department" value="college">
              <span class="se-choice__card">
                <span class="se-choice__icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
                <span class="se-choice__text">
                  <span class="se-choice__title">College</span>
                  <span class="se-choice__hint">Higher education programs</span>
                </span>
              </span>
            </label>
            <label class="se-choice">
              <input type="radio" name="department" value="shs">
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
              <label for="start_date" class="se-label">Start Date &amp; Time</label>
              <input type="datetime-local" id="start_date" name="start_date" class="se-input">
            </div>
            <div>
              <label for="end_date" class="se-label">End Date &amp; Time</label>
              <input type="datetime-local" id="end_date" name="end_date" class="se-input">
            </div>
          </div>
        </section>

        <!-- Question set -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Question Set</h3>

          <label for="questionSetSelect" class="se-label">Evaluation Form</label>
          <select name="question_set" id="questionSetSelect" class="se-select">
            <option disabled selected>Select Question Bank</option>
          </select>
          <p class="se-help">The question bank assigned to this evaluation period.</p>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="createPeriodModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-calendar-check text-xs" aria-hidden="true"></i>
          Create &amp; Schedule Period
        </button>
      </footer>
    </form>

  </div>
</div>
