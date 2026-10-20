import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getDashboardData, getEvaluationPeriods } from "./api/api.js";
import { showCardLoader, hideCardLoader } from "../shared/Loader.js";

let table;
let latestDraw = 0;
let listError = "";

export function initEvaluationTable() {
  return new Promise((resolve) => {
    $(document).ready(function () {
      table = createDataTable(
        "#evaluationTable",
        {
          serverSide: true,
          processing: true,
          pageLength: 5,
          columnDefs: [{ orderable: false, searchable: false, targets: 4 }],
          createdRow: stylePeriodRow,
          language: {
            emptyTable: renderPeriodState("No evaluation period records found"),
            zeroRecords: renderPeriodState("No matching evaluation periods"),
            processing:
              '<span class="period-loading" role="status"><span class="period-loading__icon"><span class="period-loading__spinner" aria-hidden="true"></span></span><strong>Loading evaluation period records...</strong><span class="period-loading__subtitle">Please wait a moment...</span></span>',
            info: "Showing _START_ to _END_ of _TOTAL_ records",
            infoEmpty: "Showing 0 to 0 of 0 records",
            paginate: { previous: "Previous", next: "Next" },
          },
          ajax: async function (request, callback) {
            const draw = Number(request.draw);
            latestDraw = draw;
            const tableElement = document.getElementById("evaluationTable");
            tableElement?.classList.add("periods-loading");
            tableElement?.setAttribute("aria-busy", "true");
            const loader = document.getElementById(
              "evaluationTable_processing",
            );
            if (loader) loader.style.display = "block";
            try {
              let result;
              try {
                const response = await getEvaluationPeriods({
                  draw,
                  start: request.start,
                  length: request.length,
                  search: request.search.value,
                  status: $("#statusFilter").val() || "All",
                });
                if (response.status !== "success")
                  throw new Error(
                    response.message || "Unable to load evaluation periods.",
                  );
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
                result = {
                  ...response,
                  data: response.data.map(renderPeriodRow),
                };
                if (draw === latestDraw) listError = "";
              } catch (error) {
                console.error(error);
                if (draw === latestDraw)
                  listError =
                    error.message || "Unable to load evaluation periods.";
                result = {
                  draw,
                  recordsTotal: 0,
                  recordsFiltered: 0,
                  data: [],
                };
              }
              callback(result);
            } finally {
              if (draw === latestDraw) {
                if (loader) loader.style.display = "none";
                tableElement?.classList.remove("periods-loading");
                tableElement?.setAttribute("aria-busy", "false");
              }
            }
          },
          drawCallback: function () {
            if (!listError) return;
            const cell = this.api()
              .table()
              .container()
              .querySelector("td.dataTables_empty, td.dt-empty");
            if (cell) cell.innerHTML = renderPeriodState(listError, true);
          },
        },
        "Search evaluation periods",
      );

      let searchTimer;
      $("#searchBox").on("input", function () {
        const value = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => table.search(value).draw(), 350);
      });
      $("#statusFilter").on("change", function () {
        clearTimeout(searchTimer);
        table.search($("#searchBox").val() || "").draw();
      });
      $("#evaluationTable").on("click", ".retryPeriodsBtn", () =>
        loadEvaluationPeriods(),
      );
      $("#evaluationTable").on("click", ".emptyCreatePeriodBtn", () =>
        document.querySelector(".createPeriodBtn")?.click(),
      );
      resolve(table);
    });
  });
}

export function loadEvaluationPeriods(resetPaging = true) {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, resetPaging));
}

function renderPeriodState(message, error = false) {
  return `<div class="flex flex-col items-center justify-center py-14 text-center">
    <p class="text-sm font-semibold text-slate-700">${escapeHTML(message)}</p>
    <p class="mt-1 text-xs text-slate-400">${error ? "Please try again." : "Try changing your search or filters."}</p>
    <button type="button" class="${error ? "retryPeriodsBtn" : "emptyCreatePeriodBtn"} mt-4 rounded-lg bg-violet-600 px-4 py-2 text-xs text-white">${error ? "Try Again" : "Create Period"}</button>
  </div>`;
}

