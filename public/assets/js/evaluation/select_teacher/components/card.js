const SELECTED_CLASSES = [
  "border-violet-500",
  "bg-violet-50",
  "ring-2",
  "ring-violet-500/20",
];

export function createTeacherCard(teacher, { isSelected, onToggle }) {
  const card = document.createElement("div");

  card.className = [
    "group relative cursor-pointer overflow-hidden",
    "rounded-2xl border border-slate-200 bg-white",
    "p-4 shadow-sm",
    "transition-all duration-200 ease-out",
    "hover:-translate-y-0.5",
    "hover:border-violet-200",
    "hover:shadow-lg hover:shadow-slate-200/60",
    "focus:outline-none",
    "focus-visible:ring-4 focus-visible:ring-violet-500/20",
    isSelected ? SELECTED_CLASSES.join(" ") : "",
  ]
    .join(" ")
    .trim();

  const image = teacher.image_path
    ? teacher.image_path
    : teacher.profile_image
      ? teacher.profile_image
      : null;

  const initials = getInitials(teacher.full_name);

  card.innerHTML = `
    <!-- Selected Indicator -->
    <div
      class="absolute right-3 top-3 z-10
             flex h-7 w-7 items-center justify-center
             rounded-full border
             ${
               isSelected
                 ? "border-violet-600 bg-violet-600 text-white"
                 : "border-slate-200 bg-white text-transparent"
             }
             shadow-sm transition-all duration-200"
      data-selection-indicator
      aria-hidden="true"
    >
      <svg
        class="h-4 w-4"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="m5 12 4 4L19 6"
        />
      </svg>
    </div>


    <!-- Teacher Image -->
    <div class="flex justify-center pt-2">

      <div
        class="relative flex h-24 w-24 items-center
               justify-center overflow-hidden
               rounded-2xl border-4 border-white
               bg-violet-100
               text-xl font-bold text-violet-700
               shadow-md ring-1 ring-slate-200
               transition duration-200
               group-hover:ring-violet-200"
      >

        ${
          image
            ? `
              <img
                src="${window.BASE_URL}uploads/teachers/${image}"
                alt="${escapeHtml(teacher.full_name)}"
                class="h-full w-full object-cover"
                loading="lazy"
              />
            `
            : `
              <span>
                ${initials}
              </span>
            `
        }

      </div>

    </div>


    <!-- Teacher Information -->
    <div class="mt-4 text-center">

      <h3
        class="truncate text-base font-bold
               text-slate-900"
        title="${escapeHtml(teacher.full_name)}"
      >
        ${escapeHtml(teacher.full_name)}
      </h3>

      <p
        class="mt-1 truncate text-xs
               font-medium text-slate-500"
        title="${escapeHtml(teacher.department ?? "")}"
      >
        ${escapeHtml(teacher.department ?? "Department not specified")}
      </p>

      ${
        teacher.subject
          ? `
            <p
              class="mt-2 inline-flex max-w-full
                     items-center rounded-lg
                     bg-slate-100 px-2.5 py-1
                     text-[11px] font-medium
                     text-slate-600"
              title="${escapeHtml(teacher.subject)}"
            >
              <span class="truncate">
                ${escapeHtml(teacher.subject)}
              </span>
            </p>
          `
          : ""
      }

    </div>


    <!-- Action -->
    <div class="mt-5">

      <div
        data-select-label
        class="flex h-10 w-full items-center
               justify-center gap-2 rounded-xl
               ${
                 isSelected
                   ? "bg-violet-600 text-white"
                   : "bg-violet-50 text-violet-700"
               }
               text-xs font-semibold
               transition-all duration-200
               group-hover:bg-violet-600
               group-hover:text-white"
      >

        <span data-select-text>
          ${isSelected ? "Selected Teacher" : "Select Teacher"}
        </span>

        <svg
          data-select-icon
          class="h-4 w-4 transition-transform duration-200
                 group-hover:translate-x-0.5"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          ${
            isSelected
              ? `
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m5 12 4 4L19 6"
                />
              `
              : `
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="m9 18 6-6-6-6"
                />
              `
          }
        </svg>

      </div>

    </div>
  `;

  card.setAttribute("data-teacher-id", teacher.teacher_id);

  card.setAttribute("role", "button");
  card.setAttribute("tabindex", "0");
  card.setAttribute("aria-pressed", isSelected ? "true" : "false");
  card.setAttribute(
    "aria-label",
    `${isSelected ? "Selected" : "Select"} teacher ${teacher.full_name}`,
  );

  card.addEventListener("click", () => onToggle(teacher.teacher_id, card));

  card.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
      event.preventDefault();
      onToggle(teacher.teacher_id, card);
    }
  });

  return card;
}

export function setCardSelected(card, isSelected) {
  SELECTED_CLASSES.forEach((className) => {
    card.classList.toggle(className, isSelected);
  });

  card.setAttribute("aria-pressed", isSelected ? "true" : "false");

  const indicator = card.querySelector("[data-selection-indicator]");

  if (indicator) {
    indicator.className = `
      absolute right-3 top-3 z-10
      flex h-7 w-7 items-center justify-center
      rounded-full border shadow-sm
      transition-all duration-200
      ${
        isSelected
          ? "border-violet-600 bg-violet-600 text-white"
          : "border-slate-200 bg-white text-transparent"
      }
    `;
  }

  const selectLabel = card.querySelector("[data-select-label]");

  if (selectLabel) {
    selectLabel.classList.toggle("bg-violet-600", isSelected);

    selectLabel.classList.toggle("text-white", isSelected);

    selectLabel.classList.toggle("bg-violet-50", !isSelected);

    selectLabel.classList.toggle("text-violet-700", !isSelected);
  }

  const selectText = card.querySelector("[data-select-text]");

  if (selectText) {
    selectText.textContent = isSelected ? "Selected Teacher" : "Select Teacher";
  }

  const selectIcon = card.querySelector("[data-select-icon]");

  if (selectIcon) {
    selectIcon.innerHTML = isSelected
      ? `
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="m5 12 4 4L19 6"
        />
      `
      : `
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="m9 18 6-6-6-6"
        />
      `;
  }
}

/* ============================================================
   HELPERS
============================================================ */

function getInitials(name = "") {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((word) => word.charAt(0).toUpperCase())
    .join("");
}

function escapeHtml(value = "") {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}
