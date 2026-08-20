<div
  id="confirmationModal"
  class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modalTitle"
>
  <div
    class="w-full max-w-md rounded-2xl border border-slate-200 bg-white p-7 shadow-2xl"
  >

    <!-- Icon -->
    <div class="mb-5 flex justify-center">
      <div
        class="flex h-14 w-14 items-center justify-center rounded-full bg-red-50"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-7 w-7 text-red-500"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v3.75m0 3.75h.008v.008H12v-.008z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"
          />
        </svg>
      </div>
    </div>

    <!-- Content -->
    <div class="text-center">
      <h2
        id="modalTitle"
        class="text-xl font-semibold text-slate-900"
      >
        Confirm Action
      </h2>

      <p
        id="modalMessage"
        class="mt-2 text-sm leading-6 text-slate-500"
      >
        Are you sure you want to proceed?
      </p>
    </div>

    <!-- Buttons -->
    <div class="mt-7 grid grid-cols-2 gap-3">

      <button
        id="cancelBtn"
        type="button"
        class="h-11 rounded-xl border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-slate-300"
      >
        Cancel
      </button>

      <button
        id="confirmBtn"
        type="button"
        class="h-11 rounded-xl bg-red-500 px-4 text-sm font-semibold text-white transition hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
      >
        Yes
      </button>

    </div>
  </div>
</div>