<!-- MAIN CONTENT -->
<div class="flex flex-col lg:flex-row h-[calc(94vh-64px)] overflow-hidden">

  <!-- LEFT CONTENT -->
  <div class="flex-1 flex flex-col overflow-hidden lg:p-6">

    <!-- Progress -->
    <div class="mb-4">
      <p class="text-sm text-gray-500 mb-1">
        Evaluate Teachers 1 by 1
      </p>

      <div class="w-full bg-gray-200 rounded-full h-2">
        <div id="progressBar"
          class="bg-purple-700 h-2 rounded-full transition-all duration-500"
          style="width: 40%">
        </div>
      </div>
    </div>

    <!-- TEACHER CARD -->
    <div class="bg-white rounded-xl shadow-md flex flex-col flex-1 overflow-hidden p-4">

      <!-- Teacher Header -->
      <div class="flex items-center gap-3 sm:gap-4 border-b pb-4">

        <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-300 rounded-full"></div>

        <h2 data-teacher-name
          class="text-base sm:text-lg font-semibold text-gray-800">

          Teacher Name

        </h2>

      </div>

      <!-- QUESTIONS (ONLY SCROLL AREA) -->
      <div id="questionsContainer"
        class="flex-1 overflow-y-auto space-y-6 mt-4 pr-1 sm:pr-2">

        <!-- Questions go here -->

      </div>

      <!-- FOOTER -->
      <div class="pt-4 border-t mt-4">

        <div class="flex flex-col sm:flex-row justify-between gap-3">

          <button id="previousBtn"
            class="text-gray-600 hover:text-black transition">

            ← Previous

          </button>

          <button id="nextBtn"
            class="bg-purple-700 text-white px-5 py-2 rounded-md hover:bg-purple-800 transition">

            Next Teacher

          </button>

        </div>

      </div>

    </div>

  </div>

  <!-- RIGHT PANEL -->
  <div class="w-full lg:w-[320px] flex flex-col gap-4 lg:p-6">

    <!-- Procedure -->
    <div class="bg-yellow-100 p-4 rounded-xl hidden lg:block">

      <h3 class="font-semibold text-yellow-800 mb-2 text-sm sm:text-base">
        ⚠️ Evaluation Procedure
      </h3>

      <ol class="text-xs sm:text-sm text-yellow-900 list-decimal ml-4 space-y-1">

        <li>Answer the evaluation questions.</li>
        <li>Click "Next Teacher".</li>
        <li>Complete all evaluations.</li>

      </ol>

    </div>

    <!-- Warning -->
    <div class="bg-red-200 p-4 rounded-xl text-red-800 text-center text-sm hidden lg:block">

      ❗ You must evaluate all teachers to complete.

    </div>

    <!-- Complete Button -->
    <button id="completeBtn"
      class="w-full bg-purple-700 text-white py-3 rounded-lg hover:bg-purple-800 transition">

      Complete Evaluation

    </button>

  </div>

</div>