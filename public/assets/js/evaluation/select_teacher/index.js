import { submitTeacherSelection } from "./api/api.js";
import { createRoster } from "./components/roster.js";
import { createChipStrip } from "./components/chips.js";
import { createActionBar } from "./components/actionBar.js";
import { createConfirmSheet } from "./components/confirmSheet.js";

const $ = (id) => document.getElementById(id);

function showError(message) {
  if (window.StatusModal) {
    window.StatusModal.show("Failed", message, "error");
  } else {
    alert(message);
  }
}

function isTyping(target) {
  return (
    target instanceof Element &&
    !!target.closest("input, textarea, select, [contenteditable]")
  );
}

function initTeacherSelection() {
  const rosterRoot = $("roster");
  if (!rosterRoot) return; // no instructors set up: the page shows a message instead

  const search = $("teacherSearch");
  const clearSearch = $("clearSearch");
  const searchKbd = $("searchKbd");
  const toggleAll = $("toggleAllBtn");
  const continueButton = $("continueBtn");

  const updateBar = createActionBar({
    dot: $("statusDot"),
    ping: $("statusPing"),
    label: $("statusLabel"),
    clearButton: $("clearBtn"),
    continueButton,
    continueText: $("continueText"),
  });

  let renderChips = () => {};

  const roster = createRoster(rosterRoot, {
    onChange(teachers) {
      renderChips(teachers);
      updateBar(teachers.length);
      syncToggleAll();
    },
  });

  renderChips = createChipStrip(
    {
      strip: $("chipStrip"),
      list: $("chipList"),
      count: $("chipCount"),
      clearAll: $("chipClearAll"),
    },
    { onRemove: (id) => roster.toggle(id), onClear: () => roster.clear() },
  );

  // ---------- Select all / Deselect all (applies to the cards shown) ----------

  function syncToggleAll() {
    const all = roster.allVisibleSelected();
    const filtered = search.value.trim() !== "";
    toggleAll.textContent = `${all ? "Deselect" : "Select"} all${filtered ? " shown" : ""}`;
    toggleAll.dataset.mode = all ? "deselect" : "select";
  }

  toggleAll.addEventListener("click", () => {
    roster.setVisible(toggleAll.dataset.mode !== "deselect");
  });

  // ---------- Search + Total Faculty badge ----------

  function applySearch() {
    const query = search.value;
    const { shown, total } = roster.filter(query);
    const has = query.trim() !== "";

    clearSearch.classList.toggle("hidden", !has);
    clearSearch.classList.toggle("flex", has);
    searchKbd.classList.toggle("lg:block", !has);

    $("facultyCount").textContent = has ? `${shown} of ${total}` : total;
    $("facultyLabel").textContent = has ? "Faculty" : "Total Faculty";

    rosterRoot.hidden = shown === 0;
    $("noResults").classList.toggle("hidden", shown !== 0);
    $("noResultsQuery").textContent = query.trim();

    toggleAll.disabled = shown === 0;
    toggleAll.classList.toggle("opacity-50", shown === 0);
    syncToggleAll();
  }

  function resetSearch() {
    search.value = "";
    applySearch();
    search.focus();
  }

  search.addEventListener("input", applySearch);
  search.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && search.value) {
      event.stopPropagation();
      search.value = "";
      applySearch();
    }
  });
  clearSearch.addEventListener("click", resetSearch);
  $("noResultsClear").addEventListener("click", resetSearch);
  $("clearBtn").addEventListener("click", () => roster.clear());

  // ---------- Confirm + save ----------

  const sheet = createConfirmSheet($("confirmSheet"), {
    async onConfirm({ setBusy, close }) {
      const ids = roster.selectedIds;
      if (!ids.length) return;

      setBusy(true);
      try {
        const data = await submitTeacherSelection(ids.map(Number));
        if (!data?.success) {
          throw new Error(
            data?.message ||
              data?.error ||
              "Unable to save your teacher selection.",
          );
        }
        window.location.href = data.redirect || "evaluation";
      } catch (error) {
        setBusy(false);
        close();
        showError(
          error.message ||
            "Unable to save your teacher selection. Please try again.",
        );
      }
    },
  });

  continueButton.addEventListener("click", () => {
    const teachers = roster.selected;
    if (!teachers.length) return;
    sheet.open({ teachers, trigger: continueButton });
  });

  // ---------- Keyboard shortcuts ----------
  // "/" focuses search; 1–9 toggle the first nine cards shown.
  document.addEventListener("keydown", (event) => {
    if (
      sheet.isOpen ||
      event.ctrlKey ||
      event.metaKey ||
      event.altKey ||
      isTyping(event.target)
    )
      return;

    if (event.key === "/") {
      event.preventDefault();
      search.focus();
      search.select();
      return;
    }

    if (/^[1-9]$/.test(event.key)) {
      const card = roster.visibleAt(Number(event.key) - 1);
      if (!card) return;
      event.preventDefault();
      roster.toggle(card.dataset.id);
      card.focus();
    }
  });

  // Ready: swap the loading placeholders for the real cards and enable the controls.
  $("rosterLoading").hidden = true;
  search.disabled = false;
  toggleAll.disabled = false;

  applySearch();
  updateBar(0);
}

document.addEventListener("DOMContentLoaded", initTeacherSelection);
