import { escapeHTML } from "../../../services/escape.js";
import { actionStyle, dateFmt, label, moduleLabel, parseDate, timeFmt } from "./formatters.js";

const DASH = '<span class="text-slate-400">—</span>';

const ACTOR_ICONS = { admin: "fa-user-shield", student: "fa-user-graduate", system: "fa-gears" };

export function renderWhen(value) {
  const date = parseDate(value);
  if (!date) return DASH;
  return `<time datetime="${escapeHTML(value)}" class="block tabular-nums">
    <span class="block font-medium text-slate-700">${dateFmt.format(date)}</span>
    <span class="block text-xs text-slate-500">${timeFmt.format(date)}</span>
  </time>`;
}

function actorSubtitle(row) {
  if (row.actor_type === "admin") {
    if (row.actor_id === null) return "Unverified (sign-in attempt)";
    return row.actor_role ? label(String(row.actor_role).replace(/\s+/g, "_")) : "Administrator";
  }
  if (row.actor_type === "student") {
    return row.actor_id ? `Student ID ${escapeHTML(row.actor_id)}` : "Student (unverified)";
  }
  return "Scheduled job";
}

export function renderActor(row) {
  const name = escapeHTML(row.actor_name || (row.actor_type === "system" ? "System" : "Unknown"));
  const icon = ACTOR_ICONS[row.actor_type] ?? "fa-user";

  return `<span class="flex items-center gap-3">
    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#6010FF]/10 text-[#6010FF]" aria-hidden="true"><i class="fa-solid ${icon} text-xs"></i></span>
    <span class="min-w-0">
      <span class="ms-name block max-w-[220px] truncate font-semibold text-slate-900" title="${name}">${name}</span>
      <span class="block text-xs text-slate-500">${actorSubtitle(row)}</span>
    </span>
  </span>`;
}

export function renderModule(value) {
  return `<span class="text-slate-600">${escapeHTML(moduleLabel(value))}</span>`;
}

export function renderAction(action) {
  const [badge, dot] = actionStyle(action);
  return `<span class="inline-flex items-center gap-1.5 whitespace-nowrap rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${badge}">
    <span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${escapeHTML(label(action))}
  </span>`;
}

// Extra details from metadata, shown under the description (plain values only).
function renderDetails(metadata) {
  if (!metadata || typeof metadata !== "object") return "";
  const parts = Object.entries(metadata)
    .filter(([, v]) => v !== null && v !== "" && typeof v !== "object")
    .slice(0, 4)
    .map(([k, v]) => `${escapeHTML(label(k))}: ${escapeHTML(String(v))}`);
  return parts.length ? `<span class="mt-0.5 block text-xs text-slate-500">${parts.join(" · ")}</span>` : "";
}

export function renderDescription(row) {
  return `<span class="block min-w-[260px] max-w-[460px] whitespace-normal text-slate-700">${escapeHTML(row.description)}${renderDetails(row.metadata)}</span>`;
}

export function renderDepartment(value) {
  if (!value) return DASH;
  return `<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">${value === "shs" ? "SHS" : "College"}</span>`;
}

export function renderIp(value) {
  return `<span class="tabular-nums text-xs text-slate-500">${escapeHTML(value || "—")}</span>`;
}

/** One API row -> the table's 7 cells, in column order. */
export function renderRow(row) {
  return [
    renderWhen(row.created_at),
    renderActor(row),
    renderModule(row.module),
    renderAction(row.action),
    renderDescription(row),
    renderDepartment(row.department),
    renderIp(row.ip_address),
  ];
}

export function emptyState(icon, title, text, withClear = false) {
  const clearButton = withClear
    ? '<button type="button" data-clear-filters class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"><i class="fas fa-filter-circle-xmark text-[11px]" aria-hidden="true"></i>Clear search &amp; filters</button>'
    : "";

  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 text-xs text-slate-500">${text}</p>
      ${clearButton}
    </div>`;
}
