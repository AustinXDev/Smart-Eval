<!-- ADD PROGRAM MODAL -->
<div id="addProgramModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

   <!-- MODAL -->
  <div
    class="w-full max-w-xl overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl">

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <div class="border-b border-slate-200 bg-white px-6 py-5">

      <div class="flex items-start justify-between gap-4">

        <div class="flex items-center gap-3">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-100 text-violet-600">
            <i class="fa-solid fa-graduation-cap text-lg"></i>
          </div>

          <!-- Title -->
          <div>
            <h2 class="text-lg font-semibold text-slate-800">
              Add New Program
            </h2>

            <p class="mt-0.5 text-xs text-slate-500">
              Add an academic program to SMART-EVAL
            </p>
          </div>

        </div>

        <!-- Close -->
        <button
          type="button"
          data-close-modal="addProgramModal"
          class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400
                 transition hover:bg-slate-100 hover:text-slate-700">

          <i class="fa-solid fa-xmark text-base"></i>

        </button>

      </div>

    </div>


    <!-- =====================================================
         BODY
    ====================================================== -->
    <div class="px-6 py-6 overflow-y-auto">

      <form method="POST" id="addProgramForm" class="max-h-[500px] space-y-5 lg:h-auto">

        <!-- =================================================
             PROGRAM INFORMATION
        ================================================== -->
        <div>

          <div class="mb-4">
            <h3 class="text-sm font-semibold text-slate-800">
              Program Information
            </h3>

            <p class="mt-1 text-xs text-slate-500">
              Provide the basic information for the new academic program.
            </p>
          </div>


          <!-- PROGRAM CODE + PROGRAM NAME -->
          <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

            <!-- PROGRAM CODE -->
            <div>

              <label
                for="program_code"
                class="mb-1.5 block text-sm font-medium text-slate-700">

                Program Code
                <span class="text-red-500">*</span>

              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">

                  <i class="fa-solid fa-hashtag text-sm"></i>

                </div>

                <input
                  id="program_code"
                  type="text"
                  name="program_code"
                  placeholder="e.g. BSIT"
                  required
                  class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-3
                         text-sm text-slate-800 outline-none transition
                         placeholder:text-slate-400
                         hover:border-slate-400
                         focus:border-violet-500
                         focus:ring-4 focus:ring-violet-500/10">
              </div>

              <p class="mt-1.5 text-[11px] text-slate-400">
                Short identifier of the program.
              </p>

            </div>


            <!-- PROGRAM NAME -->
            <div>

              <label
                for="program_name"
                class="mb-1.5 block text-sm font-medium text-slate-700">

                Program Name
                <span class="text-red-500">*</span>

              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">

                  <i class="fa-solid fa-book-open text-sm"></i>

                </div>

                <input
                  id="program_name"
                  type="text"
                  name="program_name"
                  placeholder="e.g. Information Technology"
                  required
                  class="w-full rounded-xl border border-slate-300 bg-white py-3 pl-10 pr-3
                         text-sm text-slate-800 outline-none transition
                         placeholder:text-slate-400
                         hover:border-slate-400
                         focus:border-violet-500
                         focus:ring-4 focus:ring-violet-500/10">
              </div>

              <p class="mt-1.5 text-[11px] text-slate-400">
                Full name of the academic program.
              </p>

            </div>

          </div>


          <!-- =================================================
               DEPARTMENT
          ================================================== -->
          <div class="mt-5">

            <label
              for="department"
              class="mb-1.5 block text-sm font-medium text-slate-700">

              Department
              <span class="text-red-500">*</span>

            </label>

            <div class="relative">

              <!-- Building Icon -->
              <div
                class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">

                <i class="fa-solid fa-building-columns text-sm"></i>

              </div>


              <select
                id="department"
                name="department"
                required
                class="w-full appearance-none rounded-xl border border-slate-300 bg-white
                       py-3 pl-10 pr-10 text-sm text-slate-700 outline-none transition
                       hover:border-slate-400
                       focus:border-violet-500
                       focus:ring-4 focus:ring-violet-500/10">

                <option value="" selected disabled>
                  Select department
                </option>

                <option value="college">
                  College
                </option>

                <option value="shs">
                  Senior High School
                </option>

              </select>


              <!-- Chevron -->
              <div
                class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400">

                <i class="fa-solid fa-chevron-down text-xs"></i>

              </div>

            </div>


            <!-- Department Helper -->
            <div
              class="mt-2 flex items-start gap-2 rounded-lg bg-violet-50 px-3 py-2.5">

              <i class="fa-solid fa-circle-info mt-0.5 text-xs text-violet-500"></i>

              <p class="text-[11px] leading-4 text-violet-700">
                The selected department determines where this program
                will appear in student management and reports.
              </p>

            </div>

          </div>

        </div>


        <!-- =================================================
             PREVIEW
        ================================================== -->
        <div
          class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

          <div class="flex items-center gap-2">

            <i class="fa-solid fa-eye text-xs text-slate-400"></i>

            <span class="text-xs font-semibold text-slate-600">
              Program Preview
            </span>

          </div>

          <p class="mt-1 text-xs text-slate-400">
            The program will become available when assigning
            students and managing academic records.
          </p>

        </div>


        <!-- =================================================
             FOOTER
        ================================================== -->
        <div
          class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end">

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="addProgramModal"
            class="w-full rounded-xl border border-slate-300 bg-white px-5 py-2.5
                   text-sm font-medium text-slate-600 transition
                   hover:bg-slate-50 hover:text-slate-800
                   focus:outline-none focus:ring-4 focus:ring-slate-200
                   sm:w-auto">

            Cancel

          </button>


          <!-- Save -->
          <button
            type="submit"
            class="inline-flex w-full items-center justify-center gap-2 rounded-xl
                   bg-violet-600 px-5 py-2.5 text-sm font-semibold text-white
                   shadow-sm transition
                   hover:bg-violet-700
                   focus:outline-none focus:ring-4 focus:ring-violet-500/20
                   active:scale-[0.98]
                   sm:w-auto">

            <i class="fa-solid fa-plus text-xs"></i>

            Add Program

          </button>

        </div>

      </form>

    </div>

  </div>
</div>