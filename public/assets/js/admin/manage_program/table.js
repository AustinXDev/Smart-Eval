import { createDataTable } from "../shared/datatable_config.js";
import { fetchPrograms } from "./programs_api.js";

let table;

$(document).ready(async function () {
  table = createDataTable(
    "#programTable",
    {
      columnDefs: [{ orderable: false, targets: 4 }],
    },
    "Search programs...",
  );

  // Status filter
  $("#departmentFilter").on("change", function () {
    const status = $(this).val();
    if (status === "All") {
      table.column(2).search("").draw();
    } else {
      table
        .column(2)
        .search("^" + status + "$", true, false)
        .draw();
    }
  });

  // Course Filter
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

  // Search box
  $("#searchBox").on("keyup", function () {
    table.search(this.value).draw();
  });

  await loadPrograms();
});

export async function loadPrograms() {
  const programs = await fetchPrograms();

  if (!programs.length) {
    console.log("No programs found.");
    return;
  }

  table.clear();

  programs.forEach((p) => {
    let rowNode = table.row
      .add([
        p.program_code,
        p.program_name,
        p.department.toUpperCase(),
        `<span class="flex items-center gap-1 px-3 py-1 text-xs font-medium 
                    bg-green-100 text-green-700 rounded-full w-max">
                    <i class="fa-solid fa-circle text-[8px]"></i> 
                    ${p.is_active === 1 ? "Active" : "Inactive"}
        </span>`,
        `<div class="flex justify-center gap-2"> 
          <button 
            data-program-id="${p.program_id}" 
            data-program-code="${p.program_code}" 
            data-program-name="${p.program_name}" 
            data-program-department="${p.department}"
            class="editProgram flex items-center gap-1 px-3 py-1.5 text-xs bg-gray-100 
                  rounded-md hover:bg-gray-200 transition">
              <i class="fa-solid fa-pen"></i>
              Edit
          </button>

          <button data-program-id="${p.program_id}"
            class="deleteProgram flex items-center gap-1 px-3 py-1.5 text-xs 
                  bg-red-100 text-red-600 rounded-md 
                  hover:bg-red-200 transition">
              <i class="fa-solid fa-trash"></i>
              Delete
          </button>
        </div>`,
      ])
      .draw(false)
      .node();

    rowNode.classList.add(
      "hover:bg-gray-50",
      "p-4",
      "cursor-pointer",
      "transition",
    );
    rowNode
      .querySelectorAll("td")
      .forEach((td) =>
        td.classList.add(
          "px-6",
          "py-4",
          "md:text-sm",
          "text-xs",
          "whitespace-nowrap",
        ),
      );
    rowNode
      .querySelectorAll("button")
      .forEach((btn) => btn.classList.add("cursor-pointer"));
  });
}
