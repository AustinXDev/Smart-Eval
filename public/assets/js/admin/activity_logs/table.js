import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getActivityLogs } from "./api/api.js";
import { el } from "./components/elements.js";
import { moduleLabel } from "./components/formatters.js";
import { emptyState, renderRow } from "./components/logRender.js";
import { currentFilters } from "./components/state.js";

let table;
let latestDraw = 0;

const LANGUAGE = {
  emptyTable: emptyState("fa-clock-rotate-left", "No activity yet", "Entries appear here as actions happen."),
  zeroRecords: emptyState("fa-magnifying-glass", "No entries match your filters", "Try a different search, module, department or date.", true),
  loadingRecords: '<div class="py-12 text-center text-sm text-slate-400">Loading activity…</div>',
  info: "Showing _START_ to _END_ of _TOTAL_ entries",
  infoEmpty: "Showing 0 to 0 of 0 entries",
  infoFiltered: "",
  paginate: {
    previous: '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
    next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
  },
};

// ---------------------------------------------------------------- tab counts + module options

function updateCounts(counts = {}) {
  el.tabCounts().forEach((badge) => {
    badge.textContent = Number(counts[badge.dataset.logCount] ?? 0).toLocaleString();
  });
}

function updateModules(modules = []) {
  const select = el.module();
  const selected = select.value;
  while (select.options.length > 1) select.remove(1);
  modules.forEach((m) => select.add(new Option(moduleLabel(m), m)));
  // Keep the choice if this tab has it; otherwise fall back to "All".
  select.value = modules.includes(selected) ? selected : "";
}

// ---------------------------------------------------------------- loading state

function setBusy(busy) {
  el.table().classList.toggle("opacity-60", busy);
  el.table().setAttribute("aria-busy", String(busy));
}

function showLoadError(error) {
  const cell = el.table().querySelector("td.dataTables_empty");
  if (cell) {
    cell.innerHTML = emptyState("fa-circle-exclamation", "Activity logs could not be loaded", escapeHTML(error.message || "Please try again."));
  }
}

// ---------------------------------------------------------------- server-side source

async function loadPage(request, callback) {
  const draw = Number(request.draw);
  latestDraw = draw;
  setBusy(true);

  try {
    const response = await getActivityLogs({
      draw,
      start: request.start,
      length: request.length,
      ...currentFilters(),
    });

    // Ignore side effects from responses that a newer request has replaced.
    if (draw === latestDraw) {
      updateCounts(response.counts);
      updateModules(response.modules);
    }

    callback({
      draw,
      recordsTotal: Number(response.recordsTotal) || 0,
      recordsFiltered: Number(response.recordsFiltered) || 0,
      data: response.data.map(renderRow),
    });
  } catch (error) {
    console.error(error);
    callback({ draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
    showLoadError(error);
  } finally {
    if (draw === latestDraw) setBusy(false);
  }
}

function styleRow(row) {
  row.classList.add("border-b", "border-slate-100", "transition-colors", "duration-150", "hover:bg-slate-50/70");
  row.querySelectorAll("td").forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
}

// ---------------------------------------------------------------- public API

export function initTable() {
  table = createDataTable(
    "#activity-log-table",
    {
      serverSide: true,
      pageLength: 15,
      ordering: false,
      searching: false,
      columnDefs: [{ className: "whitespace-nowrap", targets: [0, 1, 2, 3, 5, 6] }],
      language: LANGUAGE,
      ajax: loadPage,
      createdRow: styleRow,
    },
    "Search activity",
  );
}

/** Re-fetch the table. resetPaging=false keeps the current page (Refresh button). */
export function reload(resetPaging = true) {
  table?.ajax.reload(null, resetPaging);
}
