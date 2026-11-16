import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getDashboardData, getEvaluationPeriods } from "./api/api.js";

let table;
let latestDraw = 0;
let listError = "";

const el = {
  table: () => document.getElementById("evaluationTable"),
  search: () => document.getElementById("searchBox"),
  clearSearch: () => document.getElementById("clear-search"),
  status: () => document.getElementById("statusFilter"),
  reset: () => document.getElementById("reset-filters"),
  count: () => document.getElementById("table-count"),
};

// display_status comes from EvaluationRepository::findPage().
const STATUS = {
  Active: { badge: "bg-emerald-50 text-emerald-700 ring-emerald-600/15", dot: "bg-emerald-500" },
  Upcoming: { badge: "bg-blue-50 text-blue-700 ring-blue-600/15", dot: "bg-blue-500" },
  Archived: { badge: "bg-slate-50 text-slate-600 ring-slate-500/10", dot: "bg-slate-400" },
};

const DEPARTMENT = { college: "College", shs: "SHS" };

const dateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric", year: "numeric" });
const timeFmt = new Intl.DateTimeFormat(undefined, { hour: "numeric", minute: "2-digit" });

// MySQL "Y-m-d H:i:s" -> Date (local time, as stored).
function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

function badge(status) {
  const style = STATUS[status] ?? STATUS.Archived;
  return `<span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset ${style.badge}">
    <span class="h-1.5 w-1.5 rounded-full ${style.dot}"></span>${escapeHTML(status || "Archived")}
  </span>`;
}

// ================================================================
// TABLE
// ================================================================

export function initEvaluationTable() {
  return new Promise((resolve) => {
    $(document).ready(function () {
      table = createDataTable(
        "#evaluationTable",
        {
          serverSide: true,
          pageLength: 10,
          ordering: false,
          createdRow: stylePeriodRow,
          columnDefs: [{ className: "whitespace-nowrap", targets: [0, 1, 2, 3, 4, 5, 6] }],
          language: {
            emptyTable: emptyState(
              "fa-calendar-plus",
              "No evaluation periods yet",
              "Create a period to open evaluations for College or SHS.",
              "create",
            ),
            zeroRecords: emptyState(
              "fa-magnifying-glass",
              "No periods match your search or filters",
              "Try a different year, semester, department or status.",
              "clear",
            ),
            loadingRecords:
              '<div class="py-12 text-center text-sm text-slate-400">Loading evaluation periods…</div>',
            info: "Showing _START_ to _END_ of _TOTAL_ periods",
            infoEmpty: "Showing 0 to 0 of 0 periods",
            infoFiltered: "",
            paginate: {
              previous:
                '<span class="inline-flex items-center gap-1.5"><i class="fa-solid fa-chevron-left text-[9px]"></i><span>Previous</span></span>',
              next: '<span class="inline-flex items-center gap-1.5"><span>Next</span><i class="fa-solid fa-chevron-right text-[9px]"></i></span>',
            },
          },
          ajax: async function (request, callback) {
            const draw = Number(request.draw);
            latestDraw = draw;

            const tableElement = el.table();
            tableElement?.classList.add("opacity-60");
            tableElement?.setAttribute("aria-busy", "true");

            let result;
            try {
              const response = await getEvaluationPeriods({
                draw,
                start: request.start,
                length: request.length,
                search: el.search()?.value.trim() ?? "",
                status: el.status()?.value || "All",
              });
              if (response.status !== "success")
                throw new Error(response.message || "Unable to load evaluation periods.");
              if (
                !Array.isArray(response.data) ||
                Number(response.draw) !== draw ||
                response.recordsTotal == null ||
                response.recordsFiltered == null ||
                !Number.isInteger(Number(response.recordsTotal)) ||
                !Number.isInteger(Number(response.recordsFiltered)) ||
                Number(response.recordsTotal) < 0 ||
                Number(response.recordsFiltered) < 0
              ) {
                throw new Error("Invalid evaluation-period response.");
              }
              result = { ...response, data: response.data.map(renderPeriodRow) };
              if (draw === latestDraw) {
                listError = "";
                const count = el.count();
                if (count) count.textContent = Number(response.recordsFiltered) || 0;
              }
            } catch (error) {
              console.error(error);
              if (draw === latestDraw)
                listError = error.message || "Unable to load evaluation periods.";
              result = { draw, recordsTotal: 0, recordsFiltered: 0, data: [] };
            } finally {
              if (draw === latestDraw) {
                tableElement?.classList.remove("opacity-60");
                tableElement?.setAttribute("aria-busy", "false");
              }
            }
            callback(result);
          },
          drawCallback: function () {
            if (!listError) return;
            const cell = this.api()
              .table()
              .container()
              .querySelector("td.dataTables_empty, td.dt-empty");
            if (cell) cell.innerHTML = errorState(listError);
          },
        },
        "Search evaluation periods",
      );

      try {
        bindFilters();
      } catch (error) {
        console.error("Period filters could not be initialised:", error);
      }

      resolve(table);
    });
  });
}

