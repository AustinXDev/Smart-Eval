import { escapeHTML } from "../../../services/escape.js";

/**
 * Final review: overall mean, then one card per instructor with stars, mean
 * and an expandable list of every answer. Bottom sheet on phones (drag the
 * handle down to close), centred dialog on desktop.
 */

const FOCUSABLE = 'button:not([disabled]), [href], summary, input, select, textarea, [tabindex]:not([tabindex="-1"])';

const stars = (score, label) =>
  `<span class="stars text-base" role="img" aria-label="${label}"><span style="width:${(Math.max(0, Math.min(5, score)) / 5) * 100}%"></span></span>`;

const fmt = (n) => n.toFixed(2);

function teacherCard(store, t, ratings) {
  const mean = store.mean(t.id);
  const comment = store.getComment(t.id).trim();
  // Initials box; the photo (if any) sits inside it and removes itself if it fails to load.
  const avatar = `
    <span class="ff-display flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#EEF2FF] text-xs font-bold text-[#4338CA]" aria-hidden="true">
      ${t.photo
        ? `<img src="${escapeHTML(t.photo)}" alt="" class="h-full w-full object-cover" data-fallback="${escapeHTML(t.initials)}" onerror="this.parentElement.textContent=this.dataset.fallback">`
        : escapeHTML(t.initials)}
    </span>`;

  const rows = store.questions
    .map((q, i) => {
      const score = Number(store.getAnswer(t.id, q.id) ?? 0);
      const r = ratings[score] ?? { emoji: "", label: "Not answered" };
      return `
        <li class="py-2.5">
          <p class="text-sm text-[#0F172A]"><span class="ff-mono mr-1 text-[11px] text-[#94A3B8]">${String(i + 1).padStart(2, "0")}</span>${escapeHTML(q.text)}</p>
          <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-[#475569]">
            ${stars(score, `${score} out of 5 stars`)}
            <span>${r.emoji} ${escapeHTML(r.label)}</span>
            <span class="ff-mono text-[#64748B]">${score} / 5</span>
          </p>
        </li>`;
    })
    .join("");

  return `
    <article class="rounded-xl border border-[#E2E8F0] p-4">
      <div class="flex items-center gap-3">
        ${avatar}
        <div class="min-w-0 flex-1">
          <p class="truncate text-sm font-semibold text-[#0F172A]">${escapeHTML(t.name)}</p>
          <p class="ff-mono truncate text-[11px] text-[#64748B]">${escapeHTML(t.badge)}</p>
        </div>
        <div class="shrink-0 text-right">
          ${stars(mean, `${fmt(mean)} out of 5 stars`)}
          <p class="ff-mono mt-0.5 text-sm font-semibold text-[#0F172A]">${fmt(mean)} <span class="font-normal text-[#64748B]">/ 5.00</span></p>
        </div>
      </div>
      <details class="group mt-3 rounded-lg border border-[#E2E8F0]">
        <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between gap-2 rounded-lg px-3 text-sm font-semibold text-[#4F46E5] hover:bg-[#F8FAFC] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] [&::-webkit-details-marker]:hidden">
          Review all ${store.itemTotal} answers
          <i class="fa-solid fa-chevron-down text-xs transition-transform group-open:rotate-180" aria-hidden="true"></i>
        </summary>
        <ol class="divide-y divide-[#E2E8F0] border-t border-[#E2E8F0] px-3">${rows}
          <li class="py-2.5">
            <p class="text-sm text-[#0F172A]"><span class="ff-mono mr-1 text-[11px] text-[#94A3B8]">${String(store.itemTotal).padStart(2, "0")}</span>Additional comment</p>
            <blockquote class="mt-1.5 border-l-2 border-[#C7D2FE] pl-3 text-sm italic leading-6 text-[#475569]">“${escapeHTML(comment)}”</blockquote>
          </li>
        </ol>
      </details>
    </article>`;
}

export function createReviewSheet(store, ratings, { onSubmit }) {
  const root = document.getElementById("reviewSheet");
  const panel = root.querySelector('[role="dialog"]');
  const body = document.getElementById("reviewBody");
  const submitButton = document.getElementById("submitAllBtn");
  const submitText = document.getElementById("submitText");
  const spinner = document.getElementById("submitSpinner");

  let returnFocus = null;
  let busy = false;
  let closing = false;

  function render() {
    const means = store.teachers.map((t) => store.mean(t.id));
    const overall = means.reduce((a, b) => a + b, 0) / (means.length || 1);

    body.innerHTML = `
      <div class="rounded-xl border border-[#C7D2FE] bg-[#EEF2FF] p-4">
        <p class="text-xs font-semibold uppercase tracking-wide text-[#4338CA]">Overall mean</p>
        <div class="mt-1 flex flex-wrap items-end justify-between gap-2">
          <p class="ff-mono text-2xl font-semibold text-[#0F172A]">${fmt(overall)} <span class="text-base font-normal text-[#475569]">/ 5.00</span></p>
          ${stars(overall, `${fmt(overall)} out of 5 stars`).replace("text-base", "text-xl")}
        </div>
        <p class="mt-1 text-xs text-[#475569]">Average across your ${store.teachers.length} instructor${store.teachers.length === 1 ? "" : "s"}.</p>
      </div>
      <div class="mt-3 space-y-3 pb-4">${store.teachers.map((t) => teacherCard(store, t, ratings)).join("")}</div>`;
  }

  function open(trigger) {
    render();
    body.scrollTop = 0;
    returnFocus = trigger ?? null;
    closing = false;
    root.classList.remove("hidden");
    document.body.style.overflow = "hidden";
    requestAnimationFrame(() => {
      if (root.classList.contains("hidden") || closing) return;
      root.dataset.open = "true";
      submitButton.focus();
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

  function setBusy(value, label = "Submitting…") {
    busy = value;
    submitButton.disabled = value;
    spinner.classList.toggle("hidden", !value);
    submitText.textContent = value ? label : "Submit all evaluations";
  }

  root.querySelectorAll("[data-close-sheet]").forEach((el) => el.addEventListener("click", close));
  submitButton.addEventListener("click", () => onSubmit({ setBusy, close }));

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

  // Drag down from the handle / title area to close (phones only).
  let startY = null;
  panel.addEventListener("touchstart", (event) => {
    if (busy || window.innerWidth >= 640 || !event.target.closest("[data-drag-handle]") || event.target.closest("button")) return;
    startY = event.touches[0].clientY;
    panel.style.transition = "none";
  }, { passive: true });
  panel.addEventListener("touchmove", (event) => {
    if (startY === null) return;
    panel.style.transform = `translateY(${Math.max(0, event.touches[0].clientY - startY)}px)`;
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
