import { createDataTable } from "../shared/datatable_config.js";
import { getDashboardData } from "./api/api.js";
import {
  showTableLoader,
  hideTableLoader,
  showCardLoader,
  hideCardLoader,
} from "../shared/Loader.js";

let table;
let lastPeriodsHash = null;

export function initEvaluationTable() {
  return new Promise((resolve) => {
    $(document).ready(function () {
      table = createDataTable(
        "#evaluationTable",
        { columnDefs: [{ orderable: false, targets: 4 }] },
        "Search evaluation periods",
      );

      if (!table) {
        console.error("Failed to initialize evaluation DataTable.");
        resolve(null);
        return;
      }

      $("#statusFilter").on("change", function () {
        const status = $(this).val();
        const statusColumn = table.column(2);
        if (status === "All") return statusColumn.search("").draw();
        statusColumn.search(status, false, false).draw();
      });

      $("#searchBox").on("keyup", function () {
        table.search(this.value).draw();
      });

      resolve(table);
    });
  });
}

export async function loadEvaluationPeriods(periods = []) {
  try {
    const hash = JSON.stringify(periods);

    if (hash === lastPeriodsHash) return;

    lastPeriodsHash = hash;

    showTableLoader("#evaluationTable", "Loading evaluation period records...");

    table.clear();

    if (periods.length === 0) {
      const tbody = document.querySelector("#evaluationTable tbody");

      if (tbody) {
        tbody.innerHTML = `
          <tr>
            <td colspan="5" class="px-5 py-16">
              <div class="flex flex-col items-center justify-center text-center">
                <div class="mb-5 flex h-16 w-16 items-center justify-center rounded-2xl border border-violet-100 bg-violet-50 text-violet-500">
                  <i class="fa-solid fa-calendar-days text-xl"></i>
                </div>

                <h3 class="text-sm font-semibold text-slate-800">
                  No evaluation period records found
                </h3>

                <p class="mt-1.5 max-w-sm text-xs leading-relaxed text-slate-400">
                  There are currently no evaluation periods available for this department.
                </p>

                <button
                  type="button"
                  class="mt-5 inline-flex items-center gap-2 rounded-xl border border-violet-200 bg-white px-4 py-2 text-xs font-semibold text-violet-600 shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:border-violet-300 hover:bg-violet-50 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/10 active:translate-y-0 active:scale-[0.98]"
                  onclick="document.querySelector('.createPeriodBtn')?.click()"
                >
                  <i class="fa-solid fa-plus text-[10px]"></i>
                  Create Period
                </button>
              </div>
            </td>
          </tr>
        `;
      }

      return;
    }

    periods.forEach((period) => {
      const today = new Date();
      const start = new Date(period.start_date);
      const end = new Date(period.end_date);

      const isActive = Number(period.is_active) === 1;
      const isClosed = Number(period.is_closed) === 1;
      const isForced = Number(period.is_forced) === 1;

      let statusBadge, buttons;

      const baseBtn =
        "flex h-8 items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-2.5 text-[10px] font-medium shadow-sm transition-all duration-200 hover:-translate-y-0.5 focus:outline-none focus:ring-2 focus:ring-violet-500/20";

      if (isClosed) {
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
                data-id="${period.period_id}"
                title="Download Report"
                aria-label="Download Report"
            >
                <i class="fa-solid fa-download text-[10px]"></i>
            </button>
        </div>
    `;
      } else if (isActive || isForced || (today >= start && today <= end)) {
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
                data-id="${period.period_id}"
                title="Close Period"
                aria-label="Close Period"
            >
                <i class="fa-solid fa-stop text-[9px]"></i>
            </button>
        </div>
    `;
      } else if (today < start) {
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
                data-id="${period.period_id}"
                title="Edit Period"
                aria-label="Edit Period"
            >
                <i class="fa-solid fa-pen text-[10px]"></i>
            </button>

            <button
                class="ActiveBtn ${baseBtn} hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-600"
                data-id="${period.period_id}"
                title="Activate Period"
                aria-label="Activate Period"
            >
                <i class="fa-solid fa-bolt text-[10px]"></i>
            </button>

            <button
                class="deleteBtn ${baseBtn} hover:border-red-200 hover:bg-red-50 hover:text-red-600"
                data-id="${period.period_id}"
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
                data-id="${period.period_id}"
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
              <p class="truncate text-sm font-semibold text-slate-800">${period.academic_year}</p>
              <p class="mt-0.5 text-[11px] text-slate-400">Academic Year</p>
            </div>
          </div>
        `;

      const semester = `
          <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
            <span class="h-1.5 w-1.5 rounded-full bg-violet-500"></span>
            ${period.semester}
          </span>
        `;

      const restriction = `
          <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-600">
            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
            ${period.target_dept || "—"}
          </span>
        `;

      const rowNode = table.row
        .add([academicYear, semester, statusBadge.trim(), restriction, buttons])
        .draw(false)
        .node();

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
    });
  } catch (error) {
    console.log(error);
  } finally {
    hideTableLoader("#evaluationTable");
  }
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

        const status = p.is_active === 1 ? "Active" : "Inactive";

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

export async function loadDashboard() {
  try {
    const data = await getDashboardData();

    if (data.status !== "error") {
      loadEvaluationPeriods(data.all_periods ?? []);
      loadPeriodCard(data.active_periods ?? []);
    }
  } catch (error) {
    console.error("Dashboard initialization error:", error);
  }
}
