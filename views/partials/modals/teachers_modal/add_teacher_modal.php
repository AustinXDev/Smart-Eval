<!-- Add teacher (teachers/index.js + components/teacherForm.js; photo preview: components/imagePreview.js) -->
<div id="addTeacherModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="addTeacherTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-plus"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="addTeacherTitle" class="se-modal__title">Add Teacher</h2>
        <p class="se-modal__subtitle">Register a new faculty member in the evaluation system.</p>
      </div>
      <button type="button" data-close-modal="addTeacherModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="addTeacherForm" class="se-modal__form" enctype="multipart/form-data" method="POST">
      <div class="se-modal__body">

        <!-- Photo -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Teacher Photo</h3>

          <div id="photoDropZone" class="flex flex-col items-center gap-4 rounded-xl border border-dashed border-slate-200 p-4 sm:flex-row">
            <div class="relative shrink-0">
              <div id="photoPlaceholder" class="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                <i class="fa-solid fa-user text-xl" aria-hidden="true"></i>
              </div>
              <img id="teacherPhotoPreview" src="" alt="Teacher photo preview" class="hidden h-16 w-16 rounded-full object-cover ring-1 ring-slate-200">
              <span id="photoCheck" class="absolute -bottom-0.5 -right-0.5 hidden h-5 w-5 rounded-full bg-emerald-500 text-center leading-5 text-white ring-2 ring-white" aria-hidden="true">
                <i class="fa-solid fa-check text-[9px]"></i>
              </span>
            </div>

            <div class="min-w-0 flex-1 text-center sm:text-left">
              <p class="text-sm font-semibold text-slate-900">Upload a profile photo</p>
              <p class="mt-0.5 text-xs text-slate-500">JPG or PNG, up to 5 MB.</p>
              <p class="mt-1 flex items-center justify-center gap-1.5 text-xs text-slate-500 sm:justify-start">
                <i class="fa-regular fa-image" aria-hidden="true"></i>
                <span id="fileName" class="truncate">No file selected</span>
              </p>
            </div>

            <label for="fileInput" class="se-btn se-btn--secondary shrink-0">
              <i class="fa-solid fa-arrow-up-from-bracket text-xs" aria-hidden="true"></i>
              Choose Photo
              <input type="file" id="fileInput" name="photo" accept="image/png,image/jpeg" class="hidden">
            </label>
          </div>
        </section>

        <!-- Details -->
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Basic Information</h3>

          <div class="se-grid">
            <div>
              <label for="teachersId" class="se-label">Employee ID <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-id-badge" aria-hidden="true"></i>
                <input type="text" name="employee_id" id="teachersId" class="se-input" placeholder="e.g., 00-0012" required>
              </div>
            </div>

            <div>
              <label for="teachersName" class="se-label">Full Name <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-user" aria-hidden="true"></i>
                <input type="text" name="full_name" id="teachersName" class="se-input" placeholder="First name, Middle initial, Last name" required>
              </div>
            </div>

            <div>
              <label for="teachersEmail" class="se-label">Email Address <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-envelope" aria-hidden="true"></i>
                <input type="email" name="email" id="teachersEmail" class="se-input" placeholder="name@example.com" required>
              </div>
            </div>

            <div>
              <label for="teachersDept" class="se-label">Department <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-building" aria-hidden="true"></i>
                <select name="department" id="teachersDept" class="se-select" required>
                  <option value="<?php echo htmlspecialchars($department, ENT_QUOTES, 'UTF-8'); ?>">
                    <?php echo htmlspecialchars(strtoupper($department), ENT_QUOTES, 'UTF-8'); ?>
                  </option>
                </select>
              </div>
            </div>
          </div>

          <p class="se-help">Fields marked <span class="se-required">*</span> are required.</p>
        </section>

      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="addTeacherModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-user-plus text-xs" aria-hidden="true"></i>
          Add Teacher
        </button>
      </footer>
    </form>

  </div>
</div>
