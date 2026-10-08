import { createDataTable } from "../shared/datatable_config.js";
import { get } from "../../services/http.js";
import {
  showTableLoader,
  hideTableLoader,
  showCardLoader,
  hideCardLoader,
} from "../shared/Loader.js";

let department;
let table;

$(document).ready(function () {
  const wrapper = document.getElementById("tableWrapper");
  department = wrapper ? wrapper.dataset.department : "";

  table = createDataTable(
    "#teachersTable",
    {
      columnDefs: [{ orderable: false, targets: 5 }],
    },
    "Search teachers",
  );

  // Status filter
  $("#statusFilter").on("change", function () {
    const status = $(this).val();
    if (status === "All") {
      table.column(4).search("").draw();
    } else {
      table.column(4).search(status, false, false).draw();
    }
  });

  // Search box
  $("#searchBox").on("keyup", function () {
    table.search(this.value).draw();
  });

  loadTeachers();
});

export async function loadTeachers() {
  showTableLoader("#teachersTableBody", "Loading teacher records...");

  try {
    const response = await get(
      `teacher/get_teachers.php?department=${department}`,
    );

    const teachers = response?.data?.teachers ?? [];

    table.clear();

    teachers.forEach((teacher) => {
      // Status Badge
      const statusBadge = teacher.is_active
        ? `
      <span class="inline-flex items-center gap-1.5
                   rounded-full bg-emerald-50
                   px-2.5 py-1
                   text-xs font-semibold text-emerald-700">
        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
        Active
      </span>
    `
        : `
      <span class="inline-flex items-center gap-1.5
                   rounded-full bg-red-50
                   px-2.5 py-1
                   text-xs font-semibold text-red-700">
        <span class="h-1.5 w-1.5 rounded-full bg-red-500"></span>
        Inactive
      </span>
    `;

      // Table Row
      const rowNode = table.row
        .add([
          // Photo
          `
      <div class="flex items-center justify-center">
        <div class="relative h-10 w-10">

          <div class="absolute inset-0 rounded-full
                      bg-violet-100 opacity-60">
          </div>

          <img
            class="relative h-10 w-10 rounded-full
                   border-2 border-white
                   object-cover object-center
                   shadow-sm"
            src="${window.BASE_URL}uploads/teachers/${teacher.image_path}"
            onerror="
              this.src='${window.BASE_URL}uploads/teachers/default_teacher.png';
              this.onerror=null;
            "
            alt="${teacher.full_name}"
          >

        </div>
      </div>
      `,

          // Employee ID
          `
      <div class="flex justify-center items-center">
        <span class="text-center font-medium text-slate-700">
          ${teacher.employee_id}
        </span>
      </div>
      `,

          // Teacher Name
          `
      <div class="flex flex-col">
        <span class="font-semibold text-slate-800">
          ${teacher.full_name}
        </span>

        <span class="mt-0.5 text-xs text-slate-400">
          Faculty Member
        </span>
      </div>
      `,

          // Department
          `
      <div class="flex justify-center items-center">
        <span class="inline-flex items-center rounded-md
                    bg-violet-50 px-2.5 py-1
                    text-xs font-semibold uppercase
                    tracking-wide text-violet-700">
          ${teacher.department}
        </span>
      </div>
      `,

          // Status
          statusBadge,

          // Actions
          `
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
          data-teacher-id="${teacher.teacher_id}"
          title="View Teacher">

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
          data-teacher-id="${teacher.teacher_id}"
          title="Edit Teacher">

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
          data-teacher-id="${teacher.teacher_id}"
          title="Delete Teacher">

          <i class="fas fa-trash-alt text-xs"></i>

        </button>

      </div>
      `,
        ])
        .draw(false)
        .node();

      // Row styling
      rowNode.classList.add(
        "group",
        "transition-colors",
        "duration-150",
        "hover:bg-slate-50/80",
      );

      // Cell styling
      rowNode.querySelectorAll("td").forEach((td, index) => {
        td.classList.add(
          "px-5",
          "py-3.5",
          "text-xs",
          "sm:text-sm",
          "whitespace-nowrap",
          "align-middle",
        );

        // Center specific columns
        if (index === 0 || index === 4 || index === 5) {
          td.classList.add("text-center");
        }
      });
    });

    table.draw(false);
  } catch (error) {
    console.error(error);

    const tbody = document.querySelector("$teachersTable tbody");

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
    hideTableLoader("#teachersTableBody");
  }
}

//load teacher handles
export async function loadTeacherHandles(teacherId) {
  const tbody = document.querySelector("#handleTable tbody");
  tbody.innerHTML = ""; // clear previous rows

  try {
    const response = await get(`teacher/get_teachers.php?id=${teacherId}`);

    const handles =
      response?.data?.data?.handles ?? response?.data?.handles ?? [];

    if (handles.length === 0) {
      tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">No handles assigned</td></tr>`;
      return;
    }

    handles.forEach((h) => {
      const tr = document.createElement("tr");
      tr.classList.add(
        "border-b",
        "border-gray-200",
        "hover:bg-purple-50",
        "transition",
        "duration-200",
      );

      tr.innerHTML = `
            <!-- LEVEL -->
            <td class="py-3 px-5">
              <span class="bg-gradient-to-r from-purple-600 to-indigo-600 text-white px-3 py-1 rounded-full text-xs font-medium shadow-sm">
                ${h.year_level} ${h.year_level <= 4 ? "Year" : "Grade"}
              </span>
            </td>

            <!-- PROGRAM -->
            <td>
              <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">
                ${h.program_name}
              </span>
            </td>

            <!-- ACTION -->
            <td class="pr-5">
              <div class="flex justify-end items-center">

                <button 
                  class="deleteHandleBtn group flex items-center gap-2 px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 rounded-md transition duration-200"
                  data-loadId="${h.load_id}"
                >
                  <i class="fas fa-trash text-sm transform transition-transform duration-200 group-hover:scale-110"></i>
                  <span class="text-xs font-medium hidden sm:inline">Remove</span>
                </button>

              </div>
            </td>
          `;
      tbody.appendChild(tr);
    });
  } catch (error) {
    const errorMessage =
      error.response?.data?.message ||
      error.message ||
      "Failed to fetch teacher details.";
    StatusModal.show("Error", errorMessage, "error");
  }
}

export async function loadCard() {
  const container = document.getElementById("card-container");
  const department = container.dataset.department ?? "";

  showCardLoader(["total-teachers", "total-active", "total-inactive"]);

  try {
    const response = await get(
      `teacher/get_teachers.php?department=${department}`,
    );

    const counts = response?.data?.counts ?? [];

    if (!counts) return;

    document.getElementById("total-teachers").textContent =
      Number(counts.total) || 0;
    document.getElementById("total-active").textContent =
      Number(counts.active) || 0;
    document.getElementById("total-inactive").textContent =
      Number(counts.inactive) || 0;
  } catch (error) {
    alert(error.message);
    console.error(error);
  } finally {
    hideCardLoader(["total-teachers", "total-active", "total-inactive"]);
  }
}
