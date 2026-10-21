import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { fetchAllPrograms } from "../shared/program_api.js";
import { getStudentsByDepartment } from "./api/api.js";
import { showCardLoader, hideCardLoader } from "../shared/Loader.js";

let department;
let table;
let latestDraw = 0;

$(document).ready(async function () {
  const wrapper = document.getElementById("tableWrapper");

  department = wrapper ? wrapper.dataset.department : "";

  // ============================================================
  // DATATABLE
  // ============================================================

  table = createDataTable(
    "#studentsTable",
    {
      serverSide: true,
      processing: true,
      createdRow: styleStudentRow,
      ajax: async function (request, callback) {
        const draw = Number(request.draw);
        latestDraw = draw;

        const tableElement = document.getElementById("studentsTable");
        tableElement?.classList.add("is-loading");
        tableElement?.setAttribute("aria-busy", "true");

        const loader = document.getElementById("studentsTable_processing");
        if (loader) loader.style.display = "block";

        try {
          const response = await getStudentsByDepartment(department, {
            draw: request.draw,
            start: request.start,
            length: request.length,
            search: request.search.value,
            status: $("#statusFilter").val() || "All",
            program: $("#courseFilter").val() || "All",
          });

          if (response.status !== "success") {
            throw new Error(response.message || "Unable to load students.");
          }

          callback({ ...response, data: response.data.map(renderStudentRow) });
        } catch (error) {
          callback({
            draw: request.draw,
            recordsTotal: 0,
            recordsFiltered: 0,
            data: [],
          });
          StatusModal.show(
            "Error",
            error.message || "Unable to load students.",
            "error",
          );
        } finally {
          if (draw === latestDraw) {
            if (loader) loader.style.display = "none";

            tableElement?.classList.remove("is-loading");
            tableElement?.setAttribute("aria-busy", "false");
          }
        }
      },
      columnDefs: [
        {
          orderable: false,
          targets: 5,
        },
      ],
    },
    "Search students",
  );

  // ============================================================
  // LOAD PROGRAMS
  // ============================================================

  const select = document.getElementById("courseFilter");

  if (select) {
    const programs = await fetchAllPrograms(department);

    programs.forEach((program) => {
      const programName = program.program_name.trim();
      const option = document.createElement("option");

      option.value = program.program_name;
      option.textContent = programName.toUpperCase();

      select.appendChild(option);
    });
  }

  // ============================================================
  // STATUS FILTER
  // ============================================================

  $("#statusFilter, #courseFilter").on("change", function () {
    table.ajax.reload();
  });

  let searchTimer;

  $("#searchBox").on("input", function () {
    const value = this.value;
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => table.search(value).draw(), 350);
  });

  // ============================================================
  // INITIAL LOAD
  // ============================================================

  await loadStudentCard();
});

// ================================================================
// LOAD STUDENTS
// ================================================================

export function loadStudents() {
  return new Promise((resolve) => table.ajax.reload(resolve, true));
}

