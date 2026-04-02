<!-- EDIT QUESTION MODAL -->
<div id="editQuestionModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <div
    class="bg-white w-full max-w-lg rounded-2xl shadow-2xl overflow-hidden animate-fadeIn">

    <!-- HEADER -->
    <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-center">
      <div>
        <h2 class="text-white text-lg font-semibold tracking-wide">
          Edit Question
        </h2>
        <p class="text-white/70 text-xs">Update evaluation item</p>
      </div>
    </div>

    <!-- BODY -->
    <div class="p-6 space-y-5">

      <form id="editQuestionForm" class="space-y-5">
        <input type="hidden" id="question_id_input" name="question_id">
        <input type="hidden" id="set_input" name="set_id">

        <!-- CONTEXT NOTE -->
        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-sm px-4 py-3 rounded-lg">
          <span class="font-semibold">Note:</span>
          You are editing a question from the selected evaluation questionnaire set.
        </div>

        <!-- QUESTION INPUT -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-600">
            Question Text
          </label>

          <input type="text" name="question_text"
            placeholder="Update your question"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                   outline-none transition shadow-sm" required>
        </div>

        <!-- CATEGORY SELECT -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-600">
            Category
          </label>

          <select name="category"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm
                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                   outline-none" required>
              <option value="" disabled selected>Select Category</option>
              <option value="Punctuality">Punctuality</option>
              <option value="Communication">Communication</option>
              <option value="Subject Mastery">Subject Mastery</option>
              <option value="Professionalism">Professionalism</option>
              <option value="Classroom Management">Classroom Management</option>
          </select>
        </div>

        <!-- ACTIONS -->
        <div class="flex justify-center lg:justify-end gap-3 items-center pt-2">

          <button type="button" data-close-modal="editQuestionModal"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md text-sm transition">
            Cancel
          </button>

          <button type="submit"
            class="bg-indigo-700 hover:bg-indigo-800 text-white text-sm font-medium 
                   px-5 py-2.5 rounded-lg shadow transition-all duration-200
                   hover:scale-[1.03] active:scale-[0.97] flex items-center gap-2">

            <i class="fa-solid fa-pen-to-square"></i>
            Update Question
          </button>

        </div>

      </form>

    </div>
  </div>
</div>