import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { formatYearLevel } from "../report_analytics/modules/helpers/formatters.js";
import { getParticipationMonitoring } from "./api.js";

const TABLE = "#monitoring-student-table";

// Set by reloadSilently(): the next request is a background live update, so skip
// the dimmed/loading treatment and keep the user's current page.
let silentNext = false;
let lastGood = null;

export function reloadSilently(table) {
  silentNext = true;
  table.ajax.reload(null, false);
}

const STATUS_STYLES = {
  Completed: {
    badge: "bg-emerald-50 text-emerald-700 ring-emerald-600/15",
    dot: "bg-emerald-500",
  },
  Incomplete: {
    badge: "bg-amber-50 text-amber-700 ring-amber-600/15",
    dot: "bg-amber-500",
  },
  Unresponsive: {
    badge: "bg-rose-50 text-rose-700 ring-rose-600/15",
    dot: "bg-rose-500",
  },
};

const el = {
  search: () => document.getElementById("student-search"),
  clearSearch: () => document.getElementById("clear-search"),
  year: () => document.getElementById("year-filter"),
  status: () => document.getElementById("status-filter"),
};

function emptyState(icon, title, text, withClear = false) {
  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 text-xs text-slate-400">${text}</p>
      ${withClear ? '<button type="button" data-clear-filters class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"><i class="fas fa-filter-circle-xmark text-[11px]" aria-hidden="true"></i>Clear search &amp; filters</button>' : ""}
    </div>`;
}

// Submitted teacher evaluations only; not compared against an expected total.
function renderSubmitted(_value, _type, student) {
  const submitted = Number(student.completed_count) || 0;
  const style = STATUS_STYLES[student.status] || STATUS_STYLES.Unresponsive;
  const label = `${submitted} teacher evaluation${submitted === 1 ? "" : "s"} submitted`;

  return `
    <span class="inline-flex items-center gap-2" title="${label}">
      <span class="h-2 w-2 shrink-0 rounded-full ${submitted ? style.dot : "bg-slate-300"}"></span>
      <span class="font-semibold tabular-nums ${submitted ? "text-slate-800" : "text-slate-400"}">${submitted}</span>
      <span class="text-xs text-slate-400">submitted</span>
    </span>`;
}

function renderStatus(value, _type, student) {
  const style = STATUS_STYLES[value] || {
    badge: "bg-slate-50 text-slate-600 ring-slate-500/10",
    dot: "bg-slate-400",
  };
  const today = student.submitted_today
    ? '<span class="ml-1.5 inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-[10px] font-semibold text-blue-600 ring-1 ring-inset ring-blue-600/10" title="Submitted an evaluation today">Today</span>'
    : "";
  return `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${style.badge}"><span class="h-1.5 w-1.5 rounded-full ${style.dot}"></span>${escapeHTML(value)}</span>${today}`;
}

/**
 * Server-side DataTable. Every response also carries the KPI summary for the same
 * department/period, which is broadcast via the "monitoring:updated" event.
 */