function bindFilters() {
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
    timer = setTimeout(reload, 350);
  });

  el.search().addEventListener("keydown", (event) => {
    if (event.key === "Escape" && el.search().value) {
      el.search().value = "";
      syncClearButton();
      reload();
    }
  });

  el.clearSearch().addEventListener("click", () => {
    el.search().value = "";
    syncClearButton();
    el.search().focus();
    reload();
  });

  el.status().addEventListener("change", reload);

  const resetAll = () => {
    el.search().value = "";
    el.status().value = "All";
    syncClearButton();
    reload();
  };

  el.reset().addEventListener("click", resetAll);

  el.table().addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-filters]")) resetAll();
    if (event.target.closest(".retryPeriodsBtn")) loadEvaluationPeriods();
    if (event.target.closest(".emptyCreatePeriodBtn"))
      document.querySelector(".createPeriodBtn")?.click();
  });
}

export function loadEvaluationPeriods(resetPaging = true) {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, resetPaging));
}

function emptyState(icon, title, text, action = null) {
  const button = {
    create:
      '<button type="button" class="emptyCreatePeriodBtn mt-4 inline-flex h-9 items-center gap-2 rounded-xl bg-[#6010FF] px-4 text-xs font-semibold text-white shadow-sm transition hover:brightness-110"><i class="fas fa-plus text-[11px]" aria-hidden="true"></i>Create New Period</button>',
    clear:
      '<button type="button" data-clear-filters class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"><i class="fas fa-filter-circle-xmark text-[11px]" aria-hidden="true"></i>Clear search &amp; filters</button>',
  }[action] ?? "";

  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 text-xs text-slate-500">${text}</p>
      ${button}
    </div>`;
}

function errorState(message) {
  return `
    <div class="flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-rose-50 text-rose-500">
        <i class="fa-solid fa-circle-exclamation text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">Evaluation periods could not be loaded</p>
      <p class="mt-1 text-xs text-slate-500">${escapeHTML(message)}</p>
      <button type="button" class="retryPeriodsBtn mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]">
        <i class="fas fa-rotate-right text-[11px]" aria-hidden="true"></i>Try again
      </button>
    </div>`;
}

// ================================================================
// ROWS
// ================================================================

function renderDate(value, note = "") {
  const date = parseDate(value);
  if (!date) return '<span class="text-slate-400">—</span>';
  return `<time datetime="${escapeHTML(String(value))}" class="block tabular-nums">
    <span class="block font-medium text-slate-700">${dateFmt.format(date)}</span>
    <span class="block text-xs text-slate-500">${timeFmt.format(date)}${note ? ` · ${note}` : ""}</span>
  </time>`;
}

function durationLabel(startValue, endValue) {
  const start = parseDate(startValue);
  const end = parseDate(endValue);
  if (!start || !end || end < start) return "";
  const days = Math.max(1, Math.ceil((end - start) / 86_400_000));
  return `${days} day${days === 1 ? "" : "s"}`;
}

function renderPeriodRow(period) {
  const id = escapeHTML(String(period.period_id ?? ""));
  const status = period.display_status || "Archived";
  const label = `${period.academic_year ?? ""} ${period.semester ?? ""}`.trim();

  const iconButton = (cls, icon, title, hover) => `
    <button type="button" data-id="${id}" title="${title}" aria-label="${title}: ${escapeHTML(label)}"
      class="${cls} flex h-8 w-8 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 ${hover} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]">
      <i class="fa-solid ${icon} text-xs" aria-hidden="true"></i>
    </button>`;

  // Same actions per status as before: Upcoming can be edited, activated or
  // deleted; Active can be closed; Archived can download its report.
  let actions;
  if (status === "Upcoming") {
    actions = [
      iconButton("editBtn", "fa-pen", "Edit period", "hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"),
      iconButton("ActiveBtn", "fa-bolt", "Activate period", "hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"),
      iconButton("deleteBtn", "fa-trash-alt", "Delete period", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"),
    ];
  } else if (status === "Active") {
    actions = [
      iconButton("closeBtn", "fa-stop", "Close period", "hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600"),
    ];
  } else {
    actions = [
      iconButton("downloadBtn", "fa-download", "Download report", "hover:border-blue-200 hover:bg-blue-50 hover:text-blue-600"),
    ];
  }

  const dept = String(period.target_dept ?? "").toLowerCase();
  const duration = durationLabel(period.start_date, period.end_date);

  return [
    `<span class="ms-name font-semibold text-slate-900">${escapeHTML(String(period.academic_year ?? ""))}</span>`,
    `<span class="text-slate-600">${escapeHTML(String(period.semester ?? ""))}</span>`,
    `<span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">${escapeHTML(DEPARTMENT[dept] ?? (period.target_dept || "—"))}</span>`,
    renderDate(period.start_date),
    renderDate(period.end_date, duration),
    badge(status),
    `<div class="flex items-center justify-end gap-1.5">${actions.join("")}</div>`,
  ];
}

