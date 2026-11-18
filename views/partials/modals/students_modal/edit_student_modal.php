<!-- Edit student (students/components/studentRender.js renderEditModal fills it; studentForm.js submits #editStudentForm) -->
<div id="editStudentModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editStudentTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-pen"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editStudentTitle" class="se-modal__title">Edit Student Information</h2>
        <p class="se-modal__subtitle">Update the student account and placement.</p>
      </div>
      <button type="button" data-close-modal="editStudentModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form method="POST" id="editStudentForm" class="se-modal__form">
      <input type="hidden" name="old_student_id" id="oldStudentIdInput">

      <div class="se-modal__body">

        <!-- Who is being edited -->
        <section class="se-modal__section">
          <div class="flex items-center gap-3">
            <span id="editStudentAvatar" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#16213E] text-sm font-semibold text-white">JD</span>
            <div class="min-w-0 flex-1">
              <p id="editStudentName" class="truncate text-sm font-semibold text-slate-900">Juan Dela Cruz</p>
              <p id="editStudentId" class="text-xs text-slate-500"></p>
            </div>
            <span id="editStudentStatus" class="shrink-0">Active</span>
          </div>
        </section>

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Student Information</h3>

          <div class="se-grid">
            <div>
              <label for="editStudentIDInput" class="se-label">Student ID <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                <input id="editStudentIDInput" type="text" name="student_id" class="se-input" required>
              </div>
            </div>

            <div>
              <label for="editStudentYearLevel" class="se-label">
                <?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>
                <span class="se-required">*</span>
              </label>
              <select name="year_level" id="editStudentYearLevel" class="se-select" required>
                <?php if ($department === 'college') { ?>
                  <option value="" selected>Select Year Level</option>
                  <option value="1">1st Year</option>
                  <option value="2">2nd Year</option>
                  <option value="3">3rd Year</option>
                  <option value="4">4th Year</option>
                <?php } else { ?>
                  <option value="" disabled selected>Select Grade Level</option>
                  <option value="11">Grade 11</option>
                  <option value="12">Grade 12</option>
                <?php } ?>
              </select>
            </div>

            <div>
              <label for="editStudentNameInput" class="se-label">Full Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <input id="editStudentNameInput" type="text" name="full_name" class="se-input" required>
              </div>
            </div>

            <div>
              <label for="editStudentEmailInput" class="se-label">Email Address <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <input id="editStudentEmailInput" type="email" name="email" class="se-input" required>
              </div>
            </div>

            <div class="se-span-2">
              <label for="editProgramSelect" class="se-label">
                <?php echo ($department === 'college') ? 'Course' : 'Program'; ?>
                <span class="se-required">*</span>
              </label>
              <select id="editProgramSelect" name="program_id" class="se-select" required>
                <!-- JS fill -->
              </select>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editStudentModal" class="se-btn se-btn--secondary">Cancel</button>
        <button id="saveBtn" type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          <span>Save Changes</span>
        </button>
      </footer>
    </form>

  </div>
</div>
