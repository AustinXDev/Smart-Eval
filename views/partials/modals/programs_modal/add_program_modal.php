<!-- Add program (manage_program/components/programForm.js submits #addProgramForm) -->
<div id="addProgramModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="addProgramTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-graduation-cap"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="addProgramTitle" class="se-modal__title">Add New Program</h2>
        <p class="se-modal__subtitle">Add an academic program to Smart-Eval.</p>
      </div>
      <button type="button" data-close-modal="addProgramModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form method="POST" id="addProgramForm" class="se-modal__form">
      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Program Information</h3>

          <div class="se-grid">
            <div>
              <label for="program_code" class="se-label">Program Code <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                <input id="program_code" type="text" name="program_code" class="se-input" placeholder="e.g., BSIT" required>
              </div>
              <p class="se-help">Short identifier of the program.</p>
            </div>

            <div>
              <label for="program_name" class="se-label">Program Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                <input id="program_name" type="text" name="program_name" class="se-input" placeholder="e.g., BS Information Technology" required>
              </div>
              <p class="se-help">Full name of the academic program.</p>
            </div>
          </div>
        </section>

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Department</h3>

          <label for="department" class="se-label">Department <span class="se-required">*</span></label>
          <div class="se-input-icon">
            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
            <select id="department" name="department" class="se-select" required>
              <option value="" selected disabled>Select department</option>
              <option value="college">College</option>
              <option value="shs">Senior High School</option>
            </select>
          </div>
          <p class="se-help">The department decides where this program appears in student management and reports.</p>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="addProgramModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
          Add Program
        </button>
      </footer>
    </form>

  </div>
</div>
