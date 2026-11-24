<!-- View account (account_management/index.js fills the [data-view] fields) -->
<div id="viewAccountModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="viewAccountTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-id-card"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="viewAccountTitle" class="se-modal__title">Administrator Details</h2>
        <p class="se-modal__subtitle">Account information and access status.</p>
      </div>
      <button type="button" data-close-modal="viewAccountModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">
      <section class="se-modal__section">
        <div class="flex items-center gap-4 rounded-xl border border-slate-200 p-4">
          <span data-view="initials" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-base font-bold text-[#6010FF] ring-1 ring-[#6010FF]/15" aria-hidden="true"></span>
          <div class="min-w-0">
            <p data-view="full_name" class="truncate text-base font-semibold text-slate-900"></p>
            <p class="mt-0.5 truncate font-mono text-xs text-slate-500">@<span data-view="username"></span></p>
          </div>
          <span data-view="status_badge" class="ml-auto shrink-0"></span>
        </div>
      </section>

      <section class="se-modal__section">
        <h3 class="se-modal__section-title">Account Information</h3>
        <dl class="divide-y divide-slate-100 rounded-xl border border-slate-200 text-sm">
          <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Email</dt><dd data-view="email" class="min-w-0 truncate text-right font-medium text-slate-900"></dd></div>
          <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Role</dt><dd data-view="role_label" class="text-right font-medium text-slate-900"></dd></div>
          <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Access</dt><dd data-view="access" class="text-right font-medium text-slate-900"></dd></div>
          <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Created</dt><dd data-view="created_at" class="text-right font-medium tabular-nums text-slate-900"></dd></div>
          <div class="flex justify-between gap-4 px-4 py-3"><dt class="text-slate-500">Last updated</dt><dd data-view="updated_at" class="text-right font-medium tabular-nums text-slate-900"></dd></div>
        </dl>
      </section>
    </div>

    <footer class="se-modal__footer">
      <button type="button" data-close-modal="viewAccountModal" class="se-btn se-btn--secondary">Close</button>
      <button type="button" id="viewEditBtn" class="se-btn se-btn--primary">
        <i class="fa-solid fa-pen text-xs" aria-hidden="true"></i>
        <span>Edit</span>
      </button>
    </footer>
  </div>
</div>
