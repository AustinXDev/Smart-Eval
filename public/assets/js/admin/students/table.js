import { createDataTable } from "../shared/datatable_config.js";
import { fetchAllPrograms } from "../shared/program_api.js";
import { getStudentsByDepartment } from "./api/api.js";

let department;
let table;

$(document).ready(async function () {
  const wrapper = document.getElementById("tableWrapper");

  department = wrapper ? wrapper.dataset.department : "";

  // ============================================================
  // DATATABLE
  // ============================================================

  table = createDataTable(
    "#studentsTable",
    {
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
      const option = document.createElement("option");

      option.value = program.program_name;
      option.textContent = program.program_name;

      select.appendChild(option);
    });
  }

  // ============================================================
  // STATUS FILTER
  // ============================================================

  $("#statusFilter").on("change", function () {
    const status = $(this).val();

    if (status === "All") {
      table.column(4).search("").draw();
    } else {
      table
        .column(4)
        .search("^" + status + "$", true, false)
        .draw();
    }
  });

  // ============================================================
  // COURSE / PROGRAM FILTER
  // ============================================================

  $("#courseFilter").on("change", function () {
    const program = $(this).val();

    if (program === "All") {
      table.column(3).search("").draw();
    } else {
      table
        .column(3)
        .search("^" + program + "$", true, false)
        .draw();
    }
  });

  // ============================================================
  // SEARCH
  // ============================================================

  $("#searchBox").on("keyup", function () {
    table.search(this.value).draw();
  });

  // ============================================================
  // INITIAL LOAD
  // ============================================================

  loadStudents();
  loadStudentCard();
});

// ================================================================
// LOAD STUDENTS
// ================================================================

export async function loadStudents() {
  try {
    const data = await getStudentsByDepartment(department);

    if (data.status !== "success") {
      throw new Error(data.message || "Unable to load students.");
    }

    table.clear();

    const students = data.students ?? [];

    students.forEach((student) => {
      // --------------------------------------------------------
      // STATUS
      // --------------------------------------------------------

      const statusBadge = student.is_active
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
        <div class="flex items-center justify-end gap-1.5">

          <button
            type="button"
            class="
              viewBtn inline-flex h-8 w-8
              items-center justify-center
              rounded-lg border border-slate-200
              bg-white text-slate-500
              transition-all duration-150
              hover:border-violet-200
              hover:bg-violet-50
              hover:text-violet-600
              focus:outline-none
              focus:ring-2 focus:ring-violet-500/20
              cursor-pointer
            "
            data-student-id="${student.student_id}"
            title="View student"
          >
            <i class="fas fa-eye text-xs"></i>
          </button>

          <button
            type="button"
            class="
              editBtn inline-flex h-8 w-8
              items-center justify-center
              rounded-lg border border-slate-200
              bg-white text-slate-500
              transition-all duration-150
              hover:border-blue-200
              hover:bg-blue-50
              hover:text-blue-600
              focus:outline-none
              focus:ring-2 focus:ring-blue-500/20
              cursor-pointer
            "
            data-student-id="${student.student_id}"
            title="Edit student"
          >
            <i class="fas fa-pen text-xs"></i>
          </button>

          <button
            type="button"
            class="
              deleteBtn inline-flex h-8 w-8
              items-center justify-center
              rounded-lg border border-slate-200
              bg-white text-slate-500
              transition-all duration-150
              hover:border-red-200
              hover:bg-red-50
              hover:text-red-600
              focus:outline-none
              focus:ring-2 focus:ring-red-500/20
              cursor-pointer
            "
            data-student-id="${student.student_id}"
            title="Delete student"
          >
            <i class="fas fa-trash-alt text-xs"></i>
          </button>

        </div>
      `;

      // --------------------------------------------------------
      // TABLE ROW
      // --------------------------------------------------------

      const rowNode = table.row
        .add([
          `
            <span class="font-medium text-slate-700">
              ${student.student_id}
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
                ${getInitials(student.full_name)}
              </div>

              <div class="min-w-0">
                <p class="
                  truncate font-medium text-slate-800
                ">
                  ${student.full_name}
                </p>
              </div>

            </div>
          `,

          `
            <span class="text-slate-600">
              ${student.department.toUpperCase()}
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
              title="${student.program_name}"
            >
              ${student.program_name.toUpperCase()}
            </span>
          `,

          statusBadge,

          actionButtons,
        ])
        .draw(false)
        .node();

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
    });
  } catch (error) {
    console.error("Error loading students:", error);
  }
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
  try {
    const data = await getStudentsByDepartment(department);

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
  }
}
