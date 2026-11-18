<!-- Add student (students/components/studentForm.js submits #addStudentForm; programSelect.js fills #programSelect) -->
<div id="addStudentModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="addStudentTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-plus"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="addStudentTitle" class="se-modal__title">Add New Student</h2>
        <p class="se-modal__subtitle">Add a student to the evaluation system.</p>
      </div>
      <button type="button" data-close-modal="addStudentModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="addStudentForm" class="se-modal__form" method="POST">
      <div class="se-modal__body">

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Basic Information</h3>

          <div class="se-grid">
            <div>
              <label for="student_id" class="se-label">Student ID <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-id-card" aria-hidden="true"></i>
                <input id="student_id" type="text" name="student_id" class="se-input" placeholder="e.g., 00-0000" required>
              </div>
            </div>

            <div>
              <label for="full_name" class="se-label">Full Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <input id="full_name" type="text" name="full_name" class="se-input" placeholder="First name, Middle initial, Last name" required>
              </div>
            </div>

            <div class="se-span-2">
              <label for="email" class="se-label">Email Address <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <input id="email" type="email" name="email" class="se-input" placeholder="example@gmail.com" required>
              </div>
            </div>
          </div>
        </section>

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Academic Placement</h3>

          <div class="se-grid">
            <div>
              <label for="year" class="se-label">
                <?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>
                <span class="se-required">*</span>
              </label>
              <select id="year" name="year" class="se-select" required>
                <?php if ($department === 'college') { ?>
                  <option value="" selected disabled hidden>Select Year Level</option>
                  <option value="1">1st Year</option>
                  <option value="2">2nd Year</option>
                  <option value="3">3rd Year</option>
                  <option value="4">4th Year</option>
                <?php } else { ?>
                  <option value="" disabled selected hidden>Select Grade Level</option>
                  <option value="11">Grade 11</option>
                  <option value="12">Grade 12</option>
                <?php } ?>
              </select>
            </div>

            <div>
              <label for="programSelect" class="se-label">
                <?php echo ($department === 'college') ? 'Course' : 'Program'; ?>
                <span class="se-required">*</span>
              </label>
              <select name="program" id="programSelect" class="se-select" required>
                <!-- JS fill -->
              </select>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="addStudentModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-user-plus text-xs" aria-hidden="true"></i>
          <span>Add Student</span>
        </button>
      </footer>
    </form>

  </div>
</div>
