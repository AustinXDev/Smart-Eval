/**
 * Confirmation summary: a bottom sheet on phones, a centred dialog on desktop.
 * Esc / backdrop / "Go back" close it; focus stays inside while open and
 * returns to the Continue button afterwards. On phones it can be dragged down.
 */

const FOCUSABLE = 'button:not([disabled]), [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

export function createConfirmSheet(root, { onConfirm }) {
  const panel = root.querySelector('[role="dialog"]');
  const confirmButton = root.querySelector("#confirmBtn");
  const confirmText = root.querySelector("#confirmText");
  const spinner = root.querySelector("#confirmSpinner");

  let returnFocus = null;
  let busy = false;
  let closing = false;

  /** details: { typeLabel, rosterLabel, trigger } — trigger gets focus back on close. */
  function open(details) {
    root.querySelector("#sheetType").textContent = details.typeLabel;
    root.querySelector("#sheetRoster").textContent = details.rosterLabel;

    returnFocus = details.trigger ?? null;
    closing = false;
    root.classList.remove("hidden");
    document.body.style.overflow = "hidden";

    requestAnimationFrame(() => {
      // Skip if it was closed again before this frame ran.
      if (root.classList.contains("hidden") || closing) return;
      root.dataset.open = "true";
      confirmButton.focus();
    });
  }

  function close() {
    if (busy || root.classList.contains("hidden") || closing) return;

    closing = true;
    root.dataset.open = "false";
    document.body.style.overflow = "";
    panel.style.transform = "";

    const reduce = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    setTimeout(() => {
      root.classList.add("hidden");
      closing = false;
    }, reduce ? 0 : 260);
    returnFocus?.focus();
  }

  function setBusy(value) {
    busy = value;
    confirmButton.disabled = value;
    spinner.classList.toggle("hidden", !value);
    confirmText.textContent = value ? "Setting up…" : "Confirm and continue";
  }

  root.querySelectorAll("[data-close-sheet]").forEach((el) => el.addEventListener("click", close));
  confirmButton.addEventListener("click", () => onConfirm({ setBusy, close }));

  // Esc closes from anywhere while open (focus may not be inside the panel yet).
  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || root.classList.contains("hidden")) return;
    event.preventDefault();
    close();
  });

  root.addEventListener("keydown", (event) => {
    if (event.key !== "Tab") return;

    const items = [...panel.querySelectorAll(FOCUSABLE)].filter((el) => el.offsetParent !== null);
    if (!items.length) return;

    const first = items[0];
    const last = items[items.length - 1];
    if (event.shiftKey && document.activeElement === first) {
      event.preventDefault();
      last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
      event.preventDefault();
      first.focus();
    }
  });

  // Drag down to dismiss (phones only; the sheet is anchored to the bottom).
  let startY = null;
  panel.addEventListener("touchstart", (event) => {
    if (busy || window.innerWidth >= 640 || event.target.closest("button")) return;
    startY = event.touches[0].clientY;
    panel.style.transition = "none";
  }, { passive: true });

  panel.addEventListener("touchmove", (event) => {
    if (startY === null) return;
    const dy = Math.max(0, event.touches[0].clientY - startY);
    panel.style.transform = `translateY(${dy}px)`;
  }, { passive: true });

  panel.addEventListener("touchend", (event) => {
    if (startY === null) return;
    const dy = event.changedTouches[0].clientY - startY;
    startY = null;
    panel.style.transition = "";
    if (dy > 90) close();
    else panel.style.transform = "";
  });

  return {
    open,
    close,
    get isOpen() {
      return !root.classList.contains("hidden");
    },
  };
}
