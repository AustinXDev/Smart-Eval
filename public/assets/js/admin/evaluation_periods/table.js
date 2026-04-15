import { createDataTable } from "../shared/datatable_config.js";

let table;

$(document).ready(async function () {
  table = createDataTable(
    "#evaluationTable",
    {
      columnDefs: [{ orderable: false, targets: 4 }],
    },
    "Search evaluation periods",
  );

  // Status filter
  $("#statusFilter").on("change", function () {
    const status = $(this).val();
    if (status === "All") {
      table.column(2).search("").draw();
    } else {
      table
        .column(2)
        .search("^" + status + "$", true, false, true)
        .draw();
    }
  });

  // Search box
  $("#searchBox").on("keyup", function () {
    table.search(this.value).draw();
  });

  loadEvaluationPeriods();
  loadPeriodCard();
});

export function loadEvaluationPeriods() {
  fetch("/Smart-Eval/app/Controllers/periods/get_periods.php")
    .then((res) => res.json())
    .then((data) => {
      table.clear();

      data.periods.forEach((period) => {
        const today = new Date();
        const start = new Date(period.start_date);
        const end = new Date(period.end_date);

        let statusBadge, buttons;

        const baseBtn =
          "flex items-center gap-1 px-3 py-1.5 text-xs rounded-md transition-all duration-200 shadow-sm hover:scale-105 ";

        if (period.is_active) {
          // Active badge
          statusBadge = `
          <span class="px-3 py-1 text-xs font-medium rounded-full 
            bg-gradient-to-r from-green-100 to-emerald-200 text-green-800 
            ring-1 ring-green-300 shadow-sm animate-pulse">
            🟢 Active
          </span>
        `;
          buttons = `
          <div class="flex gap-2">
            <button class="closeBtn ${baseBtn} bg-purple-700 hover:bg-purple-800 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-stop"></i> Close Period
            </button>
          </div>
        `;
        } else if (period.is_closed == 1) {
          // 📁 ARCHIVED (FORCED CLOSE)
          statusBadge = `
          <span class="px-3 py-1 text-xs font-medium rounded-full 
            bg-gray-200 text-gray-700 ring-1 ring-gray-300 shadow-sm">
            📁 Archived
          </span>
        `;

          buttons = `
          <div class="flex flex-nowrap overflow-x-auto gap-2">
            <button class="viewBtn ${baseBtn} bg-purple-500 hover:bg-purple-600 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-eye"></i> View
            </button>

            <button class="downloadBtn ${baseBtn} bg-purple-700 hover:bg-purple-800 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-download"></i> Report
            </button>
          </div>
        `;
        } else if (today < start) {
          // Upcoming badge
          statusBadge = `
          <span class="px-3 py-1 text-xs font-medium rounded-full 
            bg-gradient-to-r from-purple-100 to-purple-200 text-purple-800 
            ring-1 ring-purple-300 shadow-sm">
            ⏳ Upcoming
          </span>
        `;
          buttons = `
          <div class="flex flex-nowrap overflow-hidden gap-2 ">
            <button class="editBtn ${baseBtn} bg-purple-500 hover:bg-purple-600 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-pen"></i> Edit
            </button>
            <button class="ActiveBtn ${baseBtn} bg-indigo-500 hover:bg-indigo-600 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-bolt"></i> Activate
            </button>
            <button class="deleteBtn ${baseBtn} bg-red-500 hover:bg-red-600 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-trash"></i> Delete
            </button>
          </div>
        `;
        } else {
          // Archived badge
          statusBadge = `
          <span class="px-3 py-1 text-xs font-medium rounded-full 
            bg-gray-200 text-gray-700 ring-1 ring-gray-300 shadow-sm">
            📁 Archived
          </span>
        `;
          buttons = `
          <div class="flex flex-nowrap overflow-x-auto gap-2">
            <button class="viewBtn ${baseBtn} bg-purple-500 hover:bg-purple-600 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-eye"></i> View
            </button>
            <button class="downloadBtn ${baseBtn} bg-purple-700 hover:bg-purple-800 text-white"
              data-id="${period.period_id}">
              <i class="fa-solid fa-download"></i> Report
            </button>
          </div>
        `;
        }

        let rowNode = table.row
          .add([
            period.academic_year,
            period.semester,
            statusBadge.trim(),
            period.target_dept,
            buttons,
          ])
          .draw(false)
          .node();

        rowNode.classList.add("hover:bg-gray-100", "p-6", "cursor-pointer");
        rowNode
          .querySelectorAll("td")
          .forEach((td) =>
            td.classList.add(
              "px-4",
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
    })
    .catch((err) => console.error("Error loading teachers:", err));
}

export function loadPeriodCard() {
  fetch("/Smart-Eval/app/Controllers/periods/get_active_periods.php")
    .then((res) => res.json())
    .then((data) => {
      // Default values
      let college = {
        year: "--",
        sem: "--",
        total: 0,
        total_finished: "--", // added this
        status: "No Active",
        participation: "--",
      };

      let shs = {
        year: "--",
        sem: "--",
        total: 0,
        total_finished: "--", // added this
        status: "No Active",
        participation: "--",
      };

      if (data.length > 0) {
        data.forEach((p) => {
          const totalStudents = Number(p.total_students) || 0;
          const totalFinished =
            p.total_finished !== undefined && p.total_finished !== null
              ? Number(p.total_finished)
              : null;

          const participation =
            totalFinished !== null && totalStudents > 0
              ? Math.round((totalFinished / totalStudents) * 100)
              : "--";

          const status = p.is_active == 1 ? "Active" : "Inactive";

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
    })
    .catch((err) => console.log(err));
}
