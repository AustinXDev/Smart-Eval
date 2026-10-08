export function confirm(teachers = []) {
  return new Promise((resolve) => {
    const confirmation = document.createElement("div");

    confirmation.className = `
      fixed inset-0 z-[9999]
      flex items-center justify-center
      bg-slate-950/50 backdrop-blur-sm
      p-4
    `;

    const teacherList = teachers
      .map(
        (teacher) => `
          <div
            class="
              flex items-center gap-3
              rounded-xl
              border border-slate-200
              bg-slate-50
              px-4 py-3
            "
          >
            <div
              class="
                flex h-10 w-10 shrink-0
                items-center justify-center
                rounded-xl
                bg-violet-100
                text-sm font-semibold
                text-violet-700
              "
            >
              ${(teacher.full_name ?? "T")
                .split(" ")
                .map((name) => name[0])
                .slice(0, 2)
                .join("")
                .toUpperCase()}
            </div>

            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-900">
                ${teacher.full_name ?? "Unknown Teacher"}
              </p>

              <p class="mt-0.5 truncate text-xs text-slate-500">
                ${teacher.department ?? "Teacher"}
              </p>
            </div>

            <svg
              class="ml-auto h-5 w-5 shrink-0 text-emerald-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="m5 12 4 4L19 6"
              />
            </svg>
          </div>
        `,
      )
      .join("");

    confirmation.innerHTML = `
      <div
        class="
          w-full max-w-lg
          overflow-hidden
          rounded-2xl
          border border-slate-200
          bg-white
          shadow-2xl shadow-slate-900/20
        "
      >

        <!-- HEADER -->
        <div class="border-b border-slate-100 px-6 py-5">
          <div class="flex items-start gap-3">

            <div
              class="
                flex h-11 w-11 shrink-0
                items-center justify-center
                rounded-xl
                bg-violet-100
                text-violet-600
              "
            >
              <svg
                class="h-6 w-6"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 12.75 11.25 15 15 9.75"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9 4.03-9 9-9 9 4.03 9 9Z"
                />
              </svg>
            </div>

            <div class="min-w-0">
              <h2 class="text-base font-semibold text-slate-900">
                Review Your Teacher Selection
              </h2>

              <p class="mt-1 text-sm leading-5 text-slate-500">
                Please review the teachers you selected before continuing
                to the evaluation.
              </p>
            </div>

          </div>
        </div>

        <!-- SUMMARY -->
        <div class="px-6 py-5">

          <div
            class="
              mb-4 flex items-center justify-between
              rounded-xl
              border border-violet-100
              bg-violet-50/60
              px-4 py-3
            "
          >
            <div>
              <p class="text-xs font-medium text-violet-600">
                Selected Teachers
              </p>

              <p class="mt-0.5 text-sm text-slate-600">
                Teachers included in this evaluation
              </p>
            </div>

            <span
              class="
                flex h-8 min-w-8 items-center justify-center
                rounded-full
                bg-violet-600
                px-2.5
                text-sm font-bold
                text-white
              "
            >
              ${teachers.length}
            </span>
          </div>

          <!-- TEACHER LIST -->
          <div
            class="
              max-h-64
              space-y-2
              overflow-y-auto
              pr-1
            "
          >
            ${
              teacherList ||
              `
                <div class="py-8 text-center">
                  <p class="text-sm text-slate-500">
                    No teachers selected.
                  </p>
                </div>
              `
            }
          </div>

          <!-- NOTICE -->
          <div
            class="
              mt-4 flex gap-2.5
              rounded-xl
              border border-amber-100
              bg-amber-50
              px-4 py-3
            "
          >
            <svg
              class="mt-0.5 h-4 w-4 shrink-0 text-amber-600"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M12 9v3.75m0 3h.008M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"
              />
            </svg>

            <p class="text-xs leading-5 text-amber-800">
              Make sure the selected teachers are correct. You will
              proceed to the evaluation for these teachers.
            </p>
          </div>

        </div>

        <!-- FOOTER -->
        <div
          class="
            flex flex-col-reverse gap-2
            border-t border-slate-100
            bg-slate-50/70
            px-6 py-4
            sm:flex-row sm:justify-end
          "
        >

          <button
            type="button"
            data-confirm-cancel
            class="
              rounded-xl
              border border-slate-200
              bg-white
              px-4 py-2.5
              text-sm font-medium text-slate-700
              transition
              hover:bg-slate-100
              focus:outline-none
              focus:ring-4
              focus:ring-slate-500/10
            "
          >
            Review Again
          </button>

          <button
            type="button"
            data-confirm-ok
            class="
              inline-flex items-center justify-center gap-2
              rounded-xl
              bg-violet-600
              px-5 py-2.5
              text-sm font-semibold
              text-white
              shadow-sm
              transition
              hover:bg-violet-700
              focus:outline-none
              focus:ring-4
              focus:ring-violet-500/20
            "
          >
            Confirm & Continue

            <svg
              class="h-4 w-4"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M5 12h14m-6-6 6 6-6 6"
              />
            </svg>
          </button>

        </div>
      </div>
    `;

    document.body.appendChild(confirmation);

    const close = (result) => {
      confirmation.remove();
      resolve(result);
    };

    confirmation
      .querySelector("[data-confirm-cancel]")
      .addEventListener("click", () => close(false));

    confirmation
      .querySelector("[data-confirm-ok]")
      .addEventListener("click", () => close(true));

    confirmation.addEventListener("click", (event) => {
      if (event.target === confirmation) {
        close(false);
      }
    });
  });
}
