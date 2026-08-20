<!-- Edit Student Modal -->
<div
  id="editStudentModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-3 py-5 backdrop-blur-sm sm:px-5"
>
  <div
    class="flex max-h-[95vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
  >

    <!-- ================= HEADER ================= -->
    <div class="relative border-b border-slate-200 bg-white px-6 py-5 sm:px-7">

      <!-- Accent -->
      <div class="absolute left-0 top-0 h-full w-1 bg-violet-600"></div>

      <div class="flex items-start justify-between gap-4">

        <div class="flex items-center gap-4">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
          >
            <i class="fas fa-user-edit text-lg"></i>
          </div>

          <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">
              Edit Student Information
            </h2>

            <p class="mt-0.5 text-sm text-slate-500">
              Update the student's profile and academic information
            </p>
          </div>

        </div>

        <!-- Close -->
        <button
          type="button"
          data-close-modal="editStudentModal"
          aria-label="Close modal"
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200"
        >
          <i class="fas fa-times text-sm"></i>
        </button>

      </div>
    </div>


    <!-- ================= CONTENT ================= -->
    <div class="flex-1 overflow-y-auto">

      <div class="space-y-5 p-5 sm:p-7">


        <!-- ================= STUDENT PROFILE ================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/50 p-5"
        >

          <div
            class="flex flex-col items-center gap-5 sm:flex-row"
          >

            <!-- Avatar -->
            <div class="relative shrink-0">

              <!-- Glow -->
              <div
                class="absolute inset-0 rounded-full bg-violet-500/20 blur-xl"
              ></div>

              <div
                id="editStudentAvatar"
                class="relative flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-violet-500 to-indigo-600 text-2xl font-bold text-white shadow-lg ring-4 ring-white"
              >
                JD
              </div>

            </div>


            <!-- Student Details -->
            <div class="min-w-0 text-center sm:text-left">

              <h2
                id="editStudentName"
                class="truncate text-xl font-semibold tracking-tight text-slate-900"
              >
                Juan Dela Cruz
              </h2>

              <p
                id="editStudentId"
                class="mt-1 text-sm text-slate-500"
              >
                Student ID: S10005
              </p>

              <!-- Status -->
              <span
                id="editStudentStatus"
                class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
              >
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Active
              </span>

            </div>

          </div>

        </section>


        <!-- ================= FORM ================= -->
        <form method="POST" id="editStudentForm">

          <!-- Student Information -->
          <section
            class="rounded-xl border border-slate-200 bg-slate-50/50 p-5"
          >

            <!-- Section Header -->
            <div class="mb-5 flex items-center gap-3">

              <div
                class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-600"
              >
                <i class="fas fa-id-card text-sm"></i>
              </div>

              <div>
                <h3 class="text-sm font-semibold text-slate-900">
                  Student Information
                </h3>

                <p class="text-xs text-slate-500">
                  Update the student's account information
                </p>
              </div>

            </div>


            <!-- Hidden Student ID -->
            <input
              type="hidden"
              name="old_student_id"
              id="oldStudentIdInput"
            >


            <!-- Fields -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">


              <!-- Student ID -->
              <div>

                <label
                  for="editStudentIDInput"
                  class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                  Student ID
                  <span class="text-red-500">*</span>
                </label>

                <div class="relative">

                  <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                  >
                    <i class="fas fa-id-badge text-sm"></i>
                  </div>

                  <input
                    id="editStudentIDInput"
                    type="text"
                    name="student_id"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                    required
                  >

                </div>

              </div>


              <!-- Year Level -->
              <div>

                <label
                  for="editStudentYearLevel"
                  class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                  <?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>

                  <span class="text-red-500">*</span>
                </label>

                <select
                  name="year_level"
                  id="editStudentYearLevel"
                  class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                  required
                >

                  <?php if ($department === 'college') { ?>

                    <option disabled selected>
                      Select Year Level
                    </option>

                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>

                  <?php } else { ?>

                    <option disabled selected>
                      Select Grade Level
                    </option>

                    <option value="11">Grade 11</option>
                    <option value="12">Grade 12</option>

                  <?php } ?>

                </select>

              </div>


              <!-- Full Name -->
              <div>

                <label
                  for="editStudentNameInput"
                  class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                  Full Name
                  <span class="text-red-500">*</span>
                </label>

                <div class="relative">

                  <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                  >
                    <i class="fas fa-user text-sm"></i>
                  </div>

                  <input
                    id="editStudentNameInput"
                    type="text"
                    name="full_name"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                    required
                  >

                </div>

              </div>


              <!-- Email -->
              <div>

                <label
                  for="editStudentEmailInput"
                  class="mb-1.5 block text-sm font-medium text-slate-700"
                >
                  Email Address
                  <span class="text-red-500">*</span>
                </label>

                <div class="relative">

                  <div
                    class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                  >
                    <i class="fas fa-envelope text-sm"></i>
                  </div>

                  <input
                    id="editStudentEmailInput"
                    type="email"
                    name="email"
                    value="example@gmail.com"
                    class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                    required
                  >

                </div>

              </div>


              <!-- Program -->
              <div class="sm:col-span-2">

                <label
                  for="editProgramSelect"
                  class="mb-1.5 block text-sm font-medium text-slate-700"
                >

                  <?php echo ($department === 'college') ? 'Course' : 'Program'; ?>

                  <span class="text-red-500">*</span>

                </label>

                <select
                  id="editProgramSelect"
                  name="program_id"
                  class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                  required
                >

                  <!-- Option value will be populated dynamically with JS -->

                </select>

              </div>

            </div>

          </section>


          <!-- ================= ACTIONS ================= -->
          <div
            class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
          >

            <!-- Cancel -->
            <button
              type="button"
              data-close-modal="editStudentModal"
              class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-200 active:scale-[0.98]"
            >
              Cancel
            </button>


            <!-- Save -->
            <button
              id="saveBtn"
              type="submit"
              class="group inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-violet-600 px-6 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/20 active:scale-[0.98]"
            >

              <span>
                Save Changes
              </span>

              <!-- Loader -->
              <span
                class="hidden h-4 w-4 rounded-full border-2 border-white border-t-transparent animate-spin group-[.loading]:inline-block"
              ></span>

            </button>

          </div>

        </form>

      </div>

    </div>

  </div>
</div>