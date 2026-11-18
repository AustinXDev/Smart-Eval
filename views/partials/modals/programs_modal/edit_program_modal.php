<!-- Edit program (manage_program/index.js fills the fields; programForm.js submits #editProgramForm) -->
<div id="editProgramModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editProgramTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-pen-to-square"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editProgramTitle" class="se-modal__title">Edit Program</h2>
        <p class="se-modal__subtitle">Update the academic program information.</p>
      </div>
      <button type="button" data-close-modal="editProgramModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="editProgramForm" class="se-modal__form">
      <input type="hidden" name="program_id" id="edit_program_id">

      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Program Information</h3>

          <div class="se-grid">
            <div>
              <label for="edit_program_code" class="se-label">Program Code <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-hashtag" aria-hidden="true"></i>
                <input type="text" name="program_code" id="edit_program_code" class="se-input" required>
              </div>
              <p class="se-help">Short identifier of the program.</p>
            </div>

            <div>
              <label for="edit_program_name" class="se-label">Program Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-book-open" aria-hidden="true"></i>
                <input type="text" name="program_name" id="edit_program_name" class="se-input" required>
              </div>
              <p class="se-help">Full name of the academic program.</p>
            </div>
          </div>
        </section>

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Department</h3>

          <label for="edit_department" class="se-label">Department <span class="se-required">*</span></label>
          <div class="se-input-icon">
            <i class="fa-solid fa-building-columns" aria-hidden="true"></i>
            <select name="department" id="edit_department" class="se-select" required>
              <option value="" disabled>Select department</option>
              <option value="college">College</option>
              <option value="shs">Senior High School</option>
            </select>
          </div>
          <p class="se-help">Changing the department may affect where this program appears in student management and reports.</p>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editProgramModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          Save Changes
        </button>
      </footer>
    </form>

  </div>
</div>
