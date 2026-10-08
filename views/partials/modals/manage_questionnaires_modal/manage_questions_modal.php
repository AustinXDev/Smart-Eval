<!-- MANAGE QUESTIONS MODAL -->
<div id="manageQuestionsModal"
  class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- Modal -->
  <div
    class="
      flex w-full max-w-5xl
      max-h-[92vh]
      flex-col
      overflow-hidden
      rounded-2xl
      border border-slate-200
      bg-white
      shadow-2xl shadow-slate-900/20
    "
  >

    <!-- =====================================================
         MODAL HEADER
    ====================================================== -->
    <div
      class="
        flex shrink-0
        items-center justify-between
        border-b border-slate-200
        bg-white
        px-5 py-4
        sm:px-6
      "
    >

      <!-- Title -->
      <div class="flex items-center gap-3">

        <!-- Icon -->
        <div
          class="
            flex h-10 w-10
            shrink-0
            items-center justify-center
            rounded-xl
            bg-violet-50
            text-violet-600
          "
        >
          <i class="fas fa-list-check text-sm"></i>
        </div>

        <!-- Text -->
        <div>

          <h2
            class="
              text-base
              font-semibold
              tracking-tight
              text-slate-900
              sm:text-lg
            "
          >
            Manage Questions
          </h2>

          <p class="mt-0.5 text-xs text-slate-400">
            Manage questions for this evaluation set
          </p>

        </div>

      </div>


      <!-- Close -->
      <button
        type="button"
        data-close-modal="manageQuestionsModal"
        class="
          flex h-9 w-9
          items-center justify-center
          rounded-lg
          text-slate-400
          transition-all duration-200
          hover:bg-slate-100
          hover:text-slate-700
          focus:outline-none
          focus:ring-4
          focus:ring-slate-500/10
        "
        aria-label="Close modal"
      >
        <i class="fas fa-xmark text-sm"></i>
      </button>

    </div>


    <!-- =====================================================
         MODAL BODY
    ====================================================== -->
    <div
      class="
        flex min-h-0
        flex-1
        flex-col
        gap-5
        overflow-y-auto
        bg-slate-50/60
        p-5
        sm:p-6
      "
    >

      <!-- ===================================================
           ADD QUESTION CARD
      ==================================================== -->
      <section
        class="
          shrink-0
          rounded-2xl
          border border-slate-200
          bg-white
          shadow-sm
        "
      >

        <!-- Card Header -->
        <div
          class="
            flex flex-col
            gap-3
            border-b border-slate-100
            px-5 py-4
            sm:flex-row
            sm:items-center
            sm:justify-between
            sm:px-6
          "
        >

          <div class="flex items-center gap-3">

            <div
              class="
                flex h-9 w-9
                items-center justify-center
                rounded-lg
                bg-violet-50
                text-violet-600
              "
            >
              <i class="fas fa-circle-plus text-sm"></i>
            </div>

            <div>

              <h3 class="text-sm font-semibold text-slate-900">
                Add Question
              </h3>

              <p class="mt-0.5 text-xs text-slate-400">
                Create a new evaluation question
              </p>

            </div>

          </div>

        </div>


        <!-- Form -->
        <form
          id="addQuestionForm"
          action="POST"
          class="p-5 sm:p-6"
        >

          <input
            type="hidden"
            name="set_id"
            id="set_id_input"
          >


          <div
            class="
              grid
              grid-cols-1
              gap-4
              lg:grid-cols-3
            "
          >

            <!-- QUESTION -->
            <div class="lg:col-span-2">

              <label
                for="question_name"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-semibold
                  text-slate-600
                "
              >
                Question Text
                <span class="text-red-500">*</span>
              </label>

              <input
                type="text"
                id="question_name"
                name="question_name"
                placeholder="Enter evaluation question..."
                class="
                  h-11
                  w-full
                  rounded-xl
                  border border-slate-200
                  bg-white
                  px-3.5
                  text-sm
                  text-slate-700
                  placeholder:text-slate-400
                  outline-none
                  transition-all duration-200
                  hover:border-slate-300
                  focus:border-violet-400
                  focus:ring-4
                  focus:ring-violet-500/10
                "
                required
              >

            </div>


            <!-- CATEGORY -->
            <div>

              <label
                for="question_category"
                class="
                  mb-1.5
                  block
                  text-xs
                  font-semibold
                  text-slate-600
                "
              >
                Category
                <span class="text-red-500">*</span>
              </label>

              <div class="relative">

                <select
                  id="question_category"
                  name="categories"
                  class="
                    h-11
                    w-full
                    appearance-none
                    rounded-xl
                    border border-slate-200
                    bg-white
                    px-3.5
                    pr-10
                    text-sm
                    text-slate-700
                    outline-none
                    transition-all duration-200
                    hover:border-slate-300
                    focus:border-violet-400
                    focus:ring-4
                    focus:ring-violet-500/10
                  "
                  required
                >

                  <option
                    value=""
                    disabled
                    selected
                  >
                    Select Category
                  </option>

                  <option value="Punctuality">
                    Punctuality
                  </option>

                  <option value="Communication Skills">
                    Communication Skills
                  </option>

                  <option value="Subject Mastery">
                    Subject Mastery
                  </option>

                  <option value="Teaching Effectiveness">
                    Teaching Effectiveness
                  </option>

                  <option value="Professionalism">
                    Professionalism
                  </option>

                  <option value="Classroom Management">
                    Classroom Management
                  </option>

                  <option value="Assessment & Feedback">
                    Assessment & Feedback
                  </option>

                  <option value="Student Engagement">
                    Student Engagement
                  </option>

                  <option value="Fairness & Inclusivity">
                    Fairness & Inclusivity
                  </option>

                </select>

                <div
                  class="
                    pointer-events-none
                    absolute inset-y-0 right-3
                    flex items-center
                    text-slate-400
                  "
                >
                  <i class="fas fa-chevron-down text-[10px]"></i>
                </div>

              </div>

            </div>

          </div>


          <!-- FORM ACTIONS -->
          <div
            class="
              mt-5
              flex
              justify-end
              border-t border-slate-100
              pt-4
            "
          >

            <button
              type="submit"
              class="
                group
                inline-flex
                h-10
                items-center
                justify-center
                gap-2
                rounded-xl
                bg-violet-600
                px-4
                text-xs
                font-semibold
                text-white
                shadow-sm
                shadow-violet-600/20
                transition-all duration-200
                hover:-translate-y-0.5
                hover:bg-violet-700
                hover:shadow-md
                hover:shadow-violet-600/20
                focus:outline-none
                focus:ring-4
                focus:ring-violet-500/15
                active:translate-y-0
                active:scale-[0.98]
              "
            >

              <span
                class="
                  flex h-5 w-5
                  items-center justify-center
                  rounded-md
                  bg-white/15
                  transition-colors
                  group-hover:bg-white/20
                "
              >
                <i class="fas fa-plus text-[9px]"></i>
              </span>

              Add Question

            </button>

          </div>

        </form>

      </section>


      <!-- ===================================================
           QUESTION LIST
      ==================================================== -->
      <section
        class="
          flex
          min-h-0
          flex-1
          flex-col
          overflow-hidden
          rounded-2xl
          border border-slate-200
          bg-white
          shadow-sm
        "
      >

        <!-- List Header -->
        <div
          class="
            flex shrink-0
            flex-col
            gap-3
            border-b border-slate-200
            px-5 py-4
            sm:flex-row
            sm:items-center
            sm:justify-between
            sm:px-6
          "
        >

          <!-- Title -->
          <div class="flex items-center gap-3">

            <div
              class="
                flex h-9 w-9
                items-center justify-center
                rounded-lg
                bg-slate-50
                text-slate-500
              "
            >
              <i class="fas fa-list text-xs"></i>
            </div>

            <div>

              <h3 class="text-sm font-semibold text-slate-900">
                Question List
              </h3>

              <p class="mt-0.5 text-xs text-slate-400">
                Questions included in this evaluation set
              </p>

            </div>

          </div>


          <!-- Count -->
          <span
            id="questionCount"
            class="
              inline-flex
              w-fit
              items-center
              gap-1.5
              rounded-full
              bg-violet-50
              px-2.5 py-1
              text-[10px]
              font-semibold
              text-violet-600
            "
          >
            <span
              class="h-1.5 w-1.5 rounded-full bg-violet-500"
            ></span>

            <span id="questionCountNumber">
              0
            </span>

            Questions
          </span>

        </div>


        <!-- Table Wrapper -->
        <div
          class="
            min-h-0
            flex-1
            overflow-auto
          "
        >

          <table
          id="questionTable"
            class="
              w-full
              min-w-[650px]
              text-left
              text-sm
            "
          >

            <!-- Table Head -->
            <thead
              class="
                sticky top-0 z-10
                border-b border-slate-200
                bg-slate-50/95
                text-[10px]
                uppercase
                tracking-wider
                text-slate-500
                backdrop-blur-sm
              "
            >

              <tr>

                <th
                  class="
                    px-5 py-3.5
                    font-semibold
                  "
                >
                  Question
                </th>

                <th
                  class="
                    w-[180px]
                    px-5 py-3.5
                    font-semibold
                  "
                >
                  Category
                </th>

                <th
                  class="
                    w-[130px]
                    px-5 py-3.5
                    text-center
                    font-semibold
                  "
                >
                  Actions
                </th>

              </tr>

            </thead>


            <!-- Table Body -->
            <tbody
              id="questionList"
              class="divide-y divide-slate-100"
            >

              <!-- Fill by JS -->

            </tbody>

          </table>

        </div>


        <!-- Table Footer -->
        <div
          class="
            shrink-0
            border-t border-slate-100
            bg-slate-50/50
            px-5 py-3
            sm:px-6
          "
        >

          <div
            class="
              flex
              items-center
              justify-between
              text-[11px]
              text-slate-400
            "
          >

            <span>
              Evaluation questions
            </span>

            <span class="inline-flex items-center gap-1.5">

              <span
                class="h-1.5 w-1.5 rounded-full bg-violet-500"
              ></span>

              Standard Faculty Evaluation

            </span>

          </div>

        </div>

      </section>

    </div>

  </div>

</div>