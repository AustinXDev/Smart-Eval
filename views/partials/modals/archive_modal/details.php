
<div
  id="archiveDetailsModal"
  role="dialog"
  aria-modal="true"
  aria-labelledby="archive-details-title"
  aria-describedby="archive-details-description"
  class="fixed inset-0 z-100 hidden items-center justify-center overflow-y-auto bg-slate-950/55 p-3 backdrop-blur-[3px] sm:p-6"
>
  <div
    id="archive-details-panel"
    tabindex="-1"
    class="mx-auto flex w-full max-w-3xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl"
    style="max-height: calc(100dvh - 2rem)"
  >
    <!-- HEADER -->
    <header class="flex shrink-0 items-start justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
      <div class="flex min-w-0 items-center gap-3">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-violet-100 bg-violet-50 text-violet-600">
          <i class="fa-solid fa-box-archive text-lg" aria-hidden="true"></i>
        </div>

        <div class="min-w-0">
          <h2
            id="archive-details-title"
            class="text-base font-semibold tracking-tight text-slate-900 sm:text-lg"
          >
            Archived Evaluation Details
          </h2>
          <p
            id="archive-details-description"
            class="mt-0.5 text-xs leading-relaxed text-slate-500 sm:text-sm"
          >
            Review archived records and recovery information.
          </p>
        </div>
      </div>

      <button
        type="button"
        id="close-archive-details"
        aria-label="Close details"
        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-500 transition-colors hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-500"
      >
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <!-- SCROLLABLE CONTENT -->
    <main
      id="archive-details-content"
      aria-live="polite"
      aria-busy="false"
      class="min-h-0 flex-1 space-y-5 overflow-y-auto bg-slate-50/70 p-4 sm:p-6"
    >
      <div class="rounded-xl border border-slate-200 bg-white p-5 text-sm text-slate-500">
        Select an archived period or recovery activity to view its details.
      </div>
    </main>

    <!-- FOOTER -->
    <footer class="flex shrink-0 items-center justify-end gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
      <button
        type="button"
        id="close-archive-details-footer"
        class="inline-flex min-h-10 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-violet-500"
      >
        Close
      </button>
    </footer>
  </div>
</div>
