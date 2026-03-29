import { createDataTable } from "../shared/datatable_config.js";

let table;

$(document).ready(async function() {
  table = createDataTable('#evaluationTable', {
        columnDefs: [{ orderable: false, targets: 4 }]
      }, 'Search evaluation periods');
  
      // Status filter
      $('#statusFilter').on('change', function() {
        const status = $(this).val();
        if(status === 'All'){
          table.column(2).search('').draw();
        }
        else  {
           table.column(2).search('^' + status + '$', true, false, true).draw();
        }
      });
  
      // Search box
      $('#searchBox').on('keyup', function() { table.search(this.value).draw(); });

      loadEvaluationPeriods();
});

export function loadEvaluationPeriods() {
  fetch('/Smart-Eval/app/handlers/periods/get_periods.php')
  .then(res => res.json())
  .then(data => {
    table.clear();

    data.periods.forEach(period => {
      const today = new Date();

      let statusBadge, buttons;

      const baseBtn = "flex items-center gap-1 px-3 py-1.5 text-xs rounded-md transition-all duration-200 shadow-sm hover:scale-105 ";

      if (new Date(period.start_date) > today) {

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

      } else if (period.is_active) {

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

      } else {

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

      let rowNode = table.row.add([
        period.academic_year,
        period.semester,
        statusBadge.trim(),
        period.target_dept,
        buttons
      ]).draw(false).node();

      rowNode.classList.add( 'hover:bg-gray-100', 'p-6', 'cursor-pointer');
      rowNode.querySelectorAll('td').forEach(td => td.classList.add('px-4', 'py-2','md:text-sm', 'text-xs', 'whitespace-nowrap'));
      rowNode.querySelectorAll('button').forEach(btn => btn.classList.add('cursor-pointer'));
    });
  })
  .catch(err => console.error('Error loading teachers:', err));
}