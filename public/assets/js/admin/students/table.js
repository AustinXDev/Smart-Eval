import { createDataTable } from "../shared/datatable_config.js";
import { fetchAllPrograms } from "../shared/program_api.js";
import { getStudentsByDepartment } from "./api/api.js";
import {
  showTableLoader,
  hideTableLoader,
  showCardLoader,
  hideCardLoader,
} from "../shared/Loader.js";

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
      table.column(3).search(program, false, true).draw();
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

  await Promise.all([loadStudents(), loadStudentCard()]);
});

// ================================================================
// LOAD STUDENTS
// ================================================================

export async function loadStudents() {
  showTableLoader("#studentsTable", "Loading student records...");

  try {
    const data = await getStudentsByDepartment(department);

    if (data.status !== "success") {
      throw new Error(data.message || "Unable to load students.");
    }

    table.clear();

    const students = data.students ?? [];

    if (students.length === 0) {
      table.draw();

      const tbody = document.querySelector("#studentsTable tbody");

      if (tbody) {
        tbody.innerHTML = `
          <tr>
            <td colspan="6" class="px-5 py-16">

              <div class="
                flex flex-col
                items-center
                justify-center
                text-center
              ">

                <!-- Icon -->
                <div class="
                  mb-5
                  flex h-16 w-16
                  items-center justify-center
                  rounded-2xl
                  border border-violet-100
                  bg-violet-50
                  text-violet-500
                ">
                  <i class="
                    fa-solid
                    fa-user-group
                    text-xl
                  "></i>
                </div>


                <!-- Title -->
                <h3 class="
                  text-sm
                  font-semibold
                  text-slate-800
                ">
                  No student records found
                </h3>


                <!-- Description -->
                <p class="
                  mt-1.5
                  max-w-sm
                  text-xs
                  leading-relaxed
                  text-slate-400
                ">
                  There are currently no registered students
                  available for this department.
                </p>


                <!-- Department Badge -->
                ${
                  department
                    ? `
                      <span class="
                        mt-4
                        inline-flex
                        items-center
                        gap-1.5
                        rounded-full
                        bg-slate-50
                        px-3 py-1.5
                        text-[10px]
                        font-semibold
                        uppercase
                        tracking-wide
                        text-slate-500
                      ">
                        <span class="
                          h-1.5 w-1.5
                          rounded-full
                          bg-violet-500
                        "></span>

                        ${department}
                      </span>
                    `
                    : ""
                }


                <!-- Optional Action -->
                <button
                  type="button"
                  class="
                    mt-5
                    inline-flex
                    items-center
                    gap-2
                    rounded-xl
                    border border-violet-200
                    bg-white
                    px-4 py-2
                    text-xs
                    font-semibold
                    text-violet-600
                    shadow-sm
                    transition-all
                    duration-200

                    hover:-translate-y-0.5
                    hover:border-violet-300
                    hover:bg-violet-50
                    hover:shadow-md

                    focus:outline-none
                    focus:ring-4
                    focus:ring-violet-500/10

                    active:translate-y-0
                    active:scale-[0.98]
                  "
                  onclick="document.querySelector('.add-btn')?.click()"
                >
                  <i class="fa-solid fa-plus text-[10px]"></i>
                  Add Student
                </button>

              </div>

            </td>
          </tr>
        `;
      }

      return;
    }

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
            data-student-id="${student.student_id}"
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
            data-student-id="${student.student_id}"
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
            data-student-id="${student.student_id}"
            title="Delete Student">

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

          `
          <div class="flex justify-center items-center">
            ${statusBadge}
          </div>

          `,

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

    const tbody = document.querySelector("#studentsTable tbody");

    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="6">

            <div class="
              flex flex-col
              items-center
              justify-center
              py-14
              text-center
            ">

              <div class="
                mb-3 flex h-12 w-12
                items-center justify-center
                rounded-xl bg-red-50
                text-red-500
              ">
                <i class="fas fa-exclamation-triangle"></i>
              </div>

              <p class="text-sm font-semibold text-slate-700">
                Unable to load students
              </p>

              <p class="mt-1 text-xs text-slate-400">
                Please try again.
              </p>

              <button
                type="button"
                onclick="location.reload()"
                class="
                  mt-4 rounded-lg
                  bg-violet-600
                  px-4 py-2
                  text-xs font-semibold
                  text-white
                  transition
                  hover:bg-violet-700
                "
              >
                Try Again
              </button>

            </div>

          </td>
        </tr>
      `;
    }
  } finally {
    hideTableLoader("#studentsTableBody");
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
  showCardLoader(["total-students", "total-active", "total-inactive"]);

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

    document.getElementById("total-students")?.replaceChildren("—");

    document.getElementById("total-active")?.replaceChildren("—");

    document.getElementById("total-inactive")?.replaceChildren("—");
  } finally {
    hideCardLoader(["total-students", "total-active", "total-inactive"]);
  }
}
