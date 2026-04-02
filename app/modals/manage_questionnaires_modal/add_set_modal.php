<!-- CREATE QUESTION SET MODAL -->
<div id="createQuestionSetModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <div
    class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden animate-fadeIn">

    <!-- HEADER -->
    <div class="bg-[#0F172A] px-5 py-4 flex justify-between items-center">
      <h2 class="text-white text-lg font-semibold tracking-wide">
        Create New Question Set
      </h2>

      <button data-close-modal="createQuestionSetModal"
        class="text-gray-400 hover:text-white text-xl transition">
        &times;
      </button>
    </div>

    <!-- BODY -->
    <div class="p-6 space-y-5">

      <form id="createQuestionSetForm" class="space-y-5">

        <!-- INPUT -->
        <div class="space-y-2">
          <label class="text-sm font-medium text-gray-600">
            Set Name
          </label>

          <input type="text" name="set_name" placeholder="Enter set name"
            class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm 
                   focus:ring-2 focus:ring-purple-500 focus:border-purple-500 
                   outline-none transition shadow-sm">
        </div>

        <!-- NOTE -->
        <div class="bg-yellow-100 border border-yellow-200 text-yellow-800 text-sm px-4 py-3 rounded-lg">
          <span class="font-semibold">NOTE:</span>
          Once created, you can add specific questions and categories to this set
        </div>

        <!-- DIVIDER -->
        <div class="border-t"></div>

        <!-- ACTION -->
        <div class="flex justify-end">
          <button type="submit"
            class="bg-purple-700 hover:bg-purple-800 text-white text-sm font-medium 
                   px-5 py-2.5 rounded-lg shadow-md transition-all duration-200
                   hover:scale-[1.03] active:scale-[0.98]">
            Create Set
          </button>
        </div>

      </form>

    </div>
  </div>
</div>