<!-- ADD PROGRAM MODAL -->
<div id="addProgramModal"
  class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- MODAL CONTAINER -->
  <div class="w-full max-w-2xl bg-white/90 backdrop-blur-xl rounded-2xl shadow-2xl overflow-hidden animate-fadeIn flex flex-col">

    <!-- HEADER -->
    <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-center">
      <div>
        <h2 class="text-white text-xl font-semibold tracking-wide">Add New Program</h2>
        <p class="text-indigo-200 text-xs">Create a new academic program</p>
      </div>

      <button data-close-modal="addProgramModal"
        class="text-indigo-200 hover:text-white text-xl transition">
        &times;
      </button>
    </div>

    <!-- BODY -->
    <div class="p-6 space-y-6">

      <form class="space-y-5" method="POST" id="addProgramForm">

        <!-- PROGRAM CODE -->
        <div class="relative">
          <label class="text-sm text-gray-600 mb-1 block">Program Code</label>
          <div class="flex items-center border rounded-xl bg-gray-50 focus-within:ring-2 focus-within:ring-purple-500 transition">
            <i class="fa-solid fa-code px-3 text-gray-400"></i>
            <input type="text" name="program_code" placeholder="e.g BSIT"
              class="w-full bg-transparent px-2 py-3 text-sm outline-none" required>
          </div>
        </div>

        <!-- PROGRAM NAME -->
        <div class="relative">
          <label class="text-sm text-gray-600 mb-1 block">Full Program Name</label>
          <div class="flex items-center border rounded-xl bg-gray-50 focus-within:ring-2 focus-within:ring-purple-500 transition">
            <i class="fa-solid fa-book px-3 text-gray-400"></i>
            <input type="text" name="program_name" placeholder="e.g Bachelor of Science in Information Technology"
              class="w-full bg-transparent px-2 py-3 text-sm outline-none" required>
          </div>
        </div>

        <!-- DEPARTMENT -->
        <div>
          <label class="text-sm text-gray-600 mb-1 block">Target Department</label>

          <div class="relative">
            <i class="fa-solid fa-building absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>

            <select
              class="w-full pl-10 pr-10 py-3 border rounded-xl bg-gray-50 text-sm focus:ring-2 focus:ring-purple-500 outline-none appearance-none" name="department" required>
              <option selected disabled>Select Department</option>
              <option value="college">College</option>
              <option value="shs">Senior High School</option>
            </select>

            <i class="fa-solid fa-chevron-down absolute right-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
          </div>

          <p class="text-xs text-gray-400 mt-2">
            This determines which dashboard the data appears in.
          </p>
        </div>

        <!-- DIVIDER -->
        <div class="border-t pt-4"></div>

        <!-- ACTION BUTTONS -->
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3">

          <button type="button" data-close-modal="addProgramModal"
            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border text-gray-600 hover:bg-gray-100 transition">
            Cancel
          </button>

          <button type="submit"
            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 
                   text-white shadow-md hover:scale-[1.03] active:scale-[0.97] transition-all">
            <i class="fa-solid fa-floppy-disk mr-2"></i> Save Program
          </button>

        </div>

      </form>

    </div>
  </div>
</div>