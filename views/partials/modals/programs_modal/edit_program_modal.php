<!-- EDIT PROGRAM MODAL -->
<div id="editProgramModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- MODAL CONTAINER -->
  <div class="w-full max-w-2xl bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col">

    <!-- HEADER -->
    <div class="bg-gradient-to-r from-[#1E1B4B] to-[#312E81] px-6 py-4 flex justify-between items-center">
      <div>
        <h2 class="text-white text-xl font-semibold tracking-wide">Edit Program</h2>
        <p class="text-indigo-200 text-xs">Update program details</p>
      </div>

      <button data-close-modal="editProgramModal"
        class="text-indigo-200 hover:text-white text-xl transition">
        &times;
      </button>
    </div>

    <!-- BODY -->
    <div class="p-6 space-y-6">

      <form id="editProgramForm" class="space-y-5">

        <!-- HIDDEN ID -->
        <input type="hidden" name="edit_program_id" id="edit_program_id">

        <!-- PROGRAM CODE -->
        <div>
          <label class="text-sm text-gray-600 mb-1 block">Program Code</label>
          <div class="flex items-center border rounded-xl bg-gray-50 focus-within:ring-2 focus-within:ring-purple-500 transition">
            <i class="fa-solid fa-code px-3 text-gray-400"></i>
            <input type="text" name="edit_program_code" id="edit_program_code"
              class="w-full bg-transparent px-2 py-3 text-sm outline-none"
              placeholder="e.g BSIT" required>
          </div>
        </div>

        <!-- PROGRAM NAME -->
        <div>
          <label class="text-sm text-gray-600 mb-1 block">Full Program Name</label>
          <div class="flex items-center border rounded-xl bg-gray-50 focus-within:ring-2 focus-within:ring-purple-500 transition">
            <i class="fa-solid fa-book px-3 text-gray-400"></i>
            <input type="text" name="edit_program_name" id="edit_program_name"
              class="w-full bg-transparent px-2 py-3 text-sm outline-none"
              placeholder="e.g Bachelor of Science in Information Technology" required>
          </div>
        </div>

        <!-- DEPARTMENT -->
        <div>
          <label class="text-sm text-gray-600 mb-1 block">Target Department</label>

          <div class="relative">
            <i class="fa-solid fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

            <select name="edit_department" id="edit_department"
              class="w-full pl-10 pr-10 py-3 border rounded-xl bg-gray-50 text-sm focus:ring-2 focus:ring-purple-500 outline-none appearance-none">
              <option disabled>Select Department</option>
              <option value="college">College</option>
              <option value="shs">Senior High School</option>
            </select>

            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>

          <p class="text-xs text-gray-400 mt-2">
            Changing this affects where the program appears.
          </p>
        </div>

        <!-- ACTIONS -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 pt-3 border-t">

          <button type="button" data-close-modal="editProgramModal"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border text-gray-600 hover:bg-gray-100 transition">
            Cancel
          </button>

          <button type="submit"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 
                   text-white shadow-md hover:scale-[1.03] active:scale-[0.97] transition-all">
            <i class="fa-solid fa-pen-to-square mr-2"></i> Update Program
          </button>

        </div>

      </form>

    </div>
  </div>
</div>