export function initMonitoringTable(department) {
  const tableElement = document.querySelector(TABLE);

  const table = createDataTable(TABLE, {
    serverSide: true,
    pageLength: 10,
    ordering: false,
    searching: false,
    ajax: async (request, callback) => {
      const draw = Number(request.draw);
      const silent = silentNext;
      silentNext = false;
      if (!silent) {
        tableElement.setAttribute("aria-busy", "true");
        tableElement.classList.add("opacity-60");
      }
      document.dispatchEvent(
        new CustomEvent("monitoring:loading", { detail: { silent } }),
      );

      try {
        const response = await getParticipationMonitoring(department, {
          draw,
          start: request.start,
          length: request.length,
          search: el.search().value.trim(),
          year_level: el.year().value,
          status_filter: el.status().value,
        });

        if (response?.status !== "success" || !Array.isArray(response.data)) {
          throw new Error(response?.message || "Invalid monitoring response.");
        }
        // Ignore stale responses from superseded requests.
        if (Number(response.draw) !== draw) return;

        document.dispatchEvent(
          new CustomEvent("monitoring:updated", {
            detail: response.monitoring || {},
          }),
        );
        document.getElementById("table-count").textContent =
          Number(response.recordsFiltered) || 0;

        lastGood = {
          recordsTotal: Number(response.recordsTotal) || 0,
          recordsFiltered: Number(response.recordsFiltered) || 0,
          data: response.data,
        };
        callback({ draw, ...lastGood });
      } catch (error) {
        console.error("Unable to load evaluation monitoring:", error);
        document.dispatchEvent(
          new CustomEvent("monitoring:error", { detail: error.message }),
        );
        // A failed background update keeps the rows already on screen.
        callback(
          silent && lastGood
            ? { draw, ...lastGood }
            : { draw, recordsTotal: 0, recordsFiltered: 0, data: [] },
        );
      } finally {
        tableElement.setAttribute("aria-busy", "false");
        tableElement.classList.remove("opacity-60");
      }
    },
    columns: [
      {
        data: "student_id",
        render: (v) =>
          `<span class="ms-id font-medium text-slate-600">${escapeHTML(v)}</span>`,
      },
      {
        data: "full_name",
        render: (v) =>
          `<span class="ms-name font-semibold text-slate-900">${escapeHTML(v)}</span>`,
      },
      {
        data: "program_name",
        render: (v) => `<span class="ms-course text-slate-600">${escapeHTML(v)}</span>`,
      },
      {
        data: "year_level",
        render: (v) =>
          `<span class="whitespace-nowrap text-slate-600">${escapeHTML(v ? formatYearLevel(v) : "—")}</span>`,
      },
      { data: null, render: renderSubmitted },
      { data: "status", render: renderStatus },
    ],
    columnDefs: [{ className: "whitespace-nowrap", targets: [0, 3, 5] }],
    language: {
      emptyTable: emptyState(
        "fa-user-graduate",
        "No eligible students",
        "Students with required teacher evaluations in this department will appear here.",
      ),
      zeroRecords: emptyState(
        "fa-magnifying-glass",
        "No students match your search or filters",
        "Try a different name, Student ID, year level, or status.",
        true,
      ),
      loadingRecords:
        '<div class="py-12 text-center text-sm text-slate-400">Loading students…</div>',
      info: "Showing _START_ to _END_ of _TOTAL_ students",
      infoEmpty: "Showing 0 to 0 of 0 students",
      infoFiltered: "",
      paginate: {
        previous:
          '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
        next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
      },
    },
    createdRow(row) {
      row.classList.add(
        "border-b",
        "border-slate-100",
        "transition-colors",
        "duration-150",
        "hover:bg-slate-50/70",
      );
      row
        .querySelectorAll("td")
        .forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
    },
  });

  bindFilters(table);
  return table;
}

function bindFilters(table) {
  const reload = () => table.ajax.reload(null, true);
  let timer;

  const syncClearButton = () => {
    const hasValue = el.search().value.length > 0;
    el.clearSearch().classList.toggle("hidden", !hasValue);
    el.clearSearch().classList.toggle("inline-flex", hasValue);
  };

  el.search().addEventListener("input", () => {
    syncClearButton();
    clearTimeout(timer);
    timer = setTimeout(reload, 300);
  });
  el.search().addEventListener("keydown", (event) => {
    if (event.key === "Escape" && el.search().value) {
      el.search().value = "";
      syncClearButton();
      clearTimeout(timer);
      reload();
    }
  });
  el.clearSearch().addEventListener("click", () => {
    el.search().value = "";
    syncClearButton();
    clearTimeout(timer);
    el.search().focus();
    reload();
  });
  el.year().addEventListener("change", reload);
  el.status().addEventListener("change", reload);

  const resetAll = () => {
    clearTimeout(timer);
    el.search().value = "";
    el.year().value = "";
    el.status().value = "";
    syncClearButton();
    reload();
  };
  document.getElementById("reset-filters").addEventListener("click", resetAll);
  document.querySelector(TABLE).addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
  });
}

/** Rebuild year options from the department's actual year levels, keeping the current choice. */
export function populateYearFilter(years = []) {
  const select = el.year();
  const selected = select.value;
  const options = [new Option("All Year Levels", "")];
  years.forEach((year) =>
    options.push(new Option(formatYearLevel(year), year)),
  );
  select.replaceChildren(...options);
  select.value = options.some((option) => option.value === selected)
    ? selected
    : "";
}
