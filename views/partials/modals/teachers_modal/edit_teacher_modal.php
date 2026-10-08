<!-- Edit Teacher Modal -->
<div
  id="editTeacherModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center
         bg-slate-950/60 backdrop-blur-sm
         p-3 sm:p-5"
>
  
  <!-- Modal -->
  <div
    class="relative flex max-h-[95vh] w-full max-w-3xl flex-col
           overflow-hidden rounded-2xl bg-slate-50
           shadow-2xl ring-1 ring-black/5"
  >

    <!-- ================================================= -->
    <!-- HEADER -->
    <!-- ================================================= -->
    <div
      class="flex items-center justify-between
             border-b border-slate-200
             bg-white px-5 py-4 sm:px-6"
    >

      <div class="flex items-center gap-3">

        <!-- Icon -->
        <div
          class="flex h-10 w-10 items-center justify-center
                 rounded-xl bg-purple-100"
        >
          <i class="fas fa-user-edit text-purple-600"></i>
        </div>

        <div>
          <h2 class="text-base font-semibold text-slate-800 sm:text-lg">
            Teacher Information
          </h2>

          <p class="text-xs text-slate-500">
            Update the teacher's profile and account information
          </p>
        </div>

      </div>

      <!-- Close -->
      <button
        type="button"
        data-close-modal="editTeacherModal"
        class="flex h-9 w-9 items-center justify-center
               rounded-lg text-slate-400
               transition-all duration-200
               hover:bg-slate-100 hover:text-slate-700
               focus:outline-none focus:ring-2 focus:ring-purple-500/30"
        aria-label="Close modal"
      >
        <i class="fas fa-times text-sm"></i>
      </button>

    </div>


    <!-- ================================================= -->
    <!-- SCROLLABLE CONTENT -->
    <!-- ================================================= -->
    <div class="overflow-y-auto">

      <form
        id="editTeacherForm"
        enctype="multipart/form-data"
        method="POST"
        class="p-4 sm:p-6"
      >

        <!-- Hidden Teacher ID -->
        <input
          type="hidden"
          name="teacher_id"
          id="teacherId"
        >


        <!-- ================================================= -->
        <!-- PROFILE SECTION -->
        <!-- ================================================= -->
        <div
          class="rounded-2xl border border-slate-200
                 bg-white p-5 shadow-sm sm:p-6"
        >

          <div
            class="flex flex-col items-center gap-5
                   sm:flex-row"
          >

            <!-- Avatar -->
            <div class="relative shrink-0">

              <!-- Glow -->
              <div
                class="absolute inset-0 rounded-full
                       bg-purple-400/20 blur-xl"
              ></div>

              <!-- Image -->
              <img
                id="editTeacherPhotoPreview"
                src="<?= BASE_URL ?>uploads/teachers/default_teacher.png"
                alt="Teacher photo"
                class="relative h-28 w-28 rounded-full
                       border-4 border-white
                       object-cover shadow-lg
                       ring-1 ring-slate-200"
              >

              <!-- Online / Photo Indicator -->
              <div
                id="editPhotoCheck"
                class="absolute bottom-1 right-1
                       flex h-7 w-7 items-center justify-center
                       rounded-full border-2 border-white
                       bg-green-500 text-white"
              >
                <i class="fas fa-check text-[10px]"></i>
              </div>

            </div>


            <!-- Profile Information -->
            <div class="flex-1 text-center sm:text-left">

              <p
                class="mb-1 text-xs font-medium uppercase
                       tracking-wider text-purple-600"
              >
                Faculty Profile
              </p>

              <h1
                id="header-name"
                class="text-xl font-bold tracking-tight
                       text-slate-800 sm:text-2xl"
              >
                Teacher Name
              </h1>

              <p class="mt-1 text-sm text-slate-500">
                Update the profile photo using JPG or PNG.
              </p>


              <!-- Upload -->
              <label
                for="editTeacherPhotoInput"
                class="mt-4 inline-flex cursor-pointer
                       items-center gap-2 rounded-lg
                       bg-purple-600 px-4 py-2.5
                       text-sm font-medium text-white
                       shadow-sm
                       transition-all duration-200
                       hover:bg-purple-700
                       hover:shadow-md
                       active:scale-[0.98]"
              >

                <i class="fas fa-camera text-xs"></i>

                Change Photo

                <input
                  type="file"
                  class="hidden"
                  id="editTeacherPhotoInput"
                  name="photo"
                  accept="image/png,image/jpeg"
                >

              </label>

              <!-- File name -->
              <p
                id="editTeacherFileName"
                class="mt-2 text-xs text-slate-400"
              >
                No new photo selected
              </p>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- TEACHER DETAILS -->
        <!-- ================================================= -->
        <div
          class="mt-5 rounded-2xl border border-slate-200
                 bg-white p-5 shadow-sm sm:p-6"
        >

          <!-- Section Header -->
          <div
            class="mb-5 flex items-center gap-3
                   border-b border-slate-100 pb-4"
          >

            <div
              class="flex h-9 w-9 items-center justify-center
                     rounded-lg bg-purple-100"
            >
              <i class="fas fa-id-card text-sm text-purple-600"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800">
                Teacher Details
              </h3>

              <p class="text-xs text-slate-500">
                Basic information about the faculty member
              </p>
            </div>

          </div>


          <!-- Fields -->
          <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">


            <!-- Employee ID -->
            <div>

              <label
                for="employee_Id"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                Employee ID
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0
                         flex items-center pl-3"
                >
                  <i class="fas fa-id-badge text-xs text-slate-400"></i>
                </div>

                <input
                  type="text"
                  name="employee_id"
                  id="employee_Id"
                  placeholder="e.g. 00-0000"
                  class="w-full rounded-xl border border-slate-200
                         bg-slate-50 py-2.5 pl-9 pr-3
                         text-sm text-slate-800
                         outline-none transition-all duration-200
                         placeholder:text-slate-400
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:bg-white
                         focus:ring-4 focus:ring-purple-500/10"
                  required
                >

              </div>

            </div>


            <!-- Full Name -->
            <div>

              <label
                for="teacherName"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                Full Name
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0
                         flex items-center pl-3"
                >
                  <i class="fas fa-user text-xs text-slate-400"></i>
                </div>

                <input
                  type="text"
                  name="full_name"
                  id="teacherName"
                  placeholder="Enter teacher name"
                  class="w-full rounded-xl border border-slate-200
                         bg-slate-50 py-2.5 pl-9 pr-3
                         text-sm text-slate-800
                         outline-none transition-all duration-200
                         placeholder:text-slate-400
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:bg-white
                         focus:ring-4 focus:ring-purple-500/10"
                  required
                >

              </div>

            </div>


            <!-- Email -->
            <div>

              <label
                for="teacherEmail"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                Email Address
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0
                         flex items-center pl-3"
                >
                  <i class="fas fa-envelope text-xs text-slate-400"></i>
                </div>

                <input
                  type="email"
                  name="email"
                  id="teacherEmail"
                  placeholder="example@gmail.com"
                  class="w-full rounded-xl border border-slate-200
                         bg-slate-50 py-2.5 pl-9 pr-3
                         text-sm text-slate-800
                         outline-none transition-all duration-200
                         placeholder:text-slate-400
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:bg-white
                         focus:ring-4 focus:ring-purple-500/10"
                  required
                >

              </div>

            </div>


            <!-- Department -->
            <div>

              <label
                for="teacherDept"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                Department
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0
                         z-10 flex items-center pl-3"
                >
                  <i class="fas fa-building text-xs text-slate-400"></i>
                </div>

                <select
                  name="department"
                  id="teacherDept"
                  class="w-full appearance-none rounded-xl
                         border border-slate-200
                         bg-slate-50 py-2.5 pl-9 pr-9
                         text-sm text-slate-800
                         outline-none transition-all duration-200
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:bg-white
                         focus:ring-4 focus:ring-purple-500/10"
                  required
                >

                  <option
                    value="<?php echo $department; ?>"
                    selected
                  >
                    <?php echo strtoupper($department); ?>
                  </option>

                </select>

                <div
                  class="pointer-events-none absolute inset-y-0 right-0
                         flex items-center pr-3"
                >
                  <i class="fas fa-chevron-down text-xs text-slate-400"></i>
                </div>

              </div>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- ACTIONS -->
        <!-- ================================================= -->
        <div
          class="mt-6 flex flex-col-reverse gap-3
                 border-t border-slate-200 pt-5
                 sm:flex-row sm:justify-end"
        >

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="editTeacherModal"
            class="inline-flex items-center justify-center
                   gap-2 rounded-xl border border-slate-200
                   bg-white px-5 py-2.5
                   text-sm font-medium text-slate-600
                   transition-all duration-200
                   hover:border-slate-300
                   hover:bg-slate-50
                   hover:text-slate-800
                   focus:outline-none
                   focus:ring-4 focus:ring-slate-200"
          >
            <i class="fas fa-times text-xs"></i>
            Cancel
          </button>


          <!-- Save -->
          <button
            type="submit"
            class="save inline-flex items-center justify-center
                   gap-2 rounded-xl
                   bg-purple-600 px-5 py-2.5
                   text-sm font-semibold text-white
                   shadow-sm
                   transition-all duration-200
                   hover:bg-purple-700
                   hover:shadow-md
                   active:scale-[0.98]
                   focus:outline-none
                   focus:ring-4 focus:ring-purple-500/20"
            id="save"
          >

            <i class="fas fa-save text-xs"></i>

            <span>Save Changes</span>

          </button>

        </div>

      </form>

    </div>

  </div>
</div>