function stylePeriodRow(rowNode) {
  rowNode.classList.add(
    "border-b",
    "border-slate-100",
    "transition-colors",
    "duration-150",
    "hover:bg-slate-50/70",
  );

  rowNode
    .querySelectorAll("td")
    .forEach((td) => td.classList.add("px-5", "py-3.5", "align-middle"));
}

// ================================================================
// ACTIVE PERIOD CARDS
// ================================================================

function setText(id, value) {
  const node = document.getElementById(id);
  if (node) node.textContent = value;
}

function renderCard(key, period) {
  const status = document.getElementById(`${key}Status`);

  if (!period) {
    setText(`${key}Year`, "No active period");
    setText(`${key}Sem`, "—");
    setText(`${key}Dates`, "Create or activate a period to start evaluations.");
    setText(`${key}ProgressText`, "—");
    setText(`${key}ProgressCount`, "");
    const bar = document.getElementById(`${key}ProgressBar`);
    if (bar) bar.style.width = "0%";
    if (status) {
      status.className =
        "inline-flex shrink-0 items-center gap-1.5 rounded-full bg-amber-50 px-2.5 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-600/15";
      status.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>No active period';
    }
    return;
  }

  const total = Number(period.total_students) || 0;
  const finished = Number(period.total_finished) || 0;
  const rate = total > 0 ? (finished / total) * 100 : 0;

  setText(`${key}Year`, `A.Y. ${period.academic_year ?? ""}`);
  setText(`${key}Sem`, period.semester ?? "");

  const start = parseDate(period.start_date);
  const end = parseDate(period.end_date);
  setText(
    `${key}Dates`,
    start && end ? `${dateFmt.format(start)} – ${dateFmt.format(end)}` : "",
  );

  setText(`${key}ProgressText`, total > 0 ? `${rate.toFixed(1)}%` : "—");
  setText(
    `${key}ProgressCount`,
    `${finished.toLocaleString()} of ${total.toLocaleString()} students`,
  );
  const bar = document.getElementById(`${key}ProgressBar`);
  if (bar) bar.style.width = `${Math.min(100, rate)}%`;

  if (status) {
    status.className =
      "inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/15";
    status.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Active';
  }
}

export async function loadPeriodCard(activeData = []) {
  try {
    const byDept = { college: null, shs: null };
    (Array.isArray(activeData) ? activeData : []).forEach((p) => {
      const dept = String(p.target_dept ?? "").toLowerCase();
      if (dept in byDept && !byDept[dept]) byDept[dept] = p;
    });

    renderCard("college", byDept.college);
    renderCard("shs", byDept.shs);
  } catch (error) {
    console.error(error);
  }
}

export async function loadDashboard({
  refreshTable = true,
  resetPaging = true,
} = {}) {
  await Promise.all([
    refreshTable ? loadEvaluationPeriods(resetPaging) : Promise.resolve(),
    (async () => {
      try {
        const response = await getDashboardData({ cards_only: 1 });
        if (response.status === "error") throw new Error(response.message);
        await loadPeriodCard(response.active_periods ?? []);
      } catch (error) {
        console.error("Unable to refresh period cards:", error);
        ["college", "shs"].forEach((key) => {
          setText(`${key}Year`, "—");
          setText(`${key}Sem`, "—");
          setText(`${key}ProgressText`, "—");
        });
      }
    })(),
  ]);
}
