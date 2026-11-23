import { selectEnrollmentType } from "./api/evaluationApi.js";
import { createTypeSelector, TYPES } from "./components/typeSelector.js";
import { initHelper } from "./components/helper.js";
import { updatePreview } from "./components/preview.js";
import { createActionBar } from "./components/actionBar.js";
import { createConfirmSheet } from "./components/confirmSheet.js";

const page = window.ENROLLMENT_PAGE ?? { teacherCount: 0, hasRoster: false, blockLabel: "" };

function showError(message) {
  if (window.StatusModal) {
    window.StatusModal.show("Failed", message, "error");
  } else {
    alert(message);
  }
}

function initEnrollmentPage() {
  const group = document.getElementById("typeGroup");
  const sheetRoot = document.getElementById("confirmSheet");
  if (!group || !sheetRoot) return;

  const actionBar = createActionBar({
    dot: document.getElementById("statusDot"),
    ping: document.getElementById("statusPing"),
    label: document.getElementById("statusLabel"),
    clearButton: document.getElementById("clearBtn"),
    continueButton: document.getElementById("continueBtn"),
    continueText: document.getElementById("continueText"),
  });

  let helper = null;

  const selector = createTypeSelector(group, {
    onChange(type) {
      actionBar.update(type);
      updatePreview(document, type);
      helper?.reset(type);
    },
  });

  helper = initHelper(document, { page, selector });

  const sheet = createConfirmSheet(sheetRoot, {
    async onConfirm({ setBusy, close }) {
      const type = selector.selected;
      if (!type) return;

      setBusy(true);
      selector.setLocked(true);

      try {
        const data = await selectEnrollmentType(TYPES[type].value);

        if (data?.status !== "success" || !data.redirect) {
          throw new Error(data?.message || "Unable to save your enrollment type.");
        }

        window.location.href = data.redirect;
      } catch (error) {
        setBusy(false);
        selector.setLocked(false);
        close();
        showError(error.message);
      }
    },
  });

  document.getElementById("clearBtn").addEventListener("click", () => selector.clear());

  const continueButton = document.getElementById("continueBtn");

  continueButton.addEventListener("click", () => {
    const type = selector.selected;
    if (!type) return;

    sheet.open({
      trigger: continueButton,
      typeLabel: TYPES[type].label,
      rosterLabel:
        type === "regular"
          ? `${page.teacherCount} instructors load automatically`
          : "You pick your instructors",
    });
  });

  // Desktop shortcuts: "1" = Regular, "2" = Irregular (not while typing or in the dialog).
  document.addEventListener("keydown", (event) => {
    if (sheet.isOpen || event.ctrlKey || event.metaKey || event.altKey) return;
    const target = event.target instanceof Element ? event.target : null;
    if (target?.closest("input, textarea, select, [contenteditable]")) return;
    if (!window.matchMedia("(min-width: 1024px)").matches) return;

    const type = { 1: "regular", 2: "irregular" }[event.key];
    if (type) selector.select(type, { focus: true });
  });
}

document.addEventListener("DOMContentLoaded", initEnrollmentPage);
