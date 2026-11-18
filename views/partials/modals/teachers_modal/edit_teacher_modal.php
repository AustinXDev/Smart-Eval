<!-- Edit teacher (teachers/components/teacherRender.js renderEditForm; teacherForm.js submits #editTeacherForm) -->
<div id="editTeacherModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editTeacherTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-pen"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editTeacherTitle" class="se-modal__title">Teacher Information</h2>
        <p class="se-modal__subtitle">Update the teacher profile and account information.</p>
      </div>
      <button type="button" data-close-modal="editTeacherModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="editTeacherForm" class="se-modal__form" enctype="multipart/form-data">
      <input type="hidden" name="teacher_id" id="teacherId">

      <div class="se-modal__body">

        <!-- Photo -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Faculty Profile</h3>

          <div class="flex flex-col items-center gap-4 rounded-xl border border-dashed border-slate-200 p-4 sm:flex-row">
            <div class="relative shrink-0">
              <img id="editTeacherPhotoPreview" src="<?= BASE_URL ?>uploads/teachers/default_teacher.png" alt="Teacher photo"
                class="h-16 w-16 rounded-full object-cover ring-1 ring-slate-200">
              <span id="editPhotoCheck" class="absolute -bottom-0.5 -right-0.5 hidden h-5 w-5 rounded-full bg-emerald-500 text-center leading-5 text-white ring-2 ring-white" aria-hidden="true">
                <i class="fa-solid fa-check text-[9px]"></i>
              </span>
            </div>

            <div class="min-w-0 flex-1 text-center sm:text-left">
              <p id="header-name" class="truncate text-sm font-semibold text-slate-900">Teacher Name</p>
              <p class="mt-0.5 text-xs text-slate-500">JPG or PNG, up to 5 MB.</p>
              <p class="mt-1 flex items-center justify-center gap-1.5 text-xs text-slate-500 sm:justify-start">
                <i class="fa-regular fa-image" aria-hidden="true"></i>
                <span id="editTeacherFileName" class="truncate">No new photo selected</span>
              </p>
            </div>

            <label for="editTeacherPhotoInput" class="se-btn se-btn--secondary shrink-0">
              <i class="fa-solid fa-camera text-xs" aria-hidden="true"></i>
              Change Photo
              <input type="file" class="hidden" id="editTeacherPhotoInput" name="photo" accept="image/png,image/jpeg">
            </label>
          </div>
        </section>

        <!-- Details -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Teacher Details</h3>

          <div class="se-grid">
            <div>
              <label for="employee_Id" class="se-label">Employee ID <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-id-badge" aria-hidden="true"></i>
                <input type="text" name="employee_id" id="employee_Id" class="se-input" required>
              </div>
            </div>

            <div>
              <label for="teacherName" class="se-label">Full Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <input type="text" name="full_name" id="teacherName" class="se-input" required>
              </div>
            </div>

            <div>
              <label for="teacherEmail" class="se-label">Email Address <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <input type="email" name="email" id="teacherEmail" class="se-input" required>
              </div>
            </div>

            <div>
              <label for="teacherDept" class="se-label">Department <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-building" aria-hidden="true"></i>
                <select name="department" id="teacherDept" class="se-select" required>
                  <option value="<?php echo htmlspecialchars($department, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars(strtoupper($department), ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                </select>
              </div>
            </div>
          </div>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editTeacherModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" id="save" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          <span>Save Changes</span>
        </button>
      </footer>
    </form>

  </div>
</div>
