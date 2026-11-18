<!-- Notification sending progress. Element IDs are kept for the sending script. -->
<div id="notifyProgressModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="notifyProgressTitle">
  <div class="se-modal__panel se-modal__panel--sm">

    <header class="se-modal__header">
      <span id="notifyIconWrap" class="se-modal__icon" aria-hidden="true">
        <svg id="notifyIconSvg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M4 4h16v16H4z" opacity="0"/>
          <path d="M22 6 12 13 2 6"/>
          <rect x="2" y="4" width="20" height="16" rx="2"/>
        </svg>
      </span>
      <div class="se-modal__heading">
        <h2 id="notifyProgressTitle" class="se-modal__title">Sending notifications</h2>
        <p id="notifyStatusText" class="se-modal__subtitle">Preparing batch...</p>
      </div>
      <span id="notifyPctBadge" class="shrink-0 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold tabular-nums text-slate-700">0%</span>
    </header>

    <div class="se-modal__body">

      <section class="se-modal__section">
        <div class="h-1.5 overflow-hidden rounded-full bg-slate-100">
          <div id="notifyProgressBar" class="h-full rounded-full bg-violet-600 transition-all duration-500" style="width:0%"></div>
        </div>
        <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
          <span id="notifyBatchLabel">Batch 0 of —</span>
          <span id="notifySentLabel">0 sent</span>
        </div>
      </section>

      <section class="se-modal__section">
        <dl class="space-y-2 text-sm">
          <div class="flex justify-between"><dt class="text-slate-500">Recipients</dt><dd id="notifyRecipientCount" class="font-semibold tabular-nums text-slate-900">—</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Sent so far</dt><dd id="notifySentCount" class="font-semibold tabular-nums text-slate-900">0</dd></div>
          <div class="flex justify-between"><dt class="text-slate-500">Remaining</dt><dd id="notifyRemainingCount" class="font-semibold tabular-nums text-slate-900">—</dd></div>
        </dl>
      </section>

      <section id="notifyDoneSection" class="se-modal__section hidden">
        <p id="notifyDoneMsg" class="se-modal__message text-center"></p>
      </section>

    </div>

    <footer class="se-modal__footer">
      <button id="notifyCancelBtn" type="button" onclick="cancelNotification()" class="se-btn se-btn--secondary">Cancel</button>
      <button id="notifyCloseBtn" type="button" onclick="document.getElementById('notifyProgressModal').classList.add('hidden')" class="se-btn se-btn--primary hidden">Close</button>
    </footer>

  </div>
</div>
