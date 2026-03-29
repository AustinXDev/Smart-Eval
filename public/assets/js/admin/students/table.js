import { createDataTable } from "../shared/datatable_config.js";
import { fetchAllPrograms } from "./student_api.js";

let department;
let table;

  $(document).ready(async function() {
    const wrapper = document.getElementById('tableWrapper');
    department = wrapper ? wrapper.dataset.department : '';

    table = createDataTable('#studentsTable', {
      columnDefs: [{ orderable: false, targets: 5 }]
    }, 'Search students');

    const select = document.getElementById('courseFilter');
    const programs = await fetchAllPrograms(department); 
    programs.forEach(program => {
      const option = document.createElement('option');
      option.value = program.program_name;
      option.textContent = program.program_name;
      select.appendChild(option);
    })

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

    // Course Filter
    $('#courseFilter').on('change', function() {
      const program = $(this).val();
      if(program === 'All'){
        table.column(3).search('').draw();
      } else {
        table.column(3).search('^' + program + '$', true, false).draw();
      }
    });

    // Search box
    $('#searchBox').on('keyup', function() { table.search(this.value).draw(); });

    loadStudents();
    loadStudentCard();
  });


  export function loadStudents() {
    const url = '/Smart-Eval/app/handlers/students/get_students.php' + (department ? `?department=${department}` : '');

    fetch(url)
      .then(res => res.json())
      .then(data => {
        table.clear();

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

  export function loadStudentCard(){
    const url = '/Smart-Eval/app/handlers/students/get_students.php' + (department ? `?department=${department}` : '');

    fetch(url)
    .then(res => res.json())
    .then(data => {
      console.log(data);
        if(!data.counts) return;

        const counts = data.counts || { total: 0, active: 0, inactive: 0 };

        document.getElementById('total-students').textContent = Number(counts.active || 0);
        document.getElementById('total-active').textContent = Number(counts.active || 0);
        document.getElementById('total-inactive').textContent = Number(counts.inactive || 0);

        console.log('Teachers count:', counts);
    })
    .catch(err => console.error('Error loading student counts:', err));
  }