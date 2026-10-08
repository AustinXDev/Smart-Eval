import { createDataTable } from "../shared/datatable_config.js";
import { getPrograms } from "./api/api.js";
import {
  showTableLoader,
  hideTableLoader,
  showCardLoader,
  hideCardLoader,
} from "../shared/Loader.js";

let table;

$(document).ready(async function () {
  // ============================================================
  // DATATABLE
  // ============================================================

  table = createDataTable("#programTable", {
    columnDefs: [
      {
        orderable: false,
        targets: 4,
      },
    ],

    order: [[1, "asc"]],
  });

  // ============================================================
  // SEARCH
  // ============================================================

  $("#searchBox").on("input", function () {
    table.search(this.value).draw();
  });

  // ============================================================
  // DEPARTMENT FILTER
  // ============================================================

  $("#departmentFilter").on("change", function () {
    const department = $(this).val();

    if (!department || department === "All") {
      table.column(2).search("").draw();
      return;
    }

    table.column(2).search(department, false, false).draw();
  });

  // ============================================================
  // LOAD PROGRAMS
  // ============================================================

  await Promise.all([loadPrograms(), loadProgramCard()]);
});

// ============================================================
// LOAD PROGRAMS
// ============================================================

export async function loadPrograms() {
  showTableLoader("#programTable", "Loadin program records...");
  try {
    const response = await getPrograms();

    if (response.status !== "success") {
      console.error("Failed to load programs", response);
      return;
    }

    const programs = response.data.programs ?? [];

    if (!table) return;

    table.clear();

    if (!programs || programs.length === 0) {
      table.draw();
      return;
    }

    programs.forEach((p) => {
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
          ${p.program_code}
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
              ${p.program_name}
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

          ${String(p.department).toUpperCase()}
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

            data-program-id="${p.program_id}"
            data-program-code="${p.program_code}"
            data-program-name="${p.program_name}"
            data-program-department="${p.department}"

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

            data-program-id="${p.program_id}"

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

      const rowNode = table.row
        .add([programCode, programName, department, status, actions])
        .node();

      // ========================================================
      // ROW STYLE
      // ========================================================

      rowNode.classList.add(
        "group",
        "border-b",
        "border-slate-100",
        "transition-colors",
        "duration-150",
        "hover:bg-slate-50/70",
      );

      // ========================================================
      // CELL STYLE
      // ========================================================

      rowNode.querySelectorAll("td").forEach((td, index) => {
        td.classList.add("px-5", "py-4", "align-middle", "whitespace-nowrap");

        if (index === 4) {
          td.classList.add("text-right");
        }
      });
    });

    table.draw(false);
  } catch (error) {
    console.error("Error loading programs:", error);

    if (table) {
      table.clear().draw();
    }
  } finally {
    hideTableLoader("#programTable");
  }
}

// ================================================================
// LOAD PROGRAM COUNTS
// ================================================================

export async function loadProgramCard() {
  showCardLoader(["total-programs", "total-active", "total-inactive"]);

  try {
    const response = await getPrograms();

    if (response.status !== "success") {
      throw new Error(data.message || "Unable to load student counts.");
    }

    const counts = response.data.count ?? {
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
    console.error("Error loading student counts:", error);

    document.getElementById("total-students")?.replaceChildren("—");

    document.getElementById("total-active")?.replaceChildren("—");

    document.getElementById("total-inactive")?.replaceChildren("—");
  } finally {
    hideCardLoader(["total-programs", "total-active", "total-inactive"]);
  }
}
