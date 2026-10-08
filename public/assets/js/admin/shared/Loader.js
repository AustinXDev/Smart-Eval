// ================================================================
// TABLE LOADING UI
// ================================================================

export function showTableLoader(selector, message = "Loading records...") {
  const table = document.querySelector(selector);

  if (!table) return;

  const tbody = table.querySelector("tbody");

  if (!tbody) return;

  tbody.innerHTML = `
    <tr>
      <td colspan="100%" class="px-5 py-12">
        <div class="flex flex-col items-center justify-center text-center">

          <div class="
            mb-3
            flex h-11 w-11
            items-center justify-center
            rounded-xl
            bg-violet-50
            text-violet-600
          ">
            <span class="
              h-5 w-5
              animate-spin
              rounded-full
              border-2
              border-violet-200
              border-t-violet-600
            "></span>
          </div>

          <p class="text-sm font-semibold text-slate-700">
            ${message}
          </p>

          <p class="mt-1 text-xs text-slate-400">
            Please wait a moment...
          </p>

        </div>
      </td>
    </tr>
  `;
}

// ================================================================
// HIDE TABLE LOADER
// ================================================================

export function hideTableLoader(selector) {
  const table = document.querySelector(selector);

  if (!table) return;

  const loader = table.querySelector("tbody [data-table-loader]");

  if (loader) {
    loader.remove();
  }
}

// ================================================================
// CARD LOADING
// ================================================================

export function showCardLoader(cardIds = []) {
  cardIds.forEach((id) => {
    const element = document.getElementById(id);

    if (!element) return;

    element.innerHTML = `
      <span
        class="
          inline-block
          h-7
          w-16
          rounded-md
          bg-slate-100
          animate-pulse
        "
      ></span>
    `;
  });
}

// ================================================================
// HIDE CARD LOADER
// ================================================================

export function hideCardLoader(cardIds = []) {
  cardIds.forEach((id) => {
    const element = document.getElementById(id);

    if (!element) return;

    element.classList.remove("data-loading");
  });
}

// ================================================================
// QUESTIONNAIRE SET CARD LOADER
// ================================================================

export function showSetCardLoader(containerSelector, count = 3) {
  const container = document.querySelector(containerSelector);

  if (!container) return;

  container.innerHTML = Array.from(
    { length: count },
    () => `
    <div class="
      relative overflow-hidden
      rounded-2xl
      border border-slate-200
      bg-white
      p-5
      shadow-sm
    ">

      <!-- Top accent -->
      <div class="absolute left-0 top-0 h-1 w-full bg-slate-200"></div>

      <!-- Header -->
      <div class="flex items-start justify-between gap-4">

        <div class="flex min-w-0 items-center gap-4">

          <!-- Icon skeleton -->
          <div class="
            h-11 w-11 shrink-0
            rounded-xl
            bg-slate-100
            animate-pulse
          "></div>

          <div class="min-w-0 space-y-2">

            <!-- Title -->
            <div class="
              h-4 w-36
              rounded-md
              bg-slate-100
              animate-pulse
            "></div>

            <!-- Questions -->
            <div class="
              h-3 w-24
              rounded-md
              bg-slate-100
              animate-pulse
            "></div>

          </div>
        </div>

        <!-- Badge -->
        <div class="
          h-6 w-20
          shrink-0
          rounded-full
          bg-slate-100
          animate-pulse
        "></div>

      </div>


      <!-- Divider -->
      <div class="my-5 h-px bg-slate-100"></div>


      <!-- Metadata -->
      <div class="flex items-center justify-between gap-4">

        <div class="space-y-2">
          <div class="
            h-2.5 w-20
            rounded
            bg-slate-100
            animate-pulse
          "></div>

          <div class="
            h-3.5 w-28
            rounded
            bg-slate-100
            animate-pulse
          "></div>
        </div>

        <div class="space-y-2 text-right">
          <div class="
            ml-auto
            h-2.5 w-16
            rounded
            bg-slate-100
            animate-pulse
          "></div>

          <div class="
            ml-auto
            h-3.5 w-8
            rounded
            bg-slate-100
            animate-pulse
          "></div>
        </div>

      </div>


      <!-- Buttons -->
      <div class="
        mt-5
        flex items-center gap-2
        border-t border-slate-100
        pt-4
      ">

        <div class="
          h-10 flex-1
          rounded-xl
          bg-slate-100
          animate-pulse
        "></div>

        <div class="
          h-10 w-20
          rounded-xl
          bg-slate-100
          animate-pulse
        "></div>

        <div class="
          h-10 w-10
          rounded-xl
          bg-slate-100
          animate-pulse
        "></div>

      </div>

    </div>
  `,
  ).join("");
}

