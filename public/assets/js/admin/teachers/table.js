import { escapeHTML } from "../../services/escape.js";
import { createDataTable } from "../shared/datatable_config.js";
import { getTeachersByDepartment, getTeacherHandles } from "./api/api.js";
import { showCardLoader, hideCardLoader } from "../shared/Loader.js";

let department;
let table;
let latestDraw = 0;
let listError = "";

$(document).ready(function () {
  const wrapper = document.getElementById("tableWrapper");
  department = wrapper ? wrapper.dataset.department : "";

  table = createDataTable(
    "#teachersTable",
    {
      serverSide: true,
      processing: true,
      pageLength: 5,
      createdRow: styleTeacherRow,
      language: {
        emptyTable: renderTeacherState("No teacher records found"),
        zeroRecords: renderTeacherState("No matching teachers"),
        processing: `<span class="teacher-loading" role="status">
          <span class="teacher-loading__icon"><span class="teacher-loading__spinner" aria-hidden="true"></span></span>
          <span class="teacher-loading__title">Loading teacher records...</span>
          <span class="teacher-loading__subtitle">Please wait a moment...</span>
        </span>`,
        info: "Showing _START_ to _END_ of _TOTAL_ records",
        infoEmpty: "Showing 0 to 0 of 0 records",
        paginate: { previous: "Previous", next: "Next" },
      },
      ajax: async function (request, callback) {
        const draw = Number(request.draw);
        latestDraw = draw;
        const loader = document.getElementById("teachersTable_processing");
        if (loader) loader.style.display = "block";
        try {
          let result;
          try {
            const response = await getTeachersByDepartment(department, {
              draw,
              start: request.start,
              length: request.length,
              search: request.search.value,
              status: $("#statusFilter").val() || "All",
            });
            if (response.status !== "success")
              throw new Error(response.message || "Unable to load teachers.");
            if (
              !Array.isArray(response.data) ||
              Number(response.draw) !== draw ||
              !Number.isInteger(Number(response.recordsTotal)) ||
              response.recordsTotal == null ||
              !Number.isInteger(Number(response.recordsFiltered)) ||
              response.recordsFiltered == null ||
              Number(response.recordsTotal) < 0 ||
              Number(response.recordsFiltered) < 0
            ) {
              throw new Error("Invalid teacher-list response.");
            }
            result = { ...response, data: response.data.map(renderTeacherRow) };
            if (draw === latestDraw) listError = "";
          } catch (error) {
            console.error(error);
            if (draw === latestDraw)
              listError = error.message || "Unable to load teachers.";
            result = { draw, recordsTotal: 0, recordsFiltered: 0, data: [] };
          }
          callback(result);
        } finally {
          if (draw === latestDraw && loader) loader.style.display = "none";
        }
      },
      drawCallback: function () {
        if (!listError) return;
        const cell = this.api()
          .table()
          .container()
          .querySelector("td.dataTables_empty, td.dt-empty");
        if (cell) cell.innerHTML = renderTeacherState(listError, true);
      },
      columnDefs: [{ orderable: false, targets: 5 }],
    },
    "Search teachers",
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
  $("#teachersTable").on("click", ".retryTeachersBtn", () => loadTeachers());
});

export function loadTeachers() {
  if (!table) return Promise.resolve();
  return new Promise((resolve) => table.ajax.reload(resolve, true));
}

function renderTeacherRow(teacher) {
  // Status Badge
  const statusBadge =
    Number(teacher.is_active) === 1
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
  return [
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
            src="${window.BASE_URL}uploads/teachers/${encodeURIComponent(teacher.image_path)}"
            onerror="
              this.src='${window.BASE_URL}uploads/teachers/default_teacher.png';
              this.onerror=null;
            "
            alt="${escapeHTML(teacher.full_name)}"
          >

        </div>
      </div>
      `,

    // Employee ID
    `
      <div class="flex justify-center items-center">
        <span class="text-center font-medium text-slate-700">
          ${escapeHTML(teacher.employee_id)}
        </span>
      </div>
      `,

    // Teacher Name
    `
      <div class="flex flex-col">
        <span class="font-semibold text-slate-800">
          ${escapeHTML(teacher.full_name)}
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
          ${escapeHTML(teacher.department)}
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
          data-teacher-id="${escapeHTML(String(teacher.teacher_id))}"
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
          data-teacher-id="${escapeHTML(String(teacher.teacher_id))}"
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
          data-teacher-id="${escapeHTML(String(teacher.teacher_id))}"
          title="Delete Teacher">

          <i class="fas fa-trash-alt text-xs"></i>

        </button>

      </div>
      `,
  ];
}

function styleTeacherRow(rowNode) {
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
}

function renderTeacherState(message, error = false) {
  return `<div class="py-12 text-center">
    <p class="text-sm font-semibold text-slate-700">${escapeHTML(message)}</p>
    ${error ? '<button type="button" class="retryTeachersBtn mt-4 rounded-lg bg-violet-600 px-4 py-2 text-white">Try Again</button>' : '<p class="mt-1 text-xs text-slate-400">Try changing your search or filters.</p>'}
  </div>`;
}

//load teacher handles
export async function loadTeacherHandles(teacherId) {
  const tbody = document.querySelector("#handleTable tbody");
  tbody.innerHTML = ""; // clear previous rows

  try {
    const handles = await getTeacherHandles(teacherId);

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
                ${escapeHTML(h.year_level)} ${h.year_level <= 4 ? "Year" : "Grade"}
              </span>
            </td>

            <!-- PROGRAM -->
            <td>
              <span class="bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-medium">
                ${escapeHTML(h.program_name)}
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
    const response = await getTeachersByDepartment(department, {
      counts_only: 1,
    });

    const counts = response?.counts;

    if (!counts) throw new Error("Unable to load teacher counts.");

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
