<!-- Teacher comments (shared/table-config.js fills #initial, #tName, #mean, #comment-container) -->
<div id="commentModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="commentModalTitle">
  <div class="se-modal__panel se-modal__panel--lg">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-regular fa-comments"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="commentModalTitle" class="se-modal__title">Teacher Comments</h2>
        <p class="se-modal__subtitle">Feedback and remarks from student evaluations.</p>
      </div>
      <button id="closeComment" type="button" class="closeModal se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">

      <section class="se-modal__section">
        <div class="flex flex-wrap items-center justify-between gap-3">
          <div class="flex min-w-0 items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#16213E] text-sm font-semibold text-white">
              <span id="initial"></span>
            </span>
            <div class="min-w-0">
              <p id="tName" class="truncate text-sm font-semibold text-slate-900"></p>
              <p class="text-xs text-slate-500">Mean Score: <span id="mean" class="font-semibold text-violet-700"></span></p>
            </div>
          </div>

          <button type="button" class="btn-export btn-export-comment se-btn se-btn--secondary" id="btn-export-ranking">
            <svg class="h-4 w-4" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 10v3h10v-3M8 2v7M5 6l3 3 3-3"/>
            </svg>
            Export to Excel
          </button>
        </div>
      </section>

      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Comments</h3>
        <div id="comment-container" class="flex flex-col gap-2"></div>
      </section>

    </div>

  </div>
</div>