// ================================================================
// HIDE SET CARD LOADER
// ================================================================

export function hideSetCardLoader(containerSelector) {
  const container = document.querySelector(containerSelector);

  if (!container) return;

  container.innerHTML = "";
}

// =====================================================================
//SET LOADING STATE
//=====================================================================

export function setLoading(message = "Loading...", status = true) {
  const existingLoader = document.getElementById("globalLoadingState");

  // ============================================================
  // HIDE
  // ============================================================

  if (!status) {
    if (existingLoader) {
      existingLoader.classList.add("opacity-0");

      setTimeout(() => {
        existingLoader.remove();
      }, 200);
    }

    return;
  }

  // ============================================================
  // UPDATE EXISTING LOADER
  // ============================================================

  if (existingLoader) {
    const messageElement = existingLoader.querySelector(
      "[data-loading-message]",
    );

    if (messageElement) {
      messageElement.textContent = message;
    }

    return;
  }

  // ============================================================
  // CREATE
  // ============================================================

  const loader = document.createElement("div");

  loader.id = "globalLoadingState";

  loader.className = `
    fixed inset-0 z-[9999]
    flex items-center justify-center
    bg-white/90
    backdrop-blur-sm
    p-6
    opacity-0
    transition-opacity duration-200
  `;

  loader.setAttribute("role", "status");
  loader.setAttribute("aria-live", "polite");
  loader.setAttribute("aria-busy", "true");

  loader.innerHTML = `
    <!-- Loader Content -->
    <div
      class="
        flex w-full max-w-[280px]
        flex-col items-center
        rounded-2xl
        border border-slate-200/80
        bg-white
        px-7 py-8
        text-center
        shadow-[0_10px_40px_rgba(15,23,42,0.08)]
      "
    >

      <!-- Animated Ring -->
      <div class="relative flex h-14 w-14 items-center justify-center">

        <!-- Soft pulse -->
        <div
          class="
            absolute inset-0
            rounded-full
            bg-indigo-500/10
            animate-ping
          "
          style="animation-duration: 2s;"
        ></div>

        <!-- Outer ring -->
        <div
          class="
            absolute inset-0
            rounded-full
            border-[3px]
            border-slate-100
          "
        ></div>

        <!-- Spinning ring -->
        <div
          class="
            absolute inset-0
            rounded-full
            border-[3px]
            border-transparent
            border-t-indigo-600
            border-r-indigo-400
            animate-spin
          "
          style="animation-duration: 0.8s;"
        ></div>

        <!-- Center -->
        <div
          class="
            h-2.5 w-2.5
            rounded-full
            bg-indigo-600
            shadow-sm
          "
        ></div>

      </div>


      <!-- Loading Text -->
      <p
        data-loading-message
        class="
          mt-5
          text-sm
          font-semibold
          tracking-tight
          text-slate-800
        "
      >
        ${message}
      </p>


      <!-- Supporting Text -->
      <p
        class="
          mt-1.5
          max-w-[220px]
          text-xs
          leading-5
          text-slate-400
        "
      >
        Please wait while we prepare everything for you.
      </p>


      <!-- Progress Indicator -->
      <div
        class="
          mt-5
          h-1
          w-28
          overflow-hidden
          rounded-full
          bg-slate-100
        "
      >
        <div
          class="
            h-full
            w-1/2
            rounded-full
            bg-indigo-600
            animate-[loadingProgress_1.2s_ease-in-out_infinite]
          "
        ></div>
      </div>

    </div>
  `;

  // Add animation once
  if (!document.getElementById("globalLoadingStyles")) {
    const style = document.createElement("style");

    style.id = "globalLoadingStyles";

    style.textContent = `
      @keyframes loadingProgress {
        0% {
          transform: translateX(-120%);
          opacity: 0.4;
        }

        50% {
          opacity: 1;
        }

        100% {
          transform: translateX(220%);
          opacity: 0.4;
        }
      }

      @media (prefers-reduced-motion: reduce) {
        #globalLoadingState *,
        #globalLoadingState {
          animation-duration: 0.01ms !important;
          animation-iteration-count: 1 !important;
          transition-duration: 0.01ms !important;
        }
      }
    `;

    document.head.appendChild(style);
  }

  document.body.appendChild(loader);

  // Trigger fade-in
  requestAnimationFrame(() => {
    loader.classList.remove("opacity-0");
  });
}
