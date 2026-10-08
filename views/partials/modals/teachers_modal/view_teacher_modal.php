<!-- View Teacher Modal -->
<div
  id="viewDetails"
  class="fixed inset-0 z-50 hidden flex items-center justify-center
         bg-slate-950/60 backdrop-blur-sm
         p-3 sm:p-5">

  <!-- Modal -->
  <div
    class="relative flex max-h-[95vh] w-full max-w-3xl flex-col
           overflow-hidden rounded-2xl bg-slate-50
           shadow-2xl ring-1 ring-black/5">


    <!-- ================================================= -->
    <!-- HEADER -->
    <!-- ================================================= -->

    <div
      class="flex items-center justify-between
             border-b border-slate-200
             bg-white px-5 py-4 sm:px-6">

      <div class="flex items-center gap-3">

        <!-- Icon -->
        <div
          class="flex h-10 w-10 items-center justify-center
                 rounded-xl bg-purple-100">

          <i class="fas fa-user text-purple-600"></i>

        </div>

        <div>

          <h2
            class="text-base font-semibold text-slate-800 sm:text-lg">
            Teacher Information
          </h2>

          <p class="text-xs text-slate-500">
            View teacher profile and teaching assignments
          </p>

        </div>

      </div>


      <!-- Close -->
      <button
        type="button"
        data-close-modal="viewDetails"
        class="flex h-9 w-9 items-center justify-center
               rounded-lg text-slate-400
               transition-all duration-200
               hover:bg-slate-100 hover:text-slate-700
               focus:outline-none
               focus:ring-2 focus:ring-purple-500/30"
        aria-label="Close modal">

        <i class="fas fa-times text-sm"></i>

      </button>

    </div>


    <!-- ================================================= -->
    <!-- SCROLLABLE CONTENT -->
    <!-- ================================================= -->

    <div class="overflow-y-auto">

      <div class="p-4 sm:p-6">


        <!-- ================================================= -->
        <!-- PROFILE SECTION -->
        <!-- ================================================= -->

        <div
          class="rounded-2xl border border-slate-200
                 bg-white p-5 shadow-sm sm:p-6">

          <div
            class="flex flex-col items-center gap-5
                   sm:flex-row sm:items-center">


            <!-- Avatar -->
            <div class="relative shrink-0">

              <!-- Glow -->
              <div
                class="absolute inset-0 rounded-full
                       bg-purple-400/20 blur-xl">
              </div>


              <!-- Image -->
              <div
                class="relative h-28 w-28 rounded-full
                       bg-gradient-to-br from-purple-500
                       via-indigo-500 to-purple-600
                       p-[3px] shadow-lg">

                <div
                  class="h-full w-full rounded-full
                         bg-white p-1">

                  <img
                    src="<?= BASE_URL ?>uploads/teachers/default_teacher.png"
                    id="teacherViewPhoto"
                    class="h-full w-full rounded-full object-cover"
                    alt="Teacher photo">

                </div>

              </div>


              <!-- Status -->
              <div
                class="absolute bottom-1 right-1
                       flex h-7 w-7 items-center justify-center
                       rounded-full border-2 border-white
                       bg-green-500 text-white">

                <i class="fas fa-check text-[10px]"></i>

              </div>

            </div>


            <!-- Teacher Information -->
            <div
              class="flex-1 text-center sm:text-left">

              <p
                class="mb-1 text-xs font-medium uppercase
                       tracking-wider text-purple-600">
                Faculty Profile
              </p>

              <h1
                id="name"
                class="text-xl font-bold tracking-tight
                       text-slate-800 sm:text-2xl">
                Juan Dela Cruz
              </h1>


              <div
                class="mt-2 flex flex-col
                       sm:flex-row sm:items-center
                       gap-1.5 sm:gap-3">

                <p class="text-sm text-slate-500">

                  <i class="fas fa-id-badge mr-1.5
                            text-slate-400"></i>

                  Employee ID:

                  <span
                    id="id"
                    class="font-medium text-slate-700">
                    S10005
                  </span>

                </p>


                <span
                  class="hidden sm:block text-slate-300">
                  •
                </span>


                <p
                  id="department"
                  class="text-sm font-medium
                         text-purple-600">
                </p>

              </div>


              <!-- Status Badge -->
              <div class="mt-3">

                <span
                  class="inline-flex items-center gap-1.5
                         rounded-full
                         border border-green-100
                         bg-green-50
                         px-2.5 py-1
                         text-[11px] font-semibold
                         text-green-700">

                  <span
                    class="h-1.5 w-1.5
                           rounded-full bg-green-500">
                  </span>

                  Active Faculty

                </span>

              </div>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- TEACHER DETAILS -->
        <!-- ================================================= -->

        <div
          class="mt-5 rounded-2xl border border-slate-200
                 bg-white p-5 shadow-sm sm:p-6">


          <!-- Section Header -->
          <div
            class="mb-5 flex items-center gap-3
                   border-b border-slate-100 pb-4">

            <div
              class="flex h-9 w-9 items-center justify-center
                     rounded-lg bg-purple-100">

              <i
                class="fas fa-address-card
                       text-sm text-purple-600">
              </i>

            </div>

            <div>

              <h3
                class="text-sm font-semibold text-slate-800">
                Contact Information
              </h3>

              <p class="text-xs text-slate-500">
                Teacher account information
              </p>

            </div>

          </div>


          <!-- Email -->
          <div
            class="flex items-center gap-4
                   rounded-xl border border-slate-200
                   bg-slate-50 p-4
                   transition-all duration-200
                   hover:border-purple-200
                   hover:bg-purple-50/50">

            <div
              class="flex h-10 w-10 shrink-0
                     items-center justify-center
                     rounded-lg bg-purple-100
                     text-purple-600">

              <i class="fas fa-envelope"></i>

            </div>

            <div class="min-w-0">

              <p
                class="text-[11px] font-semibold
                       uppercase tracking-wide
                       text-slate-400">
                Email Address
              </p>

              <p
                id="email"
                class="mt-0.5 break-all
                       text-sm font-medium
                       text-slate-700">
                example@gmail.com
              </p>

            </div>

          </div>

        </div>


        <!-- ================================================= -->
        <!-- HANDLE LEVEL & PROGRAM -->
        <!-- ================================================= -->

        <div
          class="mt-5 rounded-2xl border border-slate-200
                 bg-white p-5 shadow-sm sm:p-6">


          <!-- Section Header -->
          <div
            class="mb-5 flex items-center gap-3
                   border-b border-slate-100 pb-4">

            <div
              class="flex h-9 w-9 items-center justify-center
                     rounded-lg bg-purple-100">

              <i
                class="fas fa-graduation-cap
                       text-sm text-purple-600">
              </i>

            </div>

            <div>

              <h3
                class="text-sm font-semibold text-slate-800">
                Handle Level & Program
              </h3>

              <p class="text-xs text-slate-500">
                Assign teaching levels and programs
              </p>

            </div>

          </div>


          <!-- ================================================= -->
          <!-- ADD HANDLE FORM -->
          <!-- ================================================= -->

          <form
            id="addHandleForm"
            class="mb-5 rounded-xl
                   border border-slate-200
                   bg-slate-50 p-4"
            method="POST">

            <input
              type="hidden"
              name="teacher_id"
              id="handle_teacher_id">

            <input
              type="hidden"
              name="department"
              value="<?php echo htmlspecialchars($department); ?>">


            <div
              class="grid grid-cols-1
                     gap-3 md:grid-cols-[1fr_1fr_auto]">


              <!-- Level -->
              <div>

                <label
                  class="mb-1.5 block text-xs
                         font-medium text-slate-600">

                  <?php
                    echo ($department === 'college')
                      ? 'Year Level'
                      : 'Grade Level';
                    ?>

                </label>

                <select
                  class="w-full rounded-xl
                         border border-slate-200
                         bg-white px-3 py-2.5
                         text-sm text-slate-700
                         outline-none
                         transition-all duration-200
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:ring-4
                         focus:ring-purple-500/10"
                  name="level"
                  required>

                  <?php if ($department === 'college') { ?>

                    <option disabled selected hidden>
                      Select Year Level
                    </option>

                    <option value="1">
                      1st Year
                    </option>

                    <option value="2">
                      2nd Year
                    </option>

                    <option value="3">
                      3rd Year
                    </option>

                    <option value="4">
                      4th Year
                    </option>

                  <?php } else { ?>

                    <option disabled selected>
                      Select Grade Level
                    </option>

                    <option value="11">
                      Grade 11
                    </option>

                    <option value="12">
                      Grade 12
                    </option>

                  <?php } ?>

                </select>

              </div>


              <!-- Program -->
              <div>

                <label
                  class="mb-1.5 block text-xs
                         font-medium text-slate-600">

                  Program

                </label>

                <select
                  class="w-full rounded-xl
                         border border-slate-200
                         bg-white px-3 py-2.5
                         text-sm text-slate-700
                         outline-none
                         transition-all duration-200
                         hover:border-slate-300
                         focus:border-purple-500
                         focus:ring-4
                         focus:ring-purple-500/10"
                  name="program"
                  id="programSelect"
                  required>

                  <!-- JS fills this -->

                </select>

              </div>


              <!-- Add Button -->
              <div class="flex items-end">

                <button
                  type="submit"
                  class="inline-flex w-full
                         items-center justify-center
                         gap-2 rounded-xl
                         bg-purple-600 px-5 py-2.5
                         text-sm font-medium text-white
                         shadow-sm
                         transition-all duration-200
                         hover:bg-purple-700
                         hover:shadow-md
                         active:scale-[0.98]
                         md:w-auto">

                  <i class="fas fa-plus text-xs"></i>

                  Add

                </button>

              </div>

            </div>

          </form>


          <!-- ================================================= -->
          <!-- ASSIGNMENT TABLE -->
          <!-- ================================================= -->

          <div
            id="handleTable"
            class="overflow-hidden
                   rounded-xl border border-slate-200">

            <div
              class="max-h-[220px]
                     overflow-x-auto overflow-y-auto">

              <table
                class="min-w-full text-sm">

                <thead
                  class="sticky top-0 z-10
                         border-b border-slate-200
                         bg-slate-50">

                  <tr>

                    <th
                      class="px-4 py-3 text-left
                             text-[11px] font-semibold
                             uppercase tracking-wide
                             text-slate-500">
                      Level
                    </th>

                    <th
                      class="px-4 py-3 text-left
                             text-[11px] font-semibold
                             uppercase tracking-wide
                             text-slate-500">
                      Program
                    </th>

                    <th
                      class="px-4 py-3 text-right
                             text-[11px] font-semibold
                             uppercase tracking-wide
                             text-slate-500">
                      Action
                    </th>

                  </tr>

                </thead>

                <tbody
                  class="divide-y divide-slate-100
                         bg-white">

                  <!-- JS Content -->

                </tbody>

              </table>

            </div>

          </div>

        </div>


      </div>

    </div>

  </div>

</div>