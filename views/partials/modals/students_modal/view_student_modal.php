<!-- View Student Modal -->
<div
  id="viewStudentModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-3 py-5 backdrop-blur-sm sm:px-5"
  role="dialog"
  aria-modal="true"
  aria-labelledby="viewStudentTitle"
>

  <div
    class="flex max-h-[95vh] w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
  >

    <!-- ===================================================== -->
    <!-- HEADER -->
    <!-- ===================================================== -->
    <div class="relative border-b border-slate-200 bg-white px-5 py-5 sm:px-7">

      <!-- Violet Accent -->
      <div
        class="absolute left-0 top-0 h-full w-1 bg-violet-600"
      ></div>

      <div class="flex items-start justify-between gap-4">

        <div class="flex items-center gap-4">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
          >
            <i class="fas fa-user text-lg"></i>
          </div>

          <div>

            <h2
              id="viewStudentTitle"
              class="text-lg font-semibold tracking-tight text-slate-900"
            >
              Student Information
            </h2>

            <p class="mt-0.5 text-sm text-slate-500">
              View student profile details
            </p>

          </div>

        </div>


        <!-- Close -->
        <button
          type="button"
          data-close-modal="viewStudentModal"
          aria-label="Close modal"
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200"
        >
          <i class="fas fa-times text-sm"></i>
        </button>

      </div>

    </div>


    <!-- ===================================================== -->
    <!-- CONTENT -->
    <!-- ===================================================== -->
    <div class="flex-1 overflow-y-auto">

      <div class="space-y-5 p-5 sm:p-7">


        <!-- ================================================= -->
        <!-- PROFILE -->
        <!-- ================================================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/60 p-5"
        >

          <div
            class="flex flex-col items-center gap-5 sm:flex-row sm:items-center"
          >

            <!-- Avatar -->
            <div class="relative shrink-0">

              <!-- Soft Glow -->
              <div
                class="absolute inset-0 rounded-full bg-violet-500/20 blur-xl"
              ></div>

              <!-- Avatar Ring -->
              <div
                class="relative flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-2xl font-bold text-white shadow-lg ring-4 ring-white sm:h-24 sm:w-24 sm:text-3xl"
              >
                <span id="studentAvatar">
                  JD
                </span>
              </div>

            </div>


            <!-- Student Identity -->
            <div class="min-w-0 text-center sm:text-left">

              <h2
                id="studentName"
                class="truncate text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl"
              >
              </h2>

              <p
                id="studentId"
                class="mt-1 text-sm text-slate-500"
              >
              </p>


              <!-- Status -->
              <span
                id="studentStatus"
                class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
              >
              </span>

            </div>

          </div>

        </section>


        <!-- ================================================= -->
        <!-- STUDENT DETAILS -->
        <!-- ================================================= -->
        <section
          class="rounded-xl border border-slate-200 bg-white"
        >

          <!-- Section Header -->
          <div
            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
          >

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-600"
            >
              <i class="fas fa-id-card text-sm"></i>
            </div>

            <div>

              <h3 class="text-sm font-semibold text-slate-900">
                Student Details
              </h3>

              <p class="text-xs text-slate-500">
                Academic and contact information
              </p>

            </div>

          </div>


          <!-- Details Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2">


            <!-- Student ID -->
            <div
              class="border-b border-slate-100 px-5 py-4 sm:border-r"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Student ID
              </p>

              <p
                id="studentIdDetail"
                class="text-sm font-semibold text-slate-800"
              >
              </p>

            </div>


            <!-- Department -->
            <div
              class="border-b border-slate-100 px-5 py-4"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Department
              </p>

              <p
                id="studentDepartment"
                class="text-sm font-semibold text-slate-800"
              >
              </p>

            </div>


            <!-- Year Level -->
            <div
              class="border-b border-slate-100 px-5 py-4 sm:border-r"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Year Level
              </p>

              <p
                id="studentYearLevel"
                class="text-sm font-semibold text-slate-800"
              >
              </p>

            </div>


            <!-- Program -->
            <div
              class="border-b border-slate-100 px-5 py-4"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Program / Course
              </p>

              <span
                id="studentProgram"
                class="inline-flex items-center rounded-md bg-violet-50 px-2.5 py-1 text-xs font-medium text-violet-700 ring-1 ring-inset ring-violet-600/20"
              >
              </span>

            </div>


            <!-- Email -->
            <div
              class="border-b border-slate-100 px-5 py-4 sm:col-span-2"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Email Address
              </p>

              <p
                id="studentEmail"
                class="break-all text-sm font-medium text-violet-600"
              >
              </p>

            </div>


            <!-- Status -->
            <div
              class="px-5 py-4 sm:col-span-2"
            >

              <p class="mb-1 text-xs font-medium uppercase tracking-wide text-slate-400">
                Account Status
              </p>

              <span
                id="studentStatusDetail"
                class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
              >
              </span>

            </div>

          </div>

        </section>


        <!-- ================================================= -->
        <!-- ACCOUNT MANAGEMENT -->
        <!-- ================================================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/60 p-5"
        >

          <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
          >

            <!-- Description -->
            <div class="flex items-start gap-3">

              <div
                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600"
              >
                <i class="fas fa-lock text-sm"></i>
              </div>

              <div>

                <h3 class="text-sm font-semibold text-slate-900">
                  Account Management
                </h3>

                <p class="mt-0.5 text-xs text-slate-500">
                  Manage the student's account access
                </p>

              </div>

            </div>


            <!-- Reset Password -->
            <button
              id="resetPasswordBtn"
              type="button"
              class="inline-flex h-10 w-full items-center justify-center gap-2 rounded-lg border border-amber-200 bg-white px-4 text-sm font-medium text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 focus:outline-none focus:ring-4 focus:ring-amber-500/10 active:scale-[0.98] sm:w-auto"
            >

              <i class="fas fa-key text-xs"></i>

              <span>
                Reset Password
              </span>

            </button>

          </div>

        </section>


      </div>

    </div>


    <!-- ===================================================== -->
    <!-- FOOTER -->
    <!-- ===================================================== -->
    <div
      class="border-t border-slate-200 bg-white px-5 py-4 sm:px-7"
    >

      <div class="flex justify-end">

        <button
          type="button"
          data-close-modal="viewStudentModal"
          class="inline-flex h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-200 active:scale-[0.98]"
        >
          Close
        </button>

      </div>

    </div>

  </div>

</div>