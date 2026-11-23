import { escapeHTML } from "../../../services/escape.js";

const STATUS = {
  progress: { label: "In Progress", cls: "border-amber-200 bg-amber-50 text-amber-800", dot: "bg-amber-500" },
  ready: { label: "Ready to Continue", cls: "border-[#C7D2FE] bg-[#EEF2FF] text-[#4338CA]", dot: "bg-[#4F46E5]" },
  submitted: { label: "Submitted", cls: "border-emerald-200 bg-emerald-50 text-emerald-800", dot: "bg-emerald-500" },
};

function avatarHTML(teacher, size) {
  if (!teacher.photo) return escapeHTML(teacher.initials);
  // On a missing file, fall back to initials (and stop using the photo elsewhere).
  return `<img src="${escapeHTML(teacher.photo)}" alt="" class="h-full w-full object-cover" data-size="${size}"
    data-fallback="${escapeHTML(teacher.initials)}" onerror="this.parentElement.textContent=this.dataset.fallback">`;
}

/** Teacher switcher: earlier tabs always open; later ones unlock once every teacher before them is complete. */
export function renderTabs(store, { onSelect }) {
  const list = document.getElementById("teacherTabs");
  const total = store.teachers.length;

  // Status dot: amber = in progress, indigo = ready, green = submitted.
  const DOT = { progress: "bg-amber-500", ready: "bg-[#818CF8]", submitted: "bg-emerald-500" };

  list.innerHTML = store.teachers
    .map((t, i) => {
      const current = i === store.current;
      const unlocked = store.isUnlocked(i);
      const status = store.status(t);
      const state = !unlocked ? "locked" : STATUS[status].label;
      const dot = current && status !== "submitted" ? "bg-white/70" : DOT[status];

      return `
        <li class="shrink-0">
          <button type="button" data-tab="${i}" ${current ? 'aria-current="step"' : ""} ${unlocked ? "" : 'aria-disabled="true"'}
            title="${escapeHTML(t.name)}"
            aria-label="Teacher ${i + 1} of ${total}: ${escapeHTML(t.name)}, ${state}"
            class="flex h-11 items-center gap-2 whitespace-nowrap rounded-lg px-4 text-sm font-semibold transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5] focus-visible:ring-offset-1
              ${current ? "bg-[#4F46E5] text-white" : unlocked ? "text-[#475569] hover:bg-[#F8FAFC] hover:text-[#0F172A]" : "cursor-not-allowed text-[#94A3B8]"}">
            ${unlocked ? "" : '<i class="fa-solid fa-lock text-[11px]" aria-hidden="true"></i>'}
            <span>Teacher ${i + 1} of ${total}</span>
            <span class="h-1.5 w-1.5 shrink-0 rounded-full ${dot}" aria-hidden="true"></span>
          </button>
        </li>`;
    })
    .join("");

  list.querySelectorAll("[data-tab]").forEach((button) => {
    button.addEventListener("click", () => {
      if (button.getAttribute("aria-disabled") === "true") return;
      onSelect(Number(button.dataset.tab));
    });
  });

  // Keep the current tab visible by scrolling the tab strip sideways only.
  // (scrollIntoView would also scroll the page back up to the tabs on every answer.)
  const active = list.querySelector('[aria-current="step"]');
  if (active) {
    const left = active.offsetLeft - list.offsetLeft;
    const right = left + active.offsetWidth;
    if (left < list.scrollLeft) list.scrollLeft = left;
    else if (right > list.scrollLeft + list.clientWidth) list.scrollLeft = right - list.clientWidth;
  }
}

export function renderTeacherCard(store) {
  const t = store.teachers[store.current];
  const status = STATUS[store.status(t)];

  document.getElementById("teacherAvatar").innerHTML = avatarHTML(t, 56);
  document.getElementById("teacherBadge").textContent = t.badge;
  document.getElementById("teacherName").textContent = t.name;
  document.getElementById("teacherMeta").textContent = [t.meta, t.handles].filter(Boolean).join(" · ");

  const pill = document.getElementById("teacherStatus");
  pill.className = `inline-flex items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold ${status.cls}`;
  pill.innerHTML = `<span class="h-1.5 w-1.5 rounded-full ${status.dot}" aria-hidden="true"></span>${status.label}`;
}
