<!-- MANAGE QUESTIONS MODAL -->
<div id="manageQuestionsModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <div
    class="bg-white w-full max-w-4xl rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh] animate-fadeIn">

    <!-- HEADER -->
    <div class="bg-[#0F172A] px-5 py-4 flex justify-between items-center">
      <div>
        <h2 class="text-white text-lg md:text-xl font-semibold tracking-wide">
          Manage Questions
        </h2>
        <p class="text-white/70 text-xs">Standard Faculty Evaluation</p>
      </div>

      <button data-close-modal="manageQuestionsModal"
        class="text-white/70 hover:text-white text-2xl transition">
        &times;
      </button>
    </div>

    <!-- BODY -->
    <div class="flex flex-col p-6 gap-6 overflow-hidden">

      <!-- INPUT CARD -->
      <div class="bg-gray-50 border rounded-xl p-5 shadow-sm">
        <h1 class="mb-3 font-semibold text-purple-800">Add Question</h1>

        <form id="addQuestionForm" action="POST">
          <input type="hidden" name="set_id" id="set_id_input">
          
          <div class="grid md:grid-cols-3 gap-4">

            <!-- QUESTION -->
              <div class="md:col-span-2">
                <label class="text-sm font-medium text-gray-600">Question Text</label>
                <input type="text" name="question_name" placeholder="Enter your question"
                  class="w-full mt-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                        focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none
                        transition shadow-sm" required>
              </div>

              <!-- CATEGORY -->
              <div>
                <label class="text-sm font-medium text-gray-600">Category</label>
                <select name="categories"
                  class="w-full mt-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm
                        focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none" required>
                  <option value="" disabled selected>Select Category</option>
                  <option value="Punctuality">Punctuality</option>
                  <option value="Communication">Communication</option>
                  <option value="Subject Mastery">Subject Mastery</option>
                  <option value="Professionalism">Professionalism</option>
                  <option value="Classroom Management">Classroom Management</option>
                </select>
              </div>

            </div>

            <!-- ACTION -->
            <div class="flex justify-end mt-4">
              <button type="submit"
                class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium 
                      px-5 py-2.5 rounded-lg shadow transition-all duration-200
                      hover:scale-[1.04] active:scale-[0.97] flex items-center gap-2">
                <i class="fa-solid fa-plus"></i> Add Question
              </button>
            </div>
          </div>

        </form>

      <!-- TABLE SECTION -->
      <div class="flex flex-col border rounded-xl overflow-hidden">

        <!-- TABLE HEADER BAR -->
        <div class="bg-gray-100 px-4 py-3 flex justify-between items-center">
          <p class="text-sm font-semibold text-gray-700 text-purple-800">Question List</p>
          <span class="text-xs text-gray-500">5 items</span>
        </div>

        <!-- SCROLLABLE TABLE -->
        <div class="overflow-y-auto max-h-[300px]">

          <table class="w-full text-sm text-left">
            <thead class="bg-white text-gray-500 text-xs uppercase sticky top-0 shadow-sm">
              <tr>
                <th class="px-4 py-3">Question</th>
                <th class="px-4 py-3">Category</th>
                <th class="px-4 py-3 text-center">Actions</th>
              </tr>
            </thead>

            <tbody id="questionList" class="divide-y">
              <!-- Fill by Js -->
            </tbody>
          </table>

        </div>
      </div>

    </div>
  </div>
</div>