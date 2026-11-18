import { el } from "./elements.js";

const SEARCH_DELAY_MS = 350;

function syncClearButton() {
  const hasValue = el.search().value.length > 0;
  el.clearSearch().classList.toggle("hidden", !hasValue);
  el.clearSearch().classList.toggle("inline-flex", hasValue);
}

function clearSearch() {
  el.search().value = "";
  syncClearButton();
}

/**
 * Search box, module/department/date filters, Reset and Refresh.
 * @param {(resetPaging?: boolean) => void} reload re-fetches the table.
 */
export function initFilters(reload) {
  let timer;

  // Search (debounced), Escape and the clear (x) button.
  el.search().addEventListener("input", () => {
    syncClearButton();
    clearTimeout(timer);
    timer = setTimeout(reload, SEARCH_DELAY_MS);
  });

  el.search().addEventListener("keydown", (event) => {
    if (event.key === "Escape" && el.search().value) {
      clearSearch();
      reload();
    }
  });

  el.clearSearch().addEventListener("click", () => {
    clearSearch();
    el.search().focus();
    reload();
  });

  // Select / date filters.
  [el.module(), el.department(), el.from(), el.to()].forEach((input) =>
    input.addEventListener("change", () => reload()),
  );

  // Reset (header button + the "Clear search & filters" button in the empty state).
  const resetAll = () => {
    [el.module(), el.department(), el.from(), el.to()].forEach((input) => (input.value = ""));
    clearSearch();
    reload();
  };

  el.reset().addEventListener("click", resetAll);
  el.table().addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
  });

  // Refresh keeps the current page.
  el.refresh().addEventListener("click", () => reload(false));
}
