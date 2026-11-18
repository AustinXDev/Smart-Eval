<!-- View student (students/components/studentRender.js renderViewModal fills the IDs below;
     studentForm.js handles #resetPasswordBtn) -->
<div id="viewStudentModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="viewStudentTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-graduate"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="viewStudentTitle" class="se-modal__title">Student Information</h2>
        <p class="se-modal__subtitle">View profile and academic details.</p>
      </div>
      <button type="button" data-close-modal="viewStudentModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">

      <!-- Profile -->
      <section class="se-modal__section">
        <div class="flex items-center gap-3">
          <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-[#16213E] text-sm font-semibold text-white">
            <span id="studentAvatar">JD</span>
          </span>
          <div class="min-w-0 flex-1">
            <p id="studentName" class="truncate text-base font-semibold text-slate-900"></p>
            <p class="text-xs text-slate-500">Student ID <span id="studentId"></span></p>
          </div>
          <span id="studentStatus" class="shrink-0"></span>
        </div>
      </section>

      <!-- Details -->
      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Academic Details</h3>

        <dl class="se-grid">
          <div>
            <dt class="se-label">Student ID</dt>
            <dd id="studentIdDetail" class="se-value"></dd>
          </div>
          <div>
            <dt class="se-label">Department</dt>
            <dd id="studentDepartment" class="se-value uppercase"></dd>
          </div>
          <div>
            <dt class="se-label">Year Level</dt>
            <dd id="studentYearLevel" class="se-value"></dd>
          </div>
          <div>
            <dt class="se-label"><?php echo ($department === 'college') ? 'Course' : 'Program'; ?></dt>
            <dd id="studentProgram" class="se-value"></dd>
          </div>
          <div class="se-span-2">
            <dt class="se-label">Email Address</dt>
            <dd id="studentEmail" class="se-value"></dd>
          </div>
          <div class="se-span-2">
            <dt class="se-label">Account Status</dt>
            <dd><span id="studentStatusDetail"></span></dd>
          </div>
        </dl>
      </section>

      <!-- Account -->
      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Account Security</h3>
        <p class="se-modal__section-hint">Send the student a new password if they cannot sign in.</p>
        <button id="resetPasswordBtn" type="button" class="se-btn se-btn--secondary">
          <i class="fa-solid fa-key pointer-events-none text-xs" aria-hidden="true"></i>
          Reset Password
        </button>
      </section>

    </div>

    <footer class="se-modal__footer">
      <button type="button" data-close-modal="viewStudentModal" class="se-btn se-btn--secondary">Close</button>
    </footer>

  </div>
</div>
