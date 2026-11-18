<!-- View teacher + teaching assignments
     (teacherRender.js renderViewDetails fills #name, #id, #department, #email, #handle_teacher_id, the photo;
      table.js loadTeacherHandles fills #handleTable tbody; teacherForm.js submits #addHandleForm) -->
<div id="viewDetails" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="viewTeacherTitle">
  <div class="se-modal__panel se-modal__panel--lg">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-chalkboard-user"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="viewTeacherTitle" class="se-modal__title">Teacher Information</h2>
        <p class="se-modal__subtitle">View the teacher profile and teaching assignments.</p>
      </div>
      <button type="button" data-close-modal="viewDetails" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">

      <!-- Profile -->
      <section class="se-modal__section">
        <div class="flex flex-col items-center gap-4 text-center sm:flex-row sm:text-left">
          <img id="teacherViewPhoto" src="<?= BASE_URL ?>uploads/teachers/default_teacher.png" alt="Teacher photo"
            class="h-16 w-16 shrink-0 rounded-full object-cover ring-1 ring-slate-200">

          <div class="min-w-0 flex-1">
            <p id="name" class="truncate text-base font-semibold text-slate-900">Juan Dela Cruz</p>
            <p class="mt-1 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs text-slate-500 sm:justify-start">
              <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-id-badge" aria-hidden="true"></i><span id="id"></span></span>
              <span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-building" aria-hidden="true"></i><span id="department"></span></span>
            </p>
          </div>

          <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/15">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active Faculty
          </span>
        </div>
      </section>

      <!-- Contact -->
      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Contact Information</h3>
        <span class="se-label">Email Address</span>
        <div class="se-input-icon">
          <i class="fa-solid fa-envelope" aria-hidden="true"></i>
          <p id="email" class="se-value"></p>
        </div>
      </section>

      <!-- Assignments -->
      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Teaching Assignments</h3>
        <p class="se-modal__section-hint">Assign the levels and programs this teacher handles.</p>

        <form id="addHandleForm" method="POST">
          <input type="hidden" name="teacher_id" id="handle_teacher_id">
          <input type="hidden" name="department" value="<?php echo htmlspecialchars($department, ENT_QUOTES, 'UTF-8'); ?>">

          <div class="grid grid-cols-1 items-end gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,1.4fr)_auto]">
            <div>
              <label for="handleLevel" class="se-label"><?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?></label>
              <select id="handleLevel" name="level" class="se-select" required>
                <?php if ($department === 'college') { ?>
                  <option value="" disabled selected hidden>Select Year Level</option>
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
              <label for="programSelect" class="se-label">Program</label>
              <select name="program" id="programSelect" class="se-select" required>
                <!-- JS fills this -->
              </select>
            </div>

            <button type="submit" class="se-btn se-btn--primary">
              <i class="fa-solid fa-plus text-xs" aria-hidden="true"></i>
              Add
            </button>
          </div>
        </form>

        <div id="handleTable" class="mt-4 overflow-hidden rounded-xl border border-slate-200">
          <div class="max-h-[240px] overflow-auto">
            <table class="min-w-full text-sm">
              <thead class="sticky top-0 z-10 bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500">
                <tr>
                  <th class="px-4 py-2.5 text-left font-semibold">Level</th>
                  <th class="px-4 py-2.5 text-left font-semibold">Program</th>
                  <th class="px-4 py-2.5 text-right font-semibold">Action</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100 bg-white">
                <!-- JS fills this -->
              </tbody>
            </table>
          </div>
        </div>
      </section>

    </div>

    <footer class="se-modal__footer">
      <button type="button" data-close-modal="viewDetails" class="se-btn se-btn--secondary">Close</button>
    </footer>

  </div>
</div>