function renderPeriodRow(period) {
  let statusBadge, buttons;

  const baseBtn =
    "flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[10px] font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-violet-500/20";

  if (period.display_status === "Archived") {
    statusBadge = `
        <div class="flex justify-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                Archived
            </span>
        </div>
    `;

    buttons = `
        <div class="flex items-center justify-center gap-1.5">
            <button
                class="downloadBtn ${baseBtn} hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Download Report"
                aria-label="Download Report"
            >
                <i class="fa-solid fa-download text-[10px]"></i>
            </button>
        </div>
    `;
  } else if (period.display_status === "Active") {
    // ACTIVE

    statusBadge = `
        <div class="flex justify-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                Active
            </span>
        </div>
    `;

    buttons = `
        <div class="flex items-center justify-center gap-1.5">
            <button
                class="closeBtn ${baseBtn} border-violet-200 bg-violet-50 text-violet-600 hover:border-violet-300 hover:bg-violet-100"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Close Period"
                aria-label="Close Period"
            >
                <i class="fa-solid fa-stop text-[9px]"></i>
            </button>
        </div>
    `;
  } else if (period.display_status === "Upcoming") {
    // UPCOMING

    statusBadge = `
        <div class="flex justify-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
                Upcoming
            </span>
        </div>
    `;

    buttons = `
        <div class="flex items-center justify-center gap-1.5">
            <button
                class="editBtn ${baseBtn}"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Edit Period"
                aria-label="Edit Period"
            >
                <i class="fa-solid fa-pen text-[10px]"></i>
            </button>

            <button
                class="ActiveBtn ${baseBtn} hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Activate Period"
                aria-label="Activate Period"
            >
                <i class="fa-solid fa-bolt text-[10px]"></i>
            </button>

            <button
                class="deleteBtn ${baseBtn} hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Delete Period"
                aria-label="Delete Period"
            >
                <i class="fa-solid fa-trash text-[10px]"></i>
            </button>
        </div>
    `;
  } else {
    // EXPIRED → ARCHIVED

    statusBadge = `
        <div class="flex justify-center">
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                Archived
            </span>
        </div>
    `;

    buttons = `
        <div class="flex items-center justify-center gap-1.5">
            <button
                class="downloadBtn ${baseBtn} hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600"
                data-id="${escapeHTML(String(period.period_id ?? ""))}"
                title="Download Report"
                aria-label="Download Report"
            >
                <i class="fa-solid fa-download text-[10px]"></i>
            </button>
        </div>
    `;
  }

  const academicYear = `
          <div class="flex items-center gap-3">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-slate-50 text-slate-500">
              <i class="fa-solid fa-calendar text-[10px]"></i>
            </div>
            <div class="min-w-0">
              <p class="truncate text-sm font-semibold text-slate-800">${escapeHTML(String(period.academic_year ?? ""))}</p>
              <p class="mt-0.5 text-[11px] text-slate-400">Academic Year</p>
            </div>
          </div>
        `;

  const semester = `
          <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
            <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
            ${escapeHTML(String(period.semester ?? ""))}
          </span>
        `;

  const restriction = `
          <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
            ${escapeHTML(period.target_dept || "—")}
          </span>
        `;

  return [academicYear, semester, statusBadge.trim(), restriction, buttons];
}

function stylePeriodRow(rowNode) {
  rowNode.classList.add(
    "group",
    "border-b",
    "border-slate-100",
    "transition-colors",
    "duration-150",
    "hover:bg-slate-50/70",
  );

  rowNode.querySelectorAll("td").forEach((td, index) => {
    td.classList.add("px-5", "py-4", "align-middle", "whitespace-nowrap");

    if (index === 4) {
      td.classList.add("text-right");
    }
  });

  rowNode
    .querySelectorAll("button")
    .forEach((btn) => btn.classList.add("cursor-pointer"));
}

export async function loadPeriodCard(activeData = []) {
  showCardLoader([
    "collegeStatus",
    "collegeProgressText",
    "collegeProgressBar",
    "collegeProgressCount",
    "shsStatus",
    "shsProgressText",
    "shsProgressBar",
    "shsProgressCount",
  ]);
  try {
    // Default values
    let college = {
      year: "--",
      sem: "--",
      total: 0,
      total_finished: "--",
      status: "No Active",
      participation: "--",
    };

    let shs = {
      year: "--",
      sem: "--",
      total: 0,
      total_finished: "--",
      status: "No Active",
      participation: "--",
    };

    if (activeData.length > 0) {
      activeData.forEach((p) => {
        const totalStudents = Number(p.total_students) || 0;
        const totalFinished =
          p.total_finished !== undefined && p.total_finished !== null
            ? Number(p.total_finished)
            : null;

        const participation =
          totalFinished !== null && totalStudents > 0
            ? Math.round((totalFinished / totalStudents) * 100)
            : "--";

        const status = Number(p.is_active) === 1 ? "Active" : "Inactive";

        if (p.target_dept.toLowerCase() === "college") {
          college.year = p.academic_year;
          college.sem = p.semester;
          college.total = totalStudents;
          college.total_finished =
            totalFinished !== null ? totalFinished : "--";
          college.status = status;
          college.participation = participation;
        } else if (p.target_dept.toLowerCase() === "shs") {
          shs.year = p.academic_year;
          shs.sem = p.semester;
          shs.total = totalStudents;
          shs.total_finished = totalFinished !== null ? totalFinished : "--";
          shs.status = status;
          shs.participation = participation;
        }
      });
    }

    // Update College Card
    document.getElementById("collegeYear").textContent = college.year;
    document.getElementById("collegeSem").textContent = college.sem;
    document.getElementById("collegeStatus").textContent = college.status;
    document.getElementById("collegeProgressText").textContent =
      college.participation !== "--"
        ? college.participation + "% Completed"
        : "--";
    document.getElementById("collegeProgressBar").style.width =
      college.participation !== "--" ? college.participation + "%" : "0%";
    document.getElementById("collegeProgressCount").textContent =
      `${college.total_finished} / ${college.total} Students`;

    // Update SHS Card
    document.getElementById("shsYear").textContent = shs.year;
    document.getElementById("shsSem").textContent = shs.sem;
    document.getElementById("shsStatus").textContent = shs.status;
    document.getElementById("shsProgressText").textContent =
      shs.participation !== "--" ? shs.participation + "% Completed" : "--";
    document.getElementById("shsProgressBar").style.width =
      shs.participation !== "--" ? shs.participation + "%" : "0%";
    document.getElementById("shsProgressCount").textContent =
      `${shs.total_finished} / ${shs.total} Students`;
  } catch (error) {
    console.error(error);
  } finally {
    hideCardLoader([
      "collegeStatus",
      "collegeProgressText",
      "collegeProgressBar",
      "collegeProgressCount",
      "shsStatus",
      "shsProgressText",
      "shsProgressBar",
      "shsProgressCount",
    ]);
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
      }
    })(),
  ]);
}
