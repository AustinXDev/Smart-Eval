import { createDataTable } from "../shared/datatable_config.js";

let department;
let table;

  $(document).ready(function() {
    const wrapper = document.getElementById('tableWrapper');
    department = wrapper ? wrapper.dataset.department : '';

    table = createDataTable('#studentsTable', {
      columnDefs: [{ orderable: false, targets: 5 }]
    }, 'Search students');

    // Status filter
    $('#statusFilter').on('change', function() {
      const status = $(this).val();
      if(status === 'All'){
        table.column(4).search('').draw();
      }
      else  {
         table.column(4).search('^' + status + '$', true, false).draw();
      }
    });

    // Search box
    $('#searchBox').on('keyup', function() { table.search(this.value).draw(); });

    loadStudents();
  });


  export function loadStudents() {
    const url = '/Smart-Eval/app/handlers/students/get_students.php' + (department ? `?department=${department}` : '');

    fetch(url)
      .then(res => res.json())
      .then(data => {
        table.clear();
        console.log(data);

        data.students.forEach(student => {
          let statusBadge = student.is_active
          ? `<span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-semibold">Active</span>`
          : `<span class="bg-red-100 text-red-700 px-2 py-1 rounded text-xs font-semibold">Inactive</span>`;

          let rowNode = table.row.add([
              student.student_id,
              student.full_name,
              student.department.toUpperCase(),
              student.program_name.toUpperCase(),
              statusBadge,
              `<div class="flex gap-2">
                  <button class="viewBtn bg-blue-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-student-id="${student.student_id}"><i class="fas fa-eye"></i></button>
                  <button class="editBtn bg-green-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-student-id="${student.student_id}"><i class="fas fa-edit"></i></button>
                  <button class="deleteBtn bg-red-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-student-id="${student.student_id}"><i class="fas fa-trash"></i></button>
              </div>`
          ]).draw(false).node();

          rowNode.classList.add( 'hover:bg-gray-100', 'p-6', 'cursor-pointer');
          rowNode.querySelectorAll('td').forEach(td => td.classList.add('px-6', 'py-2','md:text-sm', 'text-xs', 'whitespace-nowrap'));
          rowNode.querySelectorAll('button').forEach(btn => btn.classList.add('cursor-pointer'));
        });
      })
      .catch(err => console.error('Error loading teachers:', err));
  }