function renderStudentRow(student) {
  // --------------------------------------------------------
  // STATUS
  // --------------------------------------------------------

  const statusBadge =
    Number(student.is_active) === 1
      ? `
          <span class="
            inline-flex items-center gap-1.5
            rounded-full bg-emerald-50
            px-2.5 py-1
            text-xs font-semibold text-emerald-700
          ">
            <span class="
              h-1.5 w-1.5 rounded-full bg-emerald-500
            "></span>
            Active
          </span>
        `
      : `
          <span class="
            inline-flex items-center gap-1.5
            rounded-full bg-red-50
            px-2.5 py-1
            text-xs font-semibold text-red-700
          ">
            <span class="
              h-1.5 w-1.5 rounded-full bg-red-500
            "></span>
            Inactive
          </span>
        `;

  // --------------------------------------------------------
  // ACTION BUTTONS
  // --------------------------------------------------------

  const actionButtons = `
        <div class="flex items-center justify-center gap-1.5">

          <!-- View -->
          <button
            type="button"
            class="viewBtn flex h-8 w-8 items-center justify-center
                  rounded-lg border border-slate-200
                  bg-white text-slate-500
                  shadow-sm
                  transition-all duration-200
                  hover:-translate-y-0.5
                  hover:border-blue-200
                  hover:bg-blue-50
                  hover:text-blue-600
                  hover:shadow-sm
                  focus:outline-none
                  focus:ring-2 focus:ring-blue-500/20
                  cursor-pointer"
            data-student-id="${escapeHTML(student.student_id)}"
            title="View Student">

            <i class="fas fa-eye text-xs"></i>

          </button>


          <!-- Edit -->
          <button
            type="button"
            class="editBtn flex h-8 w-8 items-center justify-center
                  rounded-lg border border-slate-200
                  bg-white text-slate-500
                  shadow-sm
                  transition-all duration-200
                  hover:-translate-y-0.5
                  hover:border-violet-200
                  hover:bg-violet-50
                  hover:text-violet-600
                  hover:shadow-sm
                  focus:outline-none
                  focus:ring-2 focus:ring-violet-500/20
                  cursor-pointer"
            data-student-id="${escapeHTML(student.student_id)}"
            title="Edit Student">

            <i class="fas fa-pen text-xs"></i>

          </button>


          <!-- Delete -->
          <button
            type="button"
            class="deleteBtn flex h-8 w-8 items-center justify-center
                  rounded-lg border border-slate-200
                  bg-white text-slate-500
                  shadow-sm
                  transition-all duration-200
                  hover:-translate-y-0.5
                  hover:border-red-200
                  hover:bg-red-50
                  hover:text-red-600
                  hover:shadow-sm
                  focus:outline-none
                  focus:ring-2 focus:ring-red-500/20
                  cursor-pointer"
            data-student-id="${escapeHTML(student.student_id)}"
            title="Delete Student">

            <i class="fas fa-trash-alt text-xs"></i>

          </button>

        </div>
        `;

  // --------------------------------------------------------
  // TABLE ROW
  // --------------------------------------------------------

  return [
    `
            <span class="font-medium text-slate-700">
              ${escapeHTML(student.student_id)}
            </span>
          `,

    `
            <div class="flex items-center gap-3">

              <div class="
                flex h-8 w-8 shrink-0
                items-center justify-center
                rounded-full bg-violet-50
                text-xs font-semibold
                text-violet-600
              ">
                ${escapeHTML(getInitials(student.full_name))}
              </div>

              <div class="min-w-0">
                <p class="
                  truncate font-medium text-slate-800
                ">
                  ${escapeHTML(student.full_name)}
                </p>
              </div>

            </div>
          `,

    `
            <span class="text-slate-600">
              ${escapeHTML(student.department.toUpperCase())}
            </span>
          `,

    `
            <span
              class="
                inline-flex max-w-[180px]
                truncate rounded-md
                bg-slate-50 px-2.5 py-1
                text-xs font-medium text-slate-600
              "
              title="${escapeHTML(student.program_name)}"
            >
              ${escapeHTML(student.program_name.toUpperCase())}
            </span>
          `,

    `
          <div class="flex justify-center items-center">
            ${statusBadge}
          </div>

          `,

    actionButtons,
  ];
}

function styleStudentRow(rowNode) {
  // --------------------------------------------------------
  // ROW STYLING
  // --------------------------------------------------------

  rowNode.classList.add(
    "group",
    "border-b",
    "border-slate-100",
    "transition-colors",
    "duration-150",
    "hover:bg-slate-50/70",
  );

  // --------------------------------------------------------
  // CELL STYLING
  // --------------------------------------------------------

  rowNode.querySelectorAll("td").forEach((td, index) => {
    td.classList.add("px-5", "py-3.5", "text-sm", "whitespace-nowrap");

    if (index === 5) {
      td.classList.add("text-right");
    }
  });
}

// ================================================================
// GET INITIALS
// ================================================================

function getInitials(name) {
  if (!name) return "?";

  const words = name.trim().split(/\s+/);

  if (words.length === 1) {
    return words[0].substring(0, 2).toUpperCase();
  }

  return (words[0][0] + words[words.length - 1][0]).toUpperCase();
}

// ================================================================
// LOAD STUDENT COUNTS
// ================================================================

export async function loadStudentCard() {
  showCardLoader(["total-students", "total-active", "total-inactive"]);

  try {
    const data = await getStudentsByDepartment(department, { counts_only: 1 });

    if (data.status !== "success") {
      throw new Error(data.message || "Unable to load student counts.");
    }

    const counts = data.counts ?? {
      total: 0,
      active: 0,
      inactive: 0,
    };

    document
      .getElementById("total-students")
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
    hideCardLoader(["total-students", "total-active", "total-inactive"]);
  }
}
