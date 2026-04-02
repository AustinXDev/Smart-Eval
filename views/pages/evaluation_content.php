<!-- PAGE WRAPPER (IMPORTANT) -->
<div class="h-screen flex flex-col">

  <div class="flex-1 p-4">

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full">

      <!-- LEFT SIDE -->
      <div class="lg:col-span-2 flex flex-col h-full">

        <!-- Header -->
        <div class="mb-3 flex-shrink-0">
          <p class="text-sm text-gray-500 mb-1">Evaluate Teachers 1 by 1</p>

          <div class="w-full bg-gray-200 rounded-full h-2">
            <div id="progressBar"
                 class="bg-purple-700 h-2 rounded-full transition-all duration-500"
                 style="width: 40%"></div>
          </div>
        </div>

        <!-- Teacher Card -->
        <div class="bg-white rounded-xl shadow-md p-6 flex flex-col flex-1 overflow-hidden">

          <!-- Teacher Info -->
          <div class="flex items-center gap-4 border-b pb-4 flex-shrink-0">
            <div class="w-12 h-12 bg-gray-300 rounded-full"></div>
            <h2 class="text-lg font-semibold text-gray-800">Teacher's Name</h2>
          </div>

          <!-- QUESTIONS (SCROLLABLE) -->
          <div id="questionsContainer"
               class="flex-1 overflow-y-auto space-y-5 mt-4 pr-2">

            <!-- Question -->
            <div class="question">
              <p class="font-medium text-gray-700 mb-3">
                1. This is Question 1.
              </p>

              <div class="flex flex-wrap gap-3 text-sm">

                <!-- Strongly Disagree -->
                <label class="cursor-pointer">
                  <input type="radio" name="q1" class="hidden peer">
                  <div class="px-4 py-2 rounded-full border border-gray-300 
                              text-gray-600 bg-white transition-all duration-200
                              hover:border-red-400 hover:text-red-500 hover:scale-105
                              peer-checked:bg-red-500 peer-checked:text-white 
                              peer-checked:border-red-500 peer-checked:shadow-md">
                    Strongly Disagree
                  </div>
                </label>

                <!-- Repeat others... -->

              </div>
            </div>

          </div>

          <!-- FOOTER -->
          <div class="pt-4 border-t mt-4 flex-shrink-0">

            <!-- Feedback -->
            <div>
              <label class="text-sm font-medium text-gray-600">Share Your Thoughts</label>

              <textarea id="feedback"
                maxlength="500"
                class="w-full mt-2 border rounded-lg p-3 text-sm focus:ring-2 focus:ring-purple-500 focus:outline-none resize-none"
                rows="3"
                placeholder="Type your feedback..."></textarea>

              <p class="text-xs text-gray-400 text-right mt-1">
                <span id="charCount">0</span> / 500 characters
              </p>
            </div>

            <!-- Buttons -->
            <div class="flex justify-between items-center mt-4">
              <button class="flex items-center gap-2 text-gray-600 hover:text-black transition">
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

      <!-- RIGHT SIDE -->
      <div class="space-y-4">

        <div class="bg-yellow-100 p-4 rounded-xl shadow-sm">
          <div class="flex items-center gap-2 mb-2">
            ⚠️ <h3 class="font-semibold text-yellow-800">Evaluation Procedure</h3>
          </div>

          <ol class="text-sm text-yellow-900 space-y-1 list-decimal ml-4">
            <li>Answer the evaluation questions.</li>
            <li>Click "Next Teacher".</li>
            <li>Complete all evaluations.</li>
          </ol>
        </div>

        <div class="bg-red-200 p-4 rounded-xl text-center text-red-800 shadow-sm">
          ❗ You must evaluate all teachers to complete.
        </div>

        <button id="completeBtn"
          class="w-full bg-gray-300 text-white py-3 rounded-lg cursor-not-allowed"
          disabled>
          Complete Evaluation
        </button>

      </div>

    </div>
  </div>
</div>