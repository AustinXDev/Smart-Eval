<!-- View Student Modal -->
<div
  id="viewStudentModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-3 py-5 backdrop-blur-sm sm:px-5"
  role="dialog"
  aria-modal="true"
  aria-labelledby="viewStudentTitle"
>

<!-- ==========================================================
       MODAL CONTAINER
  =========================================================== -->
  <div
    class="
      relative flex max-h-[94vh] w-full max-w-3xl
      flex-col overflow-hidden
      rounded-3xl
      border border-white/60
      bg-white
      shadow-2xl shadow-slate-950/20
    "
  >

    <!-- ========================================================
         TOP ACCENT
    ========================================================= -->
    <div
      class="
        absolute inset-x-0 top-0 h-1
        bg-gradient-to-r
        from-violet-600 via-indigo-500 to-violet-400
      "
    ></div>


    <!-- ========================================================
         HEADER
    ========================================================= -->
    <div
      class="
        shrink-0 border-b border-slate-100
        bg-white px-5 pb-5 pt-6
        sm:px-7
      "
    >

      <div class="flex items-start justify-between gap-4">

        <!-- Header Identity -->
        <div class="flex min-w-0 items-center gap-4">

          <!-- Icon -->
          <div
            class="
              relative flex h-11 w-11 shrink-0
              items-center justify-center
              rounded-2xl
              bg-violet-50
              text-violet-600
              ring-1 ring-violet-100
            "
          >

            <div
              class="
                absolute inset-0 rounded-2xl
                bg-violet-400/10 blur-md
              "
            ></div>

            <i class="fas fa-user relative text-base"></i>

          </div>


          <!-- Title -->
          <div class="min-w-0">

            <div
              class="
                mb-0.5 flex items-center gap-2
              "
            >

              <span
                class="
                  text-[10px] font-bold uppercase
                  tracking-[0.14em]
                  text-violet-600
                "
              >
                Student Profile
              </span>

            </div>

            <h2
              id="viewStudentTitle"
              class="
                truncate text-lg font-bold
                tracking-tight text-slate-900
                sm:text-xl
              "
            >
              Student Information
            </h2>

            <p
              class="
                mt-0.5 text-xs text-slate-500
                sm:text-sm
              "
            >
              View profile and academic details
            </p>

          </div>

        </div>


        <!-- Close -->
        <button
          type="button"
          data-close-modal="viewStudentModal"
          aria-label="Close modal"
          class="
            flex h-9 w-9 shrink-0
            items-center justify-center
            rounded-xl
            text-slate-400
            transition-all duration-150
            hover:bg-slate-100
            hover:text-slate-700
            focus:outline-none
            focus:ring-4
            focus:ring-violet-500/10
            active:scale-95
          "
        >
          <i class="fas fa-times text-sm"></i>
        </button>

      </div>

    </div>


    <!-- ========================================================
         CONTENT
    ========================================================= -->
    <div class="min-h-0 flex-1 overflow-y-auto">

      <div
        class="
          space-y-5
          p-5
          sm:p-7
        "
      >


        <!-- ====================================================
             PROFILE HERO
        ===================================================== -->
        <section
          class="
            relative overflow-hidden
            rounded-2xl
            border border-violet-100
            bg-gradient-to-br
            from-violet-50/80
            via-white
            to-indigo-50/50
            p-5
            sm:p-6
          "
        >

          <!-- Decorative Glow -->
          <div
            class="
              pointer-events-none absolute
              -right-12 -top-12
              h-32 w-32
              rounded-full
              bg-violet-300/20
              blur-3xl
            "
          ></div>

          <div
            class="
              pointer-events-none absolute
              -bottom-16 -left-10
              h-32 w-32
              rounded-full
              bg-indigo-300/10
              blur-3xl
            "
          ></div>


          <div
            class="
              relative flex flex-col
              items-center gap-5
              sm:flex-row
              sm:items-center
            "
          >

            <!-- Avatar -->
            <div class="relative shrink-0">

              <!-- Glow -->
              <div
                class="
                  absolute inset-0
                  rounded-full
                  bg-violet-500/20
                  blur-xl
                "
              ></div>

              <!-- Avatar -->
              <div
                class="
                  relative flex h-20 w-20
                  items-center justify-center
                  rounded-full
                  bg-gradient-to-br
                  from-violet-600
                  to-indigo-600
                  text-2xl font-bold
                  text-white
                  shadow-lg
                  shadow-violet-600/20
                  ring-4 ring-white
                  sm:h-24 sm:w-24
                  sm:text-3xl
                "
              >
                <span id="studentAvatar">
                  JD
                </span>
              </div>

            </div>


            <!-- Identity -->
            <div
              class="
                min-w-0 flex-1
                text-center
                sm:text-left
              "
            >

              <p
                class="
                  text-[10px] font-bold uppercase
                  tracking-[0.14em]
                  text-violet-500
                "
              >
                Student
              </p>

              <h2
                id="studentName"
                class="
                  mt-1 truncate
                  text-xl font-bold
                  tracking-tight text-slate-900
                  sm:text-2xl
                "
              >
              </h2>

              <div
                class="
                  mt-1 flex items-center
                  justify-center gap-2
                  text-sm text-slate-500
                  sm:justify-start
                "
              >

                <i class="fas fa-id-badge text-xs text-slate-400"></i>

                <span id="studentId"></span>

              </div>


              <!-- Status -->
              <span
                id="studentStatus"
                class="
                  mt-3 inline-flex
                  items-center gap-1.5
                  rounded-full
                  bg-emerald-50
                  px-3 py-1.5
                  text-xs font-semibold
                  text-emerald-700
                  ring-1 ring-inset
                  ring-emerald-600/15
                "
              >
              </span>

            </div>

          </div>

        </section>


        <!-- ====================================================
             STUDENT DETAILS
        ===================================================== -->
        <section
          class="
            overflow-hidden
            rounded-2xl
            border border-slate-200
            bg-white
          "
        >

          <!-- Section Header -->
          <div
            class="
              flex items-center gap-3
              border-b border-slate-100
              px-5 py-4
            "
          >

            <div
              class="
                flex h-9 w-9 shrink-0
                items-center justify-center
                rounded-xl
                bg-violet-50
                text-violet-600
                ring-1 ring-violet-100
              "
            >
              <i class="fas fa-id-card text-sm"></i>
            </div>

            <div>

              <h3
                class="
                  text-sm font-bold
                  text-slate-900
                "
              >
                Academic Details
              </h3>

              <p
                class="
                  mt-0.5 text-xs
                  text-slate-400
                "
              >
                Student academic and contact information
              </p>

            </div>

          </div>


          <!-- Details -->
          <div
            class="
              grid grid-cols-1
              sm:grid-cols-2
            "
          >

            <!-- Student ID -->
            <div
              class="
                border-b border-slate-100
                px-5 py-4
                sm:border-r
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Student ID
              </p>

              <p
                id="studentIdDetail"
                class="
                  text-sm font-semibold
                  text-slate-800
                "
              >
              </p>

            </div>


            <!-- Department -->
            <div
              class="
                border-b border-slate-100
                px-5 py-4
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Department
              </p>

              <p
                id="studentDepartment"
                class="
                  text-sm font-semibold
                  text-slate-800
                "
              >
              </p>

            </div>


            <!-- Year Level -->
            <div
              class="
                border-b border-slate-100
                px-5 py-4
                sm:border-r
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Year Level
              </p>

              <p
                id="studentYearLevel"
                class="
                  text-sm font-semibold
                  text-slate-800
                "
              >
              </p>

            </div>


            <!-- Program -->
            <div
              class="
                border-b border-slate-100
                px-5 py-4
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Program / Course
              </p>

              <span
                id="studentProgram"
                class="
                  inline-flex max-w-full
                  items-center gap-1.5
                  rounded-lg
                  bg-violet-50
                  px-2.5 py-1.5
                  text-xs font-semibold
                  text-violet-700
                  ring-1 ring-inset
                  ring-violet-600/10
                "
              >
              </span>

            </div>


            <!-- Email -->
            <div
              class="
                border-b border-slate-100
                px-5 py-4
                sm:col-span-2
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Email Address
              </p>

              <div
                class="
                  flex min-w-0 items-center gap-2
                "
              >

                <i
                  class="
                    fas fa-envelope
                    shrink-0 text-xs
                    text-violet-400
                  "
                ></i>

                <p
                  id="studentEmail"
                  class="
                    min-w-0 break-all
                    text-sm font-medium
                    text-violet-600
                  "
                >
                </p>

              </div>

            </div>


            <!-- Account Status -->
            <div
              class="
                px-5 py-4
                sm:col-span-2
              "
            >

              <p
                class="
                  mb-1.5 text-[10px]
                  font-bold uppercase
                  tracking-wider
                  text-slate-400
                "
              >
                Account Status
              </p>

              <span
                id="studentStatusDetail"
                class="
                  inline-flex
                  items-center gap-1.5
                  rounded-full
                  bg-emerald-50
                  px-2.5 py-1.5
                  text-xs font-semibold
                  text-emerald-700
                  ring-1 ring-inset
                  ring-emerald-600/15
                "
              >
              </span>

            </div>

          </div>

        </section>


        <!-- ====================================================
             ACCOUNT MANAGEMENT
        ===================================================== -->
        <section
          class="
            rounded-2xl
            border border-amber-200/70
            bg-amber-50/40
            p-5
            sm:p-5
          "
        >

          <div
            class="
              flex flex-col gap-4
              sm:flex-row
              sm:items-center
              sm:justify-between
            "
          >

            <!-- Description -->
            <div class="flex items-start gap-3">

              <div
                class="
                  flex h-10 w-10 shrink-0
                  items-center justify-center
                  rounded-xl
                  bg-white
                  text-amber-600
                  shadow-sm
                  ring-1 ring-amber-100
                "
              >
                <i class="fas fa-shield-halved text-sm"></i>
              </div>

              <div>

                <h3
                  class="
                    text-sm font-bold
                    text-slate-900
                  "
                >
                  Account Security
                </h3>

                <p
                  class="
                    mt-0.5 max-w-md
                    text-xs leading-5
                    text-slate-500
                  "
                >
                  Reset the student's password if they
                  need help accessing their account.
                </p>

              </div>

            </div>


            <!-- Reset Password -->
            <button
              id="resetPasswordBtn"
              type="button"
              class="
                inline-flex h-10
                w-full shrink-0
                items-center justify-center
                gap-2
                rounded-xl
                border border-amber-200
                bg-white
                px-4
                text-sm font-semibold
                text-amber-700
                shadow-sm
                transition-all duration-150
                hover:border-amber-300
                hover:bg-amber-100/60
                hover:shadow
                focus:outline-none
                focus:ring-4
                focus:ring-amber-500/10
                active:scale-[0.98]
                sm:w-auto
              "
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


    <!-- ========================================================
         FOOTER
    ========================================================= -->
    <div
      class="
        shrink-0
        border-t border-slate-100
        bg-slate-50/70
        px-5 py-4
        sm:px-7
      "
    >

      <div
        class="
          flex items-center
          justify-between gap-3
        "
      >

        <!-- Footer Hint -->
        <p
          class="
            hidden text-xs
            text-slate-400
            sm:block
          "
        >
          Student profile information
        </p>


        <!-- Close -->
        <button
          type="button"
          data-close-modal="viewStudentModal"
          class="
            inline-flex h-10
            w-full sm:w-auto
            items-center justify-center
            rounded-xl
            border border-slate-200
            bg-white
            px-5
            text-sm font-semibold
            text-slate-700
            shadow-sm
            transition-all duration-150
            hover:border-slate-300
            hover:bg-slate-50
            hover:text-slate-900
            focus:outline-none
            focus:ring-4
            focus:ring-violet-500/10
            active:scale-[0.98]
          "
        >
          Close
        </button>

      </div>

    </div>

  </div>

</div>