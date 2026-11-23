import { escapeHTML } from "../../../services/escape.js";

/**
 * Confirmation summary: a bottom sheet on phones, a centred dialog on desktop.
 * Esc / backdrop / "Go back" close it; focus stays inside while open and
 * returns to the Continue button afterwards. On phones it can be dragged down.
 */

const FOCUSABLE = 'button:not([disabled]), [href], input, select, textarea, [tabindex]:not([tabindex="-1"])';

function teacherRow(t) {
  const avatar = t.photo
    ? `<img src="${escapeHTML(t.photo)}" alt="" class="h-9 w-9 shrink-0 rounded-lg border border-[#E2E8F0] object-cover">`
    : `<span class="ff-display flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#EEF2FF] text-xs font-bold text-[#4338CA]" aria-hidden="true">${escapeHTML(t.initials)}</span>`;

  return `
    <li class="flex items-center gap-3 px-3 py-2.5">
      ${avatar}
      <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-[#0F172A]">${escapeHTML(t.name)}</p>
        <p class="ff-mono truncate text-[11px] text-[#64748B]">${escapeHTML(t.employee)}</p>
      </div>
      <span class="ff-mono shrink-0 rounded-md border border-[#C7D2FE] bg-[#EEF2FF] px-2 py-0.5 text-[11px] font-semibold text-[#4338CA]">${escapeHTML(t.badge)}</span>
    </li>`;
}

export function createConfirmSheet(root, { onConfirm }) {
  const panel = root.querySelector('[role="dialog"]');
  const list = root.querySelector("#sheetList");
  const count = root.querySelector("#sheetCount");
  const confirmButton = root.querySelector("#confirmBtn");
  const confirmText = root.querySelector("#confirmText");
  const spinner = root.querySelector("#confirmSpinner");

  let returnFocus = null;
  let busy = false;
  let closing = false;

  /** teachers: [{ name, employee, initials, photo, badge }]; trigger gets focus back on close. */
  function open({ teachers, trigger }) {
    count.textContent = teachers.length;
    list.innerHTML = teachers.map(teacherRow).join("");
    list.scrollTop = 0;

    returnFocus = trigger ?? null;
    closing = false;
    root.classList.remove("hidden");
    document.body.style.overflow = "hidden";

    requestAnimationFrame(() => {
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
    confirmText.textContent = value ? "Saving…" : "Confirm and continue";
  }

  root.querySelectorAll("[data-close-sheet]").forEach((el) => el.addEventListener("click", close));
  confirmButton.addEventListener("click", () => onConfirm({ setBusy, close }));

  // Esc closes from anywhere while open (focus may not be inside the panel yet).
  document.addEventListener("keydown", (event) => {
    if (event.key !== "Escape" || root.classList.contains("hidden")) return;
    event.preventDefault();
    close();
  });

  // Keep Tab inside the dialog.
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

  // Drag the handle area down to dismiss (phones only). Starting inside the
  // scrollable list is ignored so scrolling the list still works.
  let startY = null;
  panel.addEventListener("touchstart", (event) => {
    if (busy || window.innerWidth >= 640 || event.target.closest("button, #sheetList")) return;
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
