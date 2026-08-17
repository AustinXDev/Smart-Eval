<div id="notifyProgressModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4" style="background:rgba(79,70,229,0.15);backdrop-filter:blur(2px);">
  <div class="bg-white border border-indigo-200 rounded-2xl w-full max-w-sm p-6 shadow-xl">

    <div class="flex items-center gap-3 mb-5">
      <div id="notifyIconWrap" class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:#EEF2FF;border:0.5px solid #A5B4FC;">
        <svg id="notifyIconSvg" class="w-4 h-4" style="color:#4F46E5;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.86 9.82 19.79 19.79 0 01.77 1.18 2 2 0 012.76 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.91 7.63a16 16 0 006.29 6.29l1.52-1.52a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 16.92z"/>
        </svg>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-semibold text-indigo-950 m-0">Sending notifications</p>
        <p id="notifyStatusText" class="text-xs text-indigo-500 mt-0.5 m-0">Preparing batch...</p>
      </div>
      <span id="notifyPctBadge" class="text-xs font-medium px-2 py-0.5 rounded-full flex-shrink-0" style="background:#EEF2FF;color:#4F46E5;border:0.5px solid #A5B4FC;">0%</span>
    </div>

    <!-- Progress bar -->
    <div class="rounded-full h-1.5 overflow-hidden mb-2" style="background:#EEF2FF;">
      <div id="notifyProgressBar" class="h-full rounded-full transition-all duration-500" style="width:0%;background:#4F46E5;"></div>
    </div>

    <div class="flex items-center justify-between mb-5">
      <span id="notifyBatchLabel" class="text-xs text-indigo-400">Batch 0 of —</span>
      <span id="notifySentLabel" class="text-xs text-indigo-400">0 sent</span>
    </div>

    <!-- Stats panel -->
    <div class="rounded-xl p-3 mb-5" style="background:#EEF2FF;">
      <div class="flex justify-between text-xs mb-1.5">
        <span class="text-indigo-500">Recipients</span>
        <span id="notifyRecipientCount" class="font-medium text-indigo-950">—</span>
      </div>
      <div class="flex justify-between text-xs mb-1.5">
        <span class="text-indigo-500">Sent so far</span>
        <span id="notifySentCount" class="font-medium text-indigo-950">0</span>
      </div>
      <div class="flex justify-between text-xs">
        <span class="text-indigo-500">Remaining</span>
        <span id="notifyRemainingCount" class="font-medium text-indigo-950">—</span>
      </div>
    </div>

    <!-- Done message -->
    <div id="notifyDoneSection" class="hidden border-t border-indigo-100 pt-4 mb-4">
      <p id="notifyDoneMsg" class="text-xs text-center text-indigo-500 m-0"></p>
    </div>

    <!-- Buttons -->
    <div class="flex gap-2">
      <button id="notifyCancelBtn" onclick="cancelNotification()"
        class="flex-1 text-sm py-2 rounded-xl border border-indigo-200 text-indigo-600 hover:bg-indigo-50 transition-colors cursor-pointer"
        style="background:#EEF2FF;">
        Cancel
      </button>
      <button id="notifyCloseBtn"
        onclick="document.getElementById('notifyProgressModal').classList.add('hidden')"
        class="hidden flex-1 text-sm py-2 rounded-xl text-white border-0 cursor-pointer transition-colors hover:bg-indigo-700"
        style="background:#4F46E5;">
        Close
      </button>
    </div>

  </div>
</div>