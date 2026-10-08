<!-- EDIT QUESTION MODAL -->
<div id="editQuestionModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

   <!-- =======================================================
       MODAL CONTAINER
  ======================================================== -->
  <div
    class="
      w-full max-w-xl
      overflow-hidden
      rounded-2xl
      border border-slate-200
      bg-white
      shadow-2xl shadow-slate-900/20
    "
  >

    <!-- =====================================================
         HEADER
    ====================================================== -->
    <div
      class="
        flex items-start justify-between
        border-b border-slate-200
        bg-white
        px-5 py-5
        sm:px-6
      "
    >

      <div class="flex items-center gap-3">

        <!-- Icon -->
        <div
          class="
            flex h-10 w-10 shrink-0
            items-center justify-center
            rounded-xl
            bg-violet-50
            text-violet-600
          "
        >
          <i class="fa-solid fa-pen-to-square text-sm"></i>
        </div>

        <!-- Title -->
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
            Edit Question
          </h2>

          <p class="mt-0.5 text-xs text-slate-400">
            Update the evaluation question and category
          </p>

        </div>

      </div>


      <!-- Close -->
      <button
        type="button"
        data-close-modal="editQuestionModal"
        class="
          flex h-8 w-8
          items-center justify-center
          rounded-lg
          text-slate-400
          transition-all duration-200
          hover:bg-slate-100
          hover:text-slate-700
          focus:outline-none
          focus:ring-2
          focus:ring-violet-500/20
        "
        aria-label="Close modal"
      >
        <i class="fa-solid fa-xmark text-sm"></i>
      </button>

    </div>


    <!-- =====================================================
         BODY
    ====================================================== -->
    <div class="p-5 sm:p-6">

      <form
        id="editQuestionForm"
        class="space-y-5"
      >

        <!-- Hidden Fields -->
        <input
          type="hidden"
          id="question_id_input"
          name="question_id"
        >

        <input
          type="hidden"
          id="set_input"
          name="set_id"
        >


        <!-- =================================================
             CONTEXT INFORMATION
        ================================================== -->
        <div
          class="
            flex items-start gap-3
            rounded-xl
            border border-violet-100
            bg-violet-50/70
            px-4 py-3.5
          "
        >

          <div
            class="
              flex h-8 w-8 shrink-0
              items-center justify-center
              rounded-lg
              bg-white
              text-violet-600
              shadow-sm
            "
          >
            <i class="fa-solid fa-circle-info text-xs"></i>
          </div>

          <div class="min-w-0">

            <p
              class="
                text-xs
                font-semibold
                text-violet-800
              "
            >
              Editing evaluation question
            </p>

            <p
              class="
                mt-0.5
                text-xs
                leading-relaxed
                text-violet-600
              "
            >
              Changes will update this question within the selected
              questionnaire set.
            </p>

          </div>

        </div>


        <!-- =================================================
             QUESTION TEXT
        ================================================== -->
        <div class="space-y-2">

          <div class="flex items-center justify-between">

            <label
              for="editQuestionText"
              class="
                text-xs
                font-semibold
                text-slate-700
              "
            >
              Question Text
            </label>

            <span
              class="
                text-[10px]
                font-medium
                uppercase
                tracking-wide
                text-slate-400
              "
            >
              Required
            </span>

          </div>


          <div class="relative">

            <div
              class="
                pointer-events-none
                absolute left-3.5 top-1/2
                -translate-y-1/2
                text-slate-400
              "
            >
              <i class="fa-regular fa-circle-question text-xs"></i>
            </div>

            <input
              id="editQuestionText"
              type="text"
              name="question_text"
              placeholder="Enter the evaluation question..."
              class="
                h-11
                w-full
                rounded-xl
                border border-slate-200
                bg-slate-50
                pl-10 pr-4
                text-sm
                text-slate-700
                outline-none
                transition-all duration-200

                placeholder:text-slate-400

                hover:border-slate-300

                focus:border-violet-400
                focus:bg-white
                focus:ring-4
                focus:ring-violet-500/10
              "
              required
            />

          </div>

          <p class="text-[11px] text-slate-400">
            Keep the question clear, concise, and appropriate for faculty evaluation.
          </p>

        </div>


        <!-- =================================================
             CATEGORY
        ================================================== -->
        <div class="space-y-2">

          <label
            for="editQuestionCategory"
            class="
              text-xs
              font-semibold
              text-slate-700
            "
          >
            Category
          </label>


          <div class="relative">

            <div
              class="
                pointer-events-none
                absolute left-3.5 top-1/2
                -translate-y-1/2
                text-slate-400
              "
            >
              <i class="fa-solid fa-layer-group text-xs"></i>
            </div>


            <select
              id="editQuestionCategory"
              name="category"
              class="
                h-11
                w-full
                cursor-pointer
                appearance-none
                rounded-xl
                border border-slate-200
                bg-slate-50
                pl-10 pr-10
                text-sm
                font-medium
                text-slate-700
                outline-none
                transition-all duration-200

                hover:border-slate-300

                focus:border-violet-400
                focus:bg-white
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


            <!-- Chevron -->
            <div
              class="
                pointer-events-none
                absolute inset-y-0 right-3.5
                flex items-center
                text-slate-400
              "
            >
              <i class="fa-solid fa-chevron-down text-[10px]"></i>
            </div>

          </div>

          <p class="text-[11px] text-slate-400">
            Choose the category that best represents this evaluation item.
          </p>

        </div>


        <!-- =================================================
             ACTIONS
        ================================================== -->
        <div
          class="
            flex flex-col-reverse
            gap-2.5
            border-t border-slate-100
            pt-5
            sm:flex-row
            sm:justify-end
          "
        >

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="editQuestionModal"
            class="
              inline-flex
              h-10
              items-center
              justify-center
              gap-2
              rounded-xl
              border border-slate-200
              bg-white
              px-5
              text-sm
              font-semibold
              text-slate-600
              shadow-sm
              transition-all duration-200

              hover:border-slate-300
              hover:bg-slate-50
              hover:text-slate-800

              focus:outline-none
              focus:ring-4
              focus:ring-slate-500/10

              active:scale-[0.98]
            "
          >
            <i class="fa-solid fa-xmark text-xs"></i>
            Cancel
          </button>


          <!-- Update -->
          <button
            type="submit"
            class="
              inline-flex
              h-10
              items-center
              justify-center
              gap-2
              rounded-xl
              bg-violet-600
              px-5
              text-sm
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
            <i class="fa-solid fa-check text-xs"></i>
            Update Question
          </button>

        </div>

      </form>

    </div>

  </div>

</div>