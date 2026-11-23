import { escapeHTML } from "../../../services/escape.js";

/**
 * "Selected" chip strip under the roster header. Each chip removes its
 * teacher; focus moves to the next chip (or Clear all) so keyboard users
 * aren't dropped back to the top of the page.
 */
export function createChipStrip({ strip, list, count, clearAll }, { onRemove, onClear }) {
  clearAll.addEventListener("click", onClear);

  list.addEventListener("click", (event) => {
    const button = event.target.closest("[data-remove-id]");
    if (!button) return;

    const chips = [...list.querySelectorAll("[data-remove-id]")];
    const index = chips.indexOf(button);
    onRemove(button.dataset.removeId);

    const next = [...list.querySelectorAll("[data-remove-id]")];
    (next[index] ?? next[index - 1] ?? clearAll).focus();
  });

  return function render(teachers) {
    strip.classList.toggle("hidden", teachers.length === 0);
    count.textContent = teachers.length;

    list.innerHTML = teachers
      .map(
        (t) => `
        <li>
          <button type="button" data-remove-id="${escapeHTML(t.id)}"
            class="inline-flex min-h-9 items-center gap-2 rounded-full border border-[#C7D2FE] bg-white py-1 pl-3 pr-2 text-xs text-[#0F172A] transition-colors hover:border-[#4F46E5] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]"
            aria-label="Remove ${escapeHTML(t.name)}">
            <span class="ff-mono font-semibold text-[#4338CA]">${escapeHTML(t.badge)}</span>
            <span class="text-[#94A3B8]" aria-hidden="true">·</span>
            <span class="font-medium">${escapeHTML(t.name)}</span>
            <span class="flex h-5 w-5 items-center justify-center rounded-full text-[#64748B]" aria-hidden="true">
              <i class="fa-solid fa-xmark text-[10px]"></i>
            </span>
          </button>
        </li>`,
      )
      .join("");
  };
}
