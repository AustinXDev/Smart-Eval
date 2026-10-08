<!-- CREATE EVALUATION PERIOD MODAL -->
<div id="updatePeriodModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- MODAL CONTAINER -->
  <div
    class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl animate-fadeIn"
  >

    <!-- ========================================================
         HEADER
    ========================================================= -->
    <div
      class="flex shrink-0 items-center justify-between border-b border-slate-200 bg-white px-6 py-5"
    >

      <div class="flex items-center gap-3">

        <!-- Icon -->
        <div
          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
        >
          <i class="fa-solid fa-calendar-pen text-sm"></i>
        </div>

        <!-- Heading -->
        <div>
          <h2 class="text-base font-semibold text-slate-900 sm:text-lg">
            Update Evaluation Period
          </h2>

          <p class="mt-0.5 text-xs text-slate-400">
            Modify the configuration of an existing evaluation cycle
          </p>
        </div>

      </div>

      <!-- Close -->
      <button
        type="button"
        data-close-modal="updatePeriodModal"
        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-violet-500/10"
        aria-label="Close modal"
      >
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>

    </div>


    <!-- ========================================================
         SCROLLABLE BODY
    ========================================================= -->
    <div
      class="overflow-y-auto bg-slate-50/70 p-5 sm:p-6"
    >

      <form id="updatePeriodForm" class="space-y-5">

        <!-- Hidden ID -->
        <input
          type="hidden"
          name="period_id"
          id="update_period_id"
        >


        <!-- ====================================================
             ACADEMIC IDENTITY
        ===================================================== -->
        <section
          class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        >

          <!-- Section Header -->
          <div
            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
          >

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"
            >
              <i class="fa-solid fa-graduation-cap text-xs"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800">
                Academic Identity
              </h3>

              <p class="mt-0.5 text-[11px] text-slate-400">
                Update the academic year and semester
              </p>
            </div>

          </div>


          <!-- Content -->
          <div class="grid grid-cols-1 gap-5 p-5 md:grid-cols-2">

            <!-- Academic Year -->
            <div>

              <label
                for="update_academic_year"
                class="mb-1.5 block text-xs font-semibold text-slate-600"
              >
                Academic Year
                <span class="text-red-500">*</span>
              </label>

              <input
                type="text"
                id="update_academic_year"
                name="update_academic_year"
                placeholder="e.g. 2025-2026"
                class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                required
              >

              <p class="mt-1.5 text-[11px] text-slate-400">
                Update the academic year for this evaluation cycle.
              </p>

            </div>


            <!-- Semester -->
            <div>

              <label
                class="mb-2 block text-xs font-semibold text-slate-600"
              >
                Semester
                <span class="text-red-500">*</span>
              </label>

              <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

                <!-- 1st Semester -->
                <label class="cursor-pointer">

                  <input
                    type="radio"
                    name="update_semester"
                    value="1st Semester"
                    class="peer sr-only"
                    required
                  >

                  <div
                    class="flex h-10 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 peer-checked:border-violet-400 peer-checked:bg-violet-50 peer-checked:text-violet-700"
                  >

                    <span
                      class="mr-2 flex h-4 w-4 items-center justify-center rounded-full border border-slate-300"
                    >
                      <span
                        class="h-2 w-2 rounded-full bg-violet-600 opacity-0 peer-checked:opacity-100"
                      ></span>
                    </span>

                    1st Semester

                  </div>

                </label>


                <!-- 2nd Semester -->
                <label class="cursor-pointer">

                  <input
                    type="radio"
                    name="update_semester"
                    value="2nd Semester"
                    class="peer sr-only"
                  >

                  <div
                    class="flex h-10 items-center rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm font-medium text-slate-600 transition hover:border-slate-300 peer-checked:border-violet-400 peer-checked:bg-violet-50 peer-checked:text-violet-700"
                  >

                    <span
                      class="mr-2 flex h-4 w-4 items-center justify-center rounded-full border border-slate-300"
                    >
                      <span
                        class="h-2 w-2 rounded-full bg-violet-600 opacity-0 peer-checked:opacity-100"
                      ></span>
                    </span>

                    2nd Semester

                  </div>

                </label>

              </div>

            </div>

          </div>

        </section>


        <!-- ====================================================
             DEPARTMENT
        ===================================================== -->
        <section
          class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
        >

          <!-- Header -->
          <div
            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
          >

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"
            >
              <i class="fa-solid fa-building-columns text-xs"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-800">
                Department Restriction
              </h3>

              <p class="mt-0.5 text-[11px] text-slate-400">
                Update which department will use this evaluation
              </p>
            </div>

          </div>


          <!-- Department Options -->
          <div class="grid grid-cols-1 gap-3 p-5 sm:grid-cols-2">

            <!-- College -->
            <label class="cursor-pointer">

              <input
                type="radio"
                name="update_department"
                value="college"
                class="peer sr-only"
              >

              <div
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 peer-checked:border-violet-400 peer-checked:bg-violet-50"
              >

                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm peer-checked:text-violet-600"
                >
                  <i class="fa-solid fa-building text-xs"></i>
                </div>

                <div class="min-w-0">

                  <p class="text-sm font-semibold text-slate-700">
                    College
                  </p>

                  <p class="mt-0.5 text-[11px] text-slate-400">
                    Higher education programs
                  </p>

                </div>

                <div
                  class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-slate-300"
                >
                  <span
                    class="h-2.5 w-2.5 rounded-full bg-violet-600 opacity-0 peer-checked:opacity-100"
                  ></span>
                </div>

              </div>

            </label>


            <!-- SHS -->
            <label class="cursor-pointer">

              <input
                type="radio"
                name="update_department"
                value="shs"
                class="peer sr-only"
              >

              <div
                class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 transition hover:border-slate-300 peer-checked:border-violet-400 peer-checked:bg-violet-50"
              >

                <div
                  class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-slate-500 shadow-sm"
                >
                  <i class="fa-solid fa-school text-xs"></i>
                </div>

                <div class="min-w-0">

                  <p class="text-sm font-semibold text-slate-700">
                    Senior High School
                  </p>

                  <p class="mt-0.5 text-[11px] text-slate-400">
                    SHS academic programs
                  </p>

                </div>

                <div
                  class="ml-auto flex h-5 w-5 shrink-0 items-center justify-center rounded-full border border-slate-300"
                >
                  <span
                    class="h-2.5 w-2.5 rounded-full bg-violet-600 opacity-0 peer-checked:opacity-100"
                  ></span>
                </div>

              </div>

            </label>

          </div>

        </section>


        <!-- ====================================================
             SCHEDULE + QUESTION SET
        ===================================================== -->
        <div class="grid grid-cols-1 gap-5 lg:grid-cols-2">


          <!-- ==================================================
               SCHEDULE
          =================================================== -->
          <section
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
          >

            <!-- Header -->
            <div
              class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
            >

              <div
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"
              >
                <i class="fa-solid fa-clock text-xs"></i>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-slate-800">
                  Evaluation Schedule
                </h3>

                <p class="mt-0.5 text-[11px] text-slate-400">
                  Update the evaluation period
                </p>
              </div>

            </div>


            <div class="space-y-4 p-5">

              <!-- Warning -->
              <div
                class="flex gap-3 rounded-xl border border-amber-100 bg-amber-50 px-3.5 py-3"
              >

                <div class="mt-0.5 text-amber-500">
                  <i class="fa-solid fa-circle-info text-xs"></i>
                </div>

                <p class="text-[11px] leading-relaxed text-amber-700">
                  The end date and time must be later than the start date
                  and time.
                </p>

              </div>


              <!-- Start Date -->
              <div>

                <label
                  for="update_start_date"
                  class="mb-1.5 block text-xs font-semibold text-slate-600"
                >
                  Start Date & Time
                </label>

                <input
                  type="datetime-local"
                  id="update_start_date"
                  name="update_start_date"
                  class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                >

              </div>


              <!-- End Date -->
              <div>

                <label
                  for="update_end_date"
                  class="mb-1.5 block text-xs font-semibold text-slate-600"
                >
                  End Date & Time
                </label>

                <input
                  type="datetime-local"
                  id="update_end_date"
                  name="update_end_date"
                  class="h-10 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                >

              </div>

            </div>

          </section>


          <!-- ==================================================
               QUESTION SET
          =================================================== -->
          <section
            class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
          >

            <!-- Header -->
            <div
              class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
            >

              <div
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-50 text-violet-600"
              >
                <i class="fa-solid fa-clipboard-question text-xs"></i>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-slate-800">
                  Question Set
                </h3>

                <p class="mt-0.5 text-[11px] text-slate-400">
                  Update the evaluation form
                </p>
              </div>

            </div>


            <div class="p-5">

              <label
                for="updateQuestionSetSelect"
                class="mb-1.5 block text-xs font-semibold text-slate-600"
              >
                Evaluation Form
              </label>

              <div class="relative">

                <select
                  name="update_question_set"
                  id="updateQuestionSetSelect"
                  class="h-10 w-full cursor-pointer appearance-none rounded-xl border border-slate-200 bg-slate-50 px-3 pr-9 text-sm font-medium text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-400 focus:bg-white focus:ring-4 focus:ring-violet-500/10"
                >

                  <option disabled selected>
                    Select Question Bank
                  </option>

                  <!-- JS populate -->

                </select>

                <div
                  class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-slate-400"
                >
                  <i class="fa-solid fa-chevron-down text-[9px]"></i>
                </div>

              </div>


              <!-- Helper -->
              <div
                class="mt-4 flex items-start gap-2 rounded-lg bg-slate-50 px-3 py-2.5"
              >

                <i
                  class="fa-solid fa-circle-info mt-0.5 text-[10px] text-slate-400"
                ></i>

                <p class="text-[11px] leading-relaxed text-slate-400">
                  Select the question bank assigned to this evaluation
                  period.
                </p>

              </div>

            </div>

          </section>

        </div>


        <!-- ====================================================
             ACTIONS
        ===================================================== -->
        <div
          class="flex flex-col-reverse gap-2 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end"
        >

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="updatePeriodModal"
            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-600 shadow-sm transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-4 focus:ring-slate-500/10 sm:w-auto"
          >
            Cancel
          </button>


          <!-- Save -->
          <button
            type="submit"
            class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/20 sm:w-auto"
          >
            <i class="fa-solid fa-floppy-disk text-xs"></i>
            Save Changes
          </button>

        </div>

      </form>

    </div>

  </div>
</div>