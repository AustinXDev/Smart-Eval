  <div id="viewHistoryModal"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
 
    <div class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
 
      <!-- HEADER -->
      <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-center border-b-2 border-purple-600 flex-shrink-0">
        <div>
          <h2 class="text-white text-lg font-semibold">Evaluation History</h2>
          <p class="text-gray-400 text-xs mt-0.5">Browse past evaluation periods and scores</p>
        </div>
        <button id="closeModal" class="text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
      </div>
 
      <!-- BODY -->
      <div class="overflow-y-auto p-5 flex flex-col gap-4">
 
        <!-- TABLE CARD -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 whitespace-nowrap overflow-y-auto">
          <table id="tbl-history" class="w-full" style="width:100%">
            <thead>
              <tr>
                <th>Academic Year</th>
                <th>Semester</th>
                <th>Mean Score</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="historyBody">
              <!-- Populated via JS / PHP AJAX -->
            </tbody>
          </table>
        </div>
 
      </div>
 
    </div>
  </div>