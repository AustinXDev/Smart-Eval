<!-- CREATE EVALUATION PERIOD MODAL -->
<div id="updatePeriodModal"
  class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <div
    class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-3xl overflow-hidden
           max-h-[90vh] flex flex-col animate-fadeIn">

    <!-- HEADER -->
    <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-center border-b-2 border-purple-600 flex-shrink-0">
      <div>
        <h2 class="text-white text-lg font-semibold">Update Evaluation Period</h2>
        <p class="text-gray-400 text-xs">Modify an existing evaluation cycle</p>
      </div>

      <button data-close-modal="updatePeriodModal"
        class="text-gray-400 hover:text-white text-xl">&times;</button>
    </div>

    <!-- SCROLLABLE BODY -->
    <div class="overflow-y-auto p-6 space-y-6 scroll-smooth">

      <!-- FORM -->
      <form id="updatePeriodForm" class="space-y-6">
        <input type="hidden" name="period_id" id="update_period_id">

        <!-- ACADEMIC IDENTITY -->
        <div class="bg-white p-5 rounded-lg shadow">
          <h3 class="bg-purple-700 text-white text-sm px-3 py-2 rounded mb-4">
            Academic Identity
          </h3>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
              <label class="text-sm text-gray-600">Academic Year <span class="text-red-500">*</span></label>
              <input type="text" id="update_academic_year" name="update_academic_year" placeholder="e.g. 2025-2026"
                class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none" required>
            </div>

            <div>
              <label class="text-sm text-gray-600">Semester <span class="text-red-500">*</span></label>
              <div class="flex flex-wrap gap-4 mt-2">

                <label class="flex items-center gap-2 text-sm">
                  <input type="radio" name="update_semester" value="1st Semester" class="accent-purple-600" required>
                  1st Semester
                </label>

                <label class="flex items-center gap-2 text-sm">
                  <input type="radio" name="update_semester" value="2nd Semester" class="accent-purple-600">
                  2nd Semester
                </label>

              </div>
            </div>

          </div>
        </div>

        <!-- DEPARTMENT -->
        <div class="bg-white p-5 rounded-lg shadow">
          <h3 class="bg-purple-700 text-white text-sm px-3 py-2 rounded mb-4">
            Department Restriction
          </h3>

          <div class="flex flex-col gap-2 text-sm">

            <label class="flex items-center gap-2">
              <input type="radio" name="update_department" value="college" class="accent-purple-600">
              College
            </label>

            <label class="flex items-center gap-2">
              <input type="radio" name="update_department" value="shs" class="accent-purple-600">
              Senior High School
            </label>

          </div>
        </div>

        <!-- LOWER GRID -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- SCHEDULE -->
          <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="bg-purple-700 text-white text-sm px-3 py-2 rounded mb-4">
              Schedule & Active Status
            </h3>

            <div class="text-xs bg-yellow-100 text-yellow-700 px-3 py-2 rounded mb-3">
              ⚠ End date cannot be earlier than start
            </div>

            <div class="space-y-3">

              <div>
                <label class="text-sm text-gray-600">Start Date & Time</label>
                <input type="datetime-local" name="update_start_date"
                  class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
              </div>

              <div>
                <label class="text-sm text-gray-600">End Date & Time</label>
                <input type="datetime-local" name="update_end_date"
                  class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
              </div>

            </div>
          </div>

          <!-- QUESTION SET -->
          <div class="bg-white p-5 rounded-lg shadow">
            <h3 class="bg-purple-700 text-white text-sm px-3 py-2 rounded mb-4">
              Question Set Selection
            </h3>

            <div>
              <label class="text-sm text-gray-600">Evaluation Form</label>
              <select name="update_question_set" id="updateQuestionSetSelect"
                class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 outline-none">
                <option disabled selected>Select Question Bank</option>
                <!-- JS populate -->
              </select>
            </div>
          </div>

        </div>

        <!-- ACTIONS -->
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">

          <button type="button" data-close-modal="updatePeriodModal"
            class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition w-full sm:w-auto">
            Cancel
          </button>

          <button type="submit"
            class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md shadow transition w-full sm:w-auto">
            Save Changes
          </button>

        </div>

      </form>

    </div>
  </div>
</div>