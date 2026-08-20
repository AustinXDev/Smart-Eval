<!-- Add Student Modal -->
<div
  id="addStudentModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-3 py-6 backdrop-blur-sm sm:px-5"
>
  <div
    class="flex max-h-[95vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
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
            <i class="fas fa-user-plus text-lg"></i>
          </div>

          <!-- Title -->
          <div>
            <h2 class="text-lg font-semibold tracking-tight text-slate-900">
              Add New Student
            </h2>

            <p class="mt-0.5 text-sm text-slate-500">
              Add a student to the evaluation system
            </p>
          </div>

        </div>

      </div>
    </div>


    <!-- ================= FORM ================= -->
    <form
      id="addStudentForm"
      class="flex flex-1 flex-col overflow-y-auto"
      method="POST"
    >

      <div class="space-y-5 p-5 sm:p-7">


        <!-- ================= BASIC INFORMATION ================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/50 p-5"
        >

          <!-- Section Header -->
          <div class="mb-5 flex items-center gap-3">

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-600"
            >
              <i class="fas fa-user text-sm"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-900">
                Basic Information
              </h3>

              <p class="text-xs text-slate-500">
                Enter the student's personal information
              </p>
            </div>

          </div>


          <!-- Fields -->
          <div class="space-y-4">

            <!-- Student ID -->
            <div>

              <label
                for="student_id"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                Student ID
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <div
                  class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400"
                >
                  <i class="fas fa-id-card text-sm"></i>
                </div>

                <input
                  id="student_id"
                  type="text"
                  name="student_id"
                  placeholder="e.g., 00-0000"
                  class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                  required
                />

              </div>

            </div>


            <!-- Full Name -->
            <div>

              <label
                for="full_name"
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
                  id="full_name"
                  type="text"
                  name="full_name"
                  placeholder="First name, Middle initials, Last name"
                  class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                  required
                />

              </div>

            </div>


            <!-- Email -->
            <div>

              <label
                for="email"
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
                  id="email"
                  type="email"
                  name="email"
                  placeholder="example@gmail.com"
                  class="h-11 w-full rounded-lg border border-slate-200 bg-white pl-10 pr-3 text-sm text-slate-800 outline-none transition placeholder:text-slate-400 hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                  required
                />

              </div>

            </div>

          </div>
        </section>


        <!-- ================= ACADEMIC PLACEMENT ================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/50 p-5"
        >

          <!-- Section Header -->
          <div class="mb-5 flex items-center gap-3">

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-600"
            >
              <i class="fas fa-graduation-cap text-sm"></i>
            </div>

            <div>
              <h3 class="text-sm font-semibold text-slate-900">
                Academic Placement
              </h3>

              <p class="text-xs text-slate-500">
                Assign the student's academic information
              </p>
            </div>

          </div>


          <!-- Academic Fields -->
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">

            <!-- Year / Grade -->
            <div>

              <label
                for="year"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >
                <?php echo ($department === 'college') ? 'Year Level' : 'Grade Level'; ?>

                <span class="text-red-500">*</span>
              </label>

              <select
                id="year"
                name="year"
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


            <!-- Program -->
            <div>

              <label
                for="programSelect"
                class="mb-1.5 block text-sm font-medium text-slate-700"
              >

                <?php echo ($department === 'college') ? 'Course' : 'Program'; ?>

                <span class="text-red-500">*</span>

              </label>

              <select
                name="program"
                id="programSelect"
                class="h-11 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-slate-700 outline-none transition hover:border-slate-300 focus:border-violet-500 focus:ring-4 focus:ring-violet-500/10"
                required
              >

                <option disabled selected>
                  Select Program
                </option>

                <!-- JS fill -->

              </select>

            </div>

          </div>

        </section>

      </div>


      <!-- ================= FOOTER ================= -->
      <div
        class="border-t border-slate-200 bg-white px-5 py-4 sm:px-7"
      >

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="addStudentModal"
            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-200 active:scale-[0.98]"
          >
            Cancel
          </button>


          <!-- Add Student -->
          <button
            type="submit"
            class="cursor-pointer inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md hover:shadow-violet-600/20 focus:outline-none focus:ring-4 focus:ring-violet-500/20 active:scale-[0.98]"
          >

            <i class="fas fa-user-plus text-xs"></i>

            <span>Add Student</span>

          </button>

        </div>

      </div>

    </form>

  </div>
</div>