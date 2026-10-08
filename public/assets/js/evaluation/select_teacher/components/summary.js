export function updateSummaryUI({
  grid,
  count,
  list,
  summary,
  proceedBtn,
  selectionState,
}) {
  const selectedIds = selectionState.getAll();

  // ==========================================================
  // NO TEACHER SELECTED
  // ==========================================================

  if (selectedIds.length === 0) {
    summary.classList.add("hidden");
    proceedBtn.disabled = true;

    return;
  }

  // ==========================================================
  // SHOW SUMMARY
  // ==========================================================

  summary.classList.remove("hidden");

  count.textContent = selectedIds.length;

  // ==========================================================
  // GET SELECTED TEACHER NAMES
  // ==========================================================

  const names = [];

  grid.querySelectorAll("[data-teacher-id]").forEach((card) => {
    const teacherId = parseInt(card.dataset.teacherId, 10);

    if (selectedIds.includes(teacherId)) {
      const name = card.querySelector("h3")?.textContent.trim();

      if (name) {
        names.push(name);
      }
    }
  });

  // ==========================================================
  // RENDER SELECTED TEACHERS
  // ==========================================================

  list.innerHTML = "";

  if (names.length > 0) {
    names.forEach((name) => {
      const chip = document.createElement("span");

      chip.className = `
        inline-flex
        items-center
        gap-1.5
        rounded-lg
        border
        border-violet-100
        bg-violet-50
        px-2.5
        py-1.5
        text-xs
        font-medium
        text-violet-700
      `;

      chip.innerHTML = `
        <svg
          class="h-3.5 w-3.5 shrink-0 text-violet-500"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
          aria-hidden="true"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="m5 12 4 4L19 6"
          />
        </svg>

        <span>${name}</span>
      `;

      list.appendChild(chip);
    });
  }

  // ==========================================================
  // ENABLE PROCEED
  // ==========================================================

  proceedBtn.disabled = false;
}

export function setProceedButtonState(proceedBtn, { disabled, text }) {
  proceedBtn.disabled = disabled;

  proceedBtn.textContent = text;
}
