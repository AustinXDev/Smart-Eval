<!-- =========================================================
     ADD TEACHER MODAL
========================================================= -->
<div
  id="addTeacherModal"
  class="
    fixed inset-0 z-50 hidden
    flex items-center justify-center
    bg-slate-950/60
    px-3 py-4
    backdrop-blur-sm
  "
>
  <!-- Modal -->
  <div
    class="
      relative
      w-full max-w-2xl
      max-h-[94vh]
      overflow-hidden
      rounded-2xl
      border border-slate-200
      bg-white
      shadow-2xl
      animate-[modalIn_.2s_ease-out]
    "
  >

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <div
      class="
        relative
        overflow-hidden
        border-b border-slate-200
        bg-white
        px-6 py-5
      "
    >

      <!-- Subtle accent -->
      <div
        class="
          absolute left-0 top-0
          h-full w-1
          bg-gradient-to-b
          from-violet-500
          to-indigo-600
        "
      ></div>

      <div class="flex items-start gap-4">

        <!-- Icon -->
        <div
          class="
            flex h-11 w-11 shrink-0
            items-center justify-center
            rounded-xl
            bg-violet-50
            text-violet-600
            ring-1 ring-violet-100
          "
        >
          <i class="fas fa-user-plus text-lg"></i>
        </div>

        <!-- Title -->
        <div class="min-w-0">
          <h2
            class="
              text-lg
              font-semibold
              tracking-tight
              text-slate-800
            "
          >
            Add Teacher
          </h2>

          <p
            class="
              mt-0.5
              text-xs
              text-slate-500
            "
          >
            Register a new faculty member in the evaluation system.
          </p>
        </div>

      </div>
    </div>


    <!-- =====================================================
         FORM
    ====================================================== -->
    <form
      id="addTeacherForm"
      class="
        max-h-[calc(94vh-90px)]
        overflow-y-auto
        p-6
      "
      enctype="multipart/form-data"
      method="POST"
    >

      <div class="space-y-6">


      <!-- Teacher Photo -->
      <div class="space-y-3">

        <!-- Section Label -->
        <div class="flex items-center gap-2">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-purple-100">
            <i class="fas fa-camera text-purple-600 text-sm"></i>
          </div>

          <div>
            <h3 class="text-sm font-semibold text-gray-800">
              Teacher Photo
            </h3>
            <p class="text-xs text-gray-500">
              Upload a profile photo for the teacher
            </p>
          </div>
        </div>

        <!-- Upload Box -->
        <div
          id="photoDropZone"
          class="relative rounded-xl border-2 border-dashed border-gray-200
                bg-gray-50 p-5 transition-all duration-200
                hover:border-purple-400 hover:bg-purple-50/40"
        >

          <div class="flex flex-col sm:flex-row items-center gap-5">

            <!-- Image Preview -->
            <div class="relative shrink-0">

              <!-- Placeholder -->
              <div
                id="photoPlaceholder"
                class="flex h-24 w-24 items-center justify-center
                      rounded-full border-4 border-white
                      bg-gray-200 shadow-sm"
              >
                <i class="fas fa-user text-3xl text-gray-400"></i>
              </div>

              <!-- Preview Image -->
              <img
                id="teacherPhotoPreview"
                src=""
                alt="Teacher photo preview"
                class="hidden h-24 w-24 rounded-full object-cover
                      border-4 border-white shadow-md"
              />

              <!-- Check Badge -->
              <div
                id="photoCheck"
                class="hidden absolute bottom-0 right-0
                      flex h-7 w-7 items-center justify-center
                      rounded-full border-2 border-white
                      bg-green-500 text-white"
              >
                <i class="fas fa-check text-xs"></i>
              </div>

            </div>

            <!-- Upload Content -->
            <div class="flex-1 text-center sm:text-left">

              <p class="text-sm font-medium text-gray-700">
                Upload teacher photo
              </p>

              <p class="mt-1 text-xs text-gray-500">
                JPG or PNG format. Maximum file size: 5MB.
              </p>

              <!-- File Input -->
              <label
                for="fileInput"
                class="mt-3 inline-flex cursor-pointer items-center
                      gap-2 rounded-lg bg-purple-600 px-4 py-2
                      text-sm font-medium text-white shadow-sm
                      transition-all duration-200
                      hover:bg-purple-700 hover:shadow-md
                      active:scale-95"
              >
                <i class="fas fa-cloud-upload-alt"></i>
                Choose Photo

                <input
                  type="file"
                  id="fileInput"
                  name="photo"
                  accept="image/png,image/jpeg"
                  class="hidden"
                >
              </label>

              <!-- Selected File -->
              <div class="mt-2 flex items-center gap-2">
                <i class="fas fa-image text-xs text-gray-400"></i>

                <span
                  id="fileName"
                  class="truncate text-xs text-gray-500"
                >
                  No file selected
                </span>
              </div>

            </div>

          </div>

        </div>

      </div>


        <!-- =================================================
             BASIC INFORMATION
        ================================================== -->
        <section>

          <div class="mb-4 flex items-center gap-2">

            <div
              class="
                flex h-8 w-8
                items-center justify-center
                rounded-lg
                bg-indigo-50
                text-indigo-600
              "
            >
              <i class="fas fa-id-card text-sm"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800">
                Basic Information
              </h3>

              <p class="text-xs text-slate-400">
                Enter the teacher's account information.
              </p>
            </div>

          </div>


          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">


            <!-- Employee ID -->
            <div>

              <label
                for="teachersId"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-medium
                  text-slate-600
                "
              >
                Employee ID
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <span
                  class="
                    pointer-events-none
                    absolute inset-y-0 left-0
                    flex items-center
                    pl-3
                    text-slate-400
                  "
                >
                  <i class="fas fa-id-badge text-xs"></i>
                </span>

                <input
                  type="text"
                  name="employee_id"
                  id="teachersId"
                  placeholder="e.g. 00-0000"
                  class="
                    w-full
                    rounded-lg
                    border border-slate-200
                    bg-white
                    py-2.5 pl-9 pr-3
                    text-sm
                    text-slate-700
                    placeholder:text-slate-400
                    outline-none
                    transition
                    focus:border-violet-500
                    focus:ring-4
                    focus:ring-violet-500/10
                  "
                  required
                >

              </div>

            </div>


            <!-- Full Name -->
            <div>

              <label
                for="teachersName"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-medium
                  text-slate-600
                "
              >
                Full Name
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <span
                  class="
                    pointer-events-none
                    absolute inset-y-0 left-0
                    flex items-center
                    pl-3
                    text-slate-400
                  "
                >
                  <i class="fas fa-user text-xs"></i>
                </span>

                <input
                  type="text"
                  name="full_name"
                  id="teachersName"
                  placeholder="e.g. Juan Dela Cruz"
                  class="
                    w-full
                    rounded-lg
                    border border-slate-200
                    bg-white
                    py-2.5 pl-9 pr-3
                    text-sm
                    text-slate-700
                    placeholder:text-slate-400
                    outline-none
                    transition
                    focus:border-violet-500
                    focus:ring-4
                    focus:ring-violet-500/10
                  "
                  required
                >

              </div>

            </div>


            <!-- Email -->
            <div>

              <label
                for="teachersEmail"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-medium
                  text-slate-600
                "
              >
                Email Address
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <span
                  class="
                    pointer-events-none
                    absolute inset-y-0 left-0
                    flex items-center
                    pl-3
                    text-slate-400
                  "
                >
                  <i class="fas fa-envelope text-xs"></i>
                </span>

                <input
                  type="email"
                  name="email"
                  id="teachersEmail"
                  placeholder="e.g. example@gmail.com"
                  class="
                    w-full
                    rounded-lg
                    border border-slate-200
                    bg-white
                    py-2.5 pl-9 pr-3
                    text-sm
                    text-slate-700
                    placeholder:text-slate-400
                    outline-none
                    transition
                    focus:border-violet-500
                    focus:ring-4
                    focus:ring-violet-500/10
                  "
                  required
                >

              </div>

            </div>


            <!-- Department -->
            <div>

              <label
                for="teachersDept"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-medium
                  text-slate-600
                "
              >
                Department
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <span
                  class="
                    pointer-events-none
                    absolute inset-y-0 left-0
                    z-10
                    flex items-center
                    pl-3
                    text-slate-400
                  "
                >
                  <i class="fas fa-building text-xs"></i>
                </span>

                <select
                  name="department"
                  id="teachersDept"
                  class="
                    w-full
                    appearance-none
                    rounded-lg
                    border border-slate-200
                    bg-white
                    py-2.5 pl-9 pr-9
                    text-sm
                    text-slate-700
                    outline-none
                    transition
                    focus:border-violet-500
                    focus:ring-4
                    focus:ring-violet-500/10
                  "
                  required
                >

                  <option value="<?php echo $department; ?>">
                    <?php echo strtoupper($department); ?>
                  </option>

                </select>

                <span
                  class="
                    pointer-events-none
                    absolute inset-y-0 right-0
                    flex items-center
                    pr-3
                    text-slate-400
                  "
                >
                  <i class="fas fa-chevron-down text-[10px]"></i>
                </span>

              </div>

            </div>

          </div>

        </section>


        <!-- =================================================
             REQUIRED NOTE
        ================================================== -->
        <div
          class="
            flex
            items-start
            gap-3
            rounded-lg
            border border-amber-100
            bg-amber-50
            px-4 py-3
          "
        >

          <i
            class="
              fas fa-info-circle
              mt-0.5
              text-amber-500
            "
          ></i>

          <p class="text-xs leading-5 text-amber-700">
            Fields marked with
            <span class="font-semibold text-red-500">*</span>
            are required.
            Please make sure the information is accurate before adding
            the teacher.
          </p>

        </div>


      </div>


      <!-- =====================================================
           ACTIONS
      ====================================================== -->
      <div
        class="
          mt-6
          flex
          flex-col-reverse
          gap-2
          border-t
          border-slate-100
          pt-5
          sm:flex-row
          sm:justify-end
        "
      >

        <!-- Cancel -->
        <button
          type="button"
          data-close-modal="addTeacherModal"
          class="
            inline-flex
            items-center
            justify-center
            gap-2
            rounded-lg
            border border-slate-200
            bg-white
            px-5 py-2.5
            text-sm
            font-medium
            text-slate-600
            transition
            hover:bg-slate-50
            hover:text-slate-800
            active:scale-[0.98]
            focus:outline-none
            focus:ring-2
            focus:ring-slate-300/50
          "
        >
          Cancel
        </button>


        <!-- Add Teacher -->
        <button
          type="submit"
          class="
            inline-flex
            items-center
            justify-center
            gap-2
            rounded-lg
            bg-violet-600
            px-5 py-2.5
            text-sm
            font-medium
            text-white
            shadow-sm
            shadow-violet-200
            transition
            hover:bg-violet-700
            hover:shadow-md
            active:scale-[0.98]
            focus:outline-none
            focus:ring-4
            focus:ring-violet-500/20
          "
        >
          <i class="fas fa-user-plus text-xs"></i>
          Add Teacher
        </button>

      </div>

    </form>

  </div>
</div>