<!-- CSV Upload Loading Overlay -->
<div
  id="uploadLoading"
  class="fixed inset-0 z-[100] hidden flex items-center justify-center bg-slate-950/50 px-4 backdrop-blur-sm"
  role="status"
  aria-live="polite"
  aria-label="Uploading CSV file"
>

  <!-- Loading Card -->
  <div
    class="w-full max-w-xs rounded-2xl border border-white/40 bg-white p-7 text-center shadow-2xl shadow-slate-900/20"
  >

    <!-- Spinner Container -->
    <div class="mx-auto mb-5 flex h-16 w-16 items-center justify-center">

      <!-- Soft Glow -->
      <div
        class="absolute h-14 w-14 rounded-full bg-violet-500/20 blur-xl"
      ></div>

      <!-- Spinner -->
      <div
        class="relative h-12 w-12 animate-spin rounded-full border-[3px] border-slate-200 border-t-violet-600"
      ></div>

    </div>


    <!-- Loading Text -->
    <div>

      <h3
        class="text-base font-semibold tracking-tight text-slate-900"
      >
        Uploading CSV
      </h3>

      <p
        class="mt-1 text-sm leading-5 text-slate-500"
      >
        Please wait while we import your students.
      </p>

    </div>


    <!-- Animated Dots -->
    <div
      class="mt-4 flex items-center justify-center gap-1"
      aria-hidden="true"
    >

      <span
        class="h-1.5 w-1.5 animate-bounce rounded-full bg-violet-500 [animation-delay:-0.3s]"
      ></span>

      <span
        class="h-1.5 w-1.5 animate-bounce rounded-full bg-violet-500 [animation-delay:-0.15s]"
      ></span>

      <span
        class="h-1.5 w-1.5 animate-bounce rounded-full bg-violet-500"
      ></span>

    </div>

  </div>

</div>