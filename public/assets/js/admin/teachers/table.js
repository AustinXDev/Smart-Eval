import { createDataTable } from "../shared/datatable_config.js";

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
      table
        .column(4)
        .search("^" + status + "$", true, false)
        .draw();
    }
  });

  // Search box
  $("#searchBox").on("keyup", function () {
    table.search(this.value).draw();
  });

  loadTeachers();
});

export function loadTeachers() {
  const url =
    "/Smart-Eval/app/Controllers/teachers/get_teachers.php" +
    (department ? `?department=${department}` : "");

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      table.clear();

      data.teachers.forEach((teacher) => {
        let statusBadge = teacher.is_active
          ? `<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Active</span>`
          : `<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Inactive</span>`;

        let rowNode = table.row
          .add([
            `<div class="flex-shrink-0 w-9 h-9">
                  <img 
                      class="w-9 h-9 rounded-full object-cover object-center"
                      src="/Smart-Eval/public/uploads/teachers/${teacher.image_path}"
                      onerror="this.src='/Smart-Eval/public/uploads/teachers/default_teacher.png'; this.onerror=null;"
                      alt="${teacher.full_name}"
                  >
              </div>`,
            teacher.employee_id,
            teacher.full_name,
            teacher.department.toUpperCase(),
            statusBadge,
            `<div class="flex gap-2">
                  <button class="viewBtn bg-blue-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-eye"></i></button>
                  <button class="editBtn bg-green-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-edit"></i></button>
                  <button class="deleteBtn bg-red-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-trash"></i></button>
              </div>`,
          ])
          .draw(false)
          .node();

        rowNode.classList.add("hover:bg-gray-100", "p-6", "cursor-pointer");
        rowNode
          .querySelectorAll("td")
          .forEach((td) =>
            td.classList.add(
              "px-6",
              "py-2",
              "md:text-sm",
              "text-xs",
              "whitespace-nowrap",
            ),
          );
        rowNode
          .querySelectorAll("button")
          .forEach((btn) => btn.classList.add("cursor-pointer"));
      });

      table.draw(false);
    })
    .catch((err) => console.error("Error loading teachers:", err));
}

//load teacher handles
export function loadTeacherHandles(teacherId) {
  const tbody = document.querySelector("#handleTable tbody");
  tbody.innerHTML = ""; // clear previous rows

  fetch(`/Smart-Eval/app/Controllers/teachers/get_teachers.php?id=${teacherId}`)
    .then((res) => res.json())
    .then((data) => {
      const handles = data.handles || [];

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
                  data-year="${h.year_level}" 
                  data-program="${h.program_name}" 
                  data-department="${department}"
                >
                  <i class="fas fa-trash text-sm transform transition-transform duration-200 group-hover:scale-110"></i>
                  <span class="text-xs font-medium hidden sm:inline">Remove</span>
                </button>

              </div>
            </td>
          `;
        tbody.appendChild(tr);
      });
    })
    .catch((err) => console.error(err));
}

export function loadCard() {
  const container = document.getElementById("card-container");
  const card_department = container.dataset.department ?? "";

  fetch(
    `/Smart-Eval/app/Controllers/teachers/get_teachers.php?department=${card_department}`,
  )
    .then((res) => res.json())
    .then((data) => {
      if (!data.counts) return;

      const counts = data.counts;

      document.getElementById("total-teachers").textContent =
        Number(counts.total) || 0;
      document.getElementById("total-active").textContent =
        Number(counts.active) || 0;
      document.getElementById("total-inactive").textContent =
        Number(counts.inactive) || 0;

      console.log("Teachers count:", counts); // debugging
    })
    .catch((err) => console.log(err));
}
