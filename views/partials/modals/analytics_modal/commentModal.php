  <div id="commentModal"
    class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 p-4">
 
    <div class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-4xl overflow-hidden max-h-[90vh] flex flex-col">
 
      <!-- HEADER -->
      <div class="bg-[#0F172A] px-6 py-4 flex justify-between items-center border-b-2 border-purple-600 flex-shrink-0">
        <div>
          <h2 class="text-white text-lg font-semibold">Teacher comment</h2>
          <p class="text-gray-400 text-xs mt-0.5">View feedback and remarks from student evaluations</p>
        </div>
        <button id="closeComment" class="closeModal text-gray-400 hover:text-white text-2xl leading-none">&times;</button>
      </div>
 
      <!-- BODY -->
      <div class="overflow-y-auto p-5 flex flex-col gap-4">
 
        <div class="flex justify-between items-center">
          <div class="flex gap-3 items-center">
            <div class="flex justify-center items-center w-12 h-12 bg-indigo-800 rounded-full">
              <span class="text-white font-bold" id="initial"></span>
            </div>
            <div class="">
              <span class="font-semibold" id="tName"></span>
              <p class="text-xs">Mean Score: <span id="mean" class="text-indigo-800 font-bold"></span></p>
            </div>
          </div>

          <div>
            <button class="btn-export btn-export-comment" id="btn-export-ranking">
              <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 10v3h10v-3M8 2v7M5 6l3 3 3-3"/>
              </svg>
              Export to Excel
            </button>
          </div>
        </div>

        <div class="border-t border-gray-300 mb-3"></div>

        <div id="comment-container" class="flex flex-col gap-2">
        </div>
 
    </div>
  </div>