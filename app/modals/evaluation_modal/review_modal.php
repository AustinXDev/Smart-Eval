<?php /* review_modal.php */ ?>

<div id="reviewModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(0,0,0,0.5)">
  <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-xl flex flex-col overflow-hidden" style="max-height:90vh">

    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
      <div>
        <p class="text-xs text-gray-400 uppercase tracking-widest mb-0.5 whitespace-nowrap">Review before submitting</p>
        <h2 class="text-xs md:text-base font-semibold text-gray-800 m-0">Your answers</h2>
      </div>
      <div class="flex items-center gap-2">
        <span id="reviewTeacherCounter" class="whitespace-nowrap text-xs text-gray-500 bg-gray-100 px-3 py-1 rounded-full border border-gray-200"></span>
      </div>
    </div>

    <div class="px-6 py-3 border-b border-gray-100 flex items-center gap-3 flex-shrink-0">
      <div id="reviewTeacherAvatar"
        class="w-10 h-10 rounded-full bg-purple-100 border border-purple-200 flex items-center justify-center text-sm font-semibold text-purple-700 flex-shrink-0">
      </div>
      <div class="min-w-0">
        <p id="reviewTeacherName" class="text-sm font-semibold text-gray-800 m-0 truncate"></p>
        <p id="reviewTeacherDept" class="text-xs text-gray-400 m-0 truncate"></p>
      </div>
      <div class="ml-auto text-right flex-shrink-0">
        <p id="reviewScoreAvg" class="text-xl font-bold text-gray-800 m-0"></p>
        <p class="text-xs text-gray-400 m-0">avg score</p>
      </div>
    </div>

    <div id="reviewAnswerList" class="overflow-y-auto flex-1 px-6"></div>

    <div class="px-6 py-4 border-t border-gray-100 flex-shrink-0">
      <div class="flex items-center justify-center md:justify-between mb-3 gap-2 flex-wrap">
        <div class="flex gap-2">
          <button id="reviewPrevBtn" type="button"
            class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
            disabled>
            &larr; Prev
          </button>
          <button id="reviewNextBtn" type="button"
            class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed transition-colors">
            Next &rarr;
          </button>
        </div>
        <div class="flex gap-2">
          <button type="button"
            id="reviewCloseBtn"
            class="text-sm px-3 py-1.5 rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
            Go back &amp; edit
          </button>
          <button id="reviewConfirmBtn" type="button"
            onclick="studentEvaluation.completeEvaluation()"
            class="text-sm px-4 py-1.5 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50 transition-colors font-medium">
            Confirm &amp; complete
          </button>
        </div>
      </div>
      <div id="reviewDots" class="flex gap-1.5 justify-center flex-wrap mt-1"></div>
    </div>

  </div>
</div>