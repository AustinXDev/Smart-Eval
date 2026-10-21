import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getPrograms } from "./api/api.js";
import { showCardLoader, hideCardLoader } from "../shared/Loader.js";

let table;
let latestDraw = 0;
let listError = "";

$(document).ready(async function () {
  table = createDataTable(
    "#programTable",
    {
      serverSide: true,
      processing: true,
      pageLength: 5,
      ordering: false,
      columnDefs: [{ orderable: false, searchable: false, targets: 4 }],
      language: {
        emptyTable: renderEmptyState(),
        zeroRecords: renderEmptyState(true),
        processing: `<span class="program-loading" role="status">
        <span class="program-loading__icon"><span class="program-loading__spinner"></span></span>
        <strong>Loading program records...</strong>
        <span>Please wait a moment...</span>
      </span>`,
        info: "Showing _START_ to _END_ of _TOTAL_ records",
        infoEmpty: "Showing 0 to 0 of 0 records",
        infoFiltered: "(filtered from _MAX_ total records)",
        paginate: { previous: "Previous", next: "Next" },
      },
      ajax: async function (request, callback) {
        const draw = Number(request.draw);
        latestDraw = draw;
        const element = document.getElementById("programTable");
        const loader = document.getElementById("programTable_processing");
        element?.classList.add("is-loading");
        element?.setAttribute("aria-busy", "true");
        if (loader) loader.style.display = "block";
        let result;
        try {
          const response = await getPrograms({
            draw,
            start: request.start,
            length: request.length,
            search: request.search.value,
            department: $("#departmentFilter").val() || "All",
          });
          if (response?.status !== "success") {
            throw new Error(response?.message || "Unable to load programs.");
          }
          if (
            !Array.isArray(response.data) ||
            Number(response.draw) !== draw ||
            !Number.isInteger(Number(response.recordsTotal)) ||
            !Number.isInteger(Number(response.recordsFiltered)) ||
            Number(response.recordsTotal) < 0 ||
            Number(response.recordsFiltered) < 0
          ) {
            throw new Error("Invalid program-list response.");
          }
          result = {
            draw,
            recordsTotal: Number(response.recordsTotal),
            recordsFiltered: Number(response.recordsFiltered),
            data: buildProgramRows(response.data),
          };
          if (draw === latestDraw) listError = "";
        } catch (error) {
          console.error("Error loading programs:", error);
          if (draw === latestDraw)
            listError = error.message || "Unable to load programs.";
          result = { draw, recordsTotal: 0, recordsFiltered: 0, data: [] };
        }
        try {
          callback(result);
        } finally {
          if (draw === latestDraw) {
            if (loader) loader.style.display = "none";
            element?.classList.remove("is-loading");
            element?.setAttribute("aria-busy", "false");
          }
        }
      },
      createdRow(row) {
        $(row).addClass(
          "group border-b border-slate-100 transition-colors duration-150 hover:bg-slate-50/70",
        );
        $(row).find("td").addClass("px-5 py-4 align-middle whitespace-nowrap");
        $(row).find("td:last-child").addClass("text-right");
      },
      drawCallback() {
        if (!listError) return;
        const cell = this.api()
          .table()
          .container()
          .querySelector("td.dataTables_empty, td.dt-empty");
        if (cell) cell.innerHTML = renderErrorState(listError);
      },
    },
    "Search programs",
  );

  let searchTimer;
  $("#searchBox").on("input", function () {
    clearTimeout(searchTimer);
    const value = this.value;
    searchTimer = setTimeout(() => {
      if (table.search() !== value) table.search(value).draw();
    }, 350);
  });
  $("#departmentFilter").on("change", () => {
    clearTimeout(searchTimer);
    table.search($("#searchBox").val() || "").draw();
  });
  $("#programTable").on("click", ".retryPrograms", () => loadPrograms());
  // DataTables issues the initial list request.
  await loadProgramCard();
});

export function loadPrograms() {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, true));
}

function renderEmptyState(filtered = false) {
  return `<div class="py-12 text-center">
    <p class="font-semibold text-slate-700">${filtered ? "No matching programs" : "No programs found"}</p>
    <p class="mt-1 text-sm text-slate-400">${filtered ? "Try adjusting your search or department filter." : "There are currently no program records."}</p>
  </div>`;
}

function renderErrorState(message) {
  return `<div class="py-12 text-center">
    <p class="font-semibold text-slate-700">Unable to load programs</p>
    <p class="mt-1 text-sm text-slate-400">${escapeHTML(message)}</p>
    <button type="button" class="retryPrograms mt-4 rounded-lg bg-violet-600 px-4 py-2 text-white">Try Again</button>
  </div>`;
}

function buildProgramRows(programs) {
  return programs.map((p) => {
    const isActive = Number(p.is_active) === 1;

    // ========================================================
    // PROGRAM CODE
    // ========================================================

    const programCode = `
        <span
          class="
            inline-flex items-center
            rounded-lg
            border border-violet-100
            bg-violet-50
            px-2.5 py-1
            text-xs font-semibold
            tracking-wide
            text-violet-700
          "
        >
          ${escapeHTML(p.program_code)}
        </span>
      `;

    // ========================================================
    // PROGRAM NAME
    // ========================================================

    const programName = `
        <div class="flex items-center gap-3">

          <div
            class="
              flex h-9 w-9 shrink-0
              items-center justify-center
              rounded-lg
              bg-slate-50
              text-slate-500
              transition-colors
            "
          >
            <i class="fa-solid fa-graduation-cap text-xs"></i>
          </div>

          <div class="min-w-0">

            <p
              class="
                truncate
                text-sm font-semibold
                text-slate-800
              "
            >
              ${escapeHTML(p.program_name)}
            </p>

            <p class="mt-0.5 text-[11px] text-slate-400">
              Academic Program
            </p>

          </div>

        </div>
      `;

    // ========================================================
    // DEPARTMENT
    // ========================================================

    const department = `
        <span
          class="
            inline-flex items-center gap-2
            text-sm font-medium
            text-slate-600
          "
        >
          <span
            class="
              h-1.5 w-1.5
              rounded-full
              bg-violet-500
            "
          ></span>

          ${escapeHTML(String(p.department).toUpperCase())}
        </span>
      `;

    // ========================================================
    // STATUS
    // ========================================================

    const status = `
      <div class="flex justify-center items-center">
        <span
          class="
            inline-flex items-center gap-1.5
            rounded-full
            px-2.5 py-1
            text-[11px]
            font-semibold
            ${
              isActive
                ? "bg-emerald-50 text-emerald-700"
                : "bg-red-50 text-red-600"
            }
          "
        >
        
          <span
            class="
              h-1.5 w-1.5
              rounded-full
              ${isActive ? "bg-emerald-500" : "bg-red-500"}
            "
          ></span>

          ${isActive ? "Active" : "Inactive"}

        </span>
      </div>
      `;

    // ========================================================
    // ACTIONS
    // ========================================================

    const actions = `
        <div class="flex items-center justify-center gap-1.5">

          <button
            type="button"

            data-program-id="${escapeHTML(String(p.program_id))}"
            data-program-code="${escapeHTML(p.program_code)}"
            data-program-name="${escapeHTML(p.program_name)}"
            data-program-department="${escapeHTML(p.department)}"

            class="
              editProgram
              flex h-8 w-8
              items-center justify-center
              rounded-lg
              border border-slate-200
              bg-white
              text-slate-500
              shadow-sm
              transition-all duration-200

              hover:-translate-y-0.5
              hover:border-violet-200
              hover:bg-violet-50
              hover:text-violet-600

              focus:outline-none
              focus:ring-2
              focus:ring-violet-500/20
            "

            title="Edit Program"
            aria-label="Edit Program"
          >
            <i class="fa-solid fa-pen text-[10px]"></i>
          </button>


          <button
            type="button"

            data-program-id="${escapeHTML(String(p.program_id))}"

            class="
              deleteProgram
              flex h-8 w-8
              items-center justify-center
              rounded-lg
              border border-slate-200
              bg-white
              text-slate-500
              shadow-sm
              transition-all duration-200

              hover:-translate-y-0.5
              hover:border-red-200
              hover:bg-red-50
              hover:text-red-600

              focus:outline-none
              focus:ring-2
              focus:ring-red-500/20
            "

            title="Delete Program"
            aria-label="Delete Program"
          >
            <i class="fa-solid fa-trash-can text-[10px]"></i>
          </button>

        </div>
      `;

    // ========================================================
    // ADD ROW
    // ========================================================

    return [programCode, programName, department, status, actions];
  });
}

export async function loadProgramCard() {
  showCardLoader(["total-programs", "total-active", "total-inactive"]);

  try {
    const response = await getPrograms({ counts_only: 1 });

    if (response.status !== "success") {
      throw new Error(response.message || "Unable to load program counts.");
    }

    const counts = response.counts ?? {
      total: 0,
      active: 0,
      inactive: 0,
    };

    document
      .getElementById("total-programs")
      ?.replaceChildren(String(Number(counts.total || 0)));

    document
      .getElementById("total-active")
      ?.replaceChildren(String(Number(counts.active || 0)));

    document
      .getElementById("total-inactive")
      ?.replaceChildren(String(Number(counts.inactive || 0)));
  } catch (error) {
    console.error("Error loading program counts:", error);

    document.getElementById("total-programs")?.replaceChildren("—");

    document.getElementById("total-active")?.replaceChildren("—");

    document.getElementById("total-inactive")?.replaceChildren("—");
  } finally {
    hideCardLoader(["total-programs", "total-active", "total-inactive"]);
  }
}
