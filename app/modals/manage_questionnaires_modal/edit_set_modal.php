<!-- EDIT QUESTION SET MODAL -->
<div id="editQuestionSetModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- MODAL CONTAINER -->
  <div
    class="bg-white w-full max-w-lg rounded-xl shadow-2xl overflow-hidden animate-fadeInUp">

    <!-- HEADER -->
    <div class="bg-[#111827] px-6 py-4 flex items-center justify-between">
      <h2 class="text-white text-xl font-semibold tracking-wide">
        Edit Question Set
      </h2>

      <button data-close-modal="editQuestionSetModal"
        class="text-gray-300 hover:text-white text-2xl font-bold leading-none transition">
        &times;
      </button>
    </div>

    <!-- BODY -->
    <div class="p-6 space-y-6">

      <form id="editQuestionSetForm" class="space-y-6">
        <input type="hidden" id="edit_set_id" name="set_id">

        <!-- INPUT GROUP -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-600">
            Set Name
          </label>

          <input type="text" name="set_name" id="set_name_input"
            class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm
                   focus:ring-2 focus:ring-purple-600 focus:border-purple-600
                   outline-none transition shadow-sm hover:shadow-md">
        </div>

        <!-- NOTE -->
        <div class="bg-amber-100 border border-amber-200 text-amber-900 text-sm px-4 py-3 rounded-xl">
          <span class="font-semibold">NOTE:</span>
          Updating the set name will not affect the questions or existing evaluation results linked to this set.
        </div>

        <!-- DIVIDER -->
        <div class="border-t border-gray-200"></div>

        <!-- ACTIONS -->
        <div class="flex flex-col sm:flex-row gap-3 sm:justify-end">

          <button type="button"
            data-close-modal="editQuestionSetModal"
            class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600
                   hover:bg-gray-100 transition text-sm">
            Cancel
          </button>

          <button type="submit"
            class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium
                   px-6 py-2.5 rounded-lg shadow-md transition-all duration-200
                   hover:scale-[1.03] active:scale-[0.97]">
            Save Changes
          </button>

        </div>

      </form>
    </div>
  </div>
</div>