let department;
 let table;

  $(document).ready(function() {
    const wrapper = document.getElementById('tableWrapper');
    department = wrapper ? wrapper.dataset.department : '';

    table = $('#teachersTable').DataTable({
      dom:
        "<'flex flex-col sm:flex-row sm:justify-between items-center  mb-4 gap-4'<'flex items-center gap-2'f><'flex items-center gap-2'l>>" +
        "rt" +
        "<'flex flex-col sm:flex-row sm:justify-between items-center mt-4 gap-2 info-pagination min-w-full'<'text-gray-600 'i><'pagination'p>>",
      paging: true,
      searching: true,
      info: true,
      lengthChange: false,
      pageLength: 5,
      ordering: false,
      columnDefs: [{ orderable: false, targets: 5 }],
      language: { lengthMenu: "_MENU_" },
      initComplete: function() { styleControls(); }
    });

    table.on('draw.dt', function() { stylePagination(); });

    // Status filter
    $('#statusFilter').on('change', function() {
      const status = $(this).val();
      if(status === 'All'){
        table.column(4).search('').draw();
      }
      else  {
         table.column(4).search('^' + status + '$', true, false).draw();
      }
      //else table.column(5).search('').draw();
    });

    // Search box
    $('#searchBox').on('keyup', function() { table.search(this.value).draw(); });

    loadTeachers();
  });

  function stylePagination() {
    $('.pagination .paginate_button').css({
      'padding': '0.25rem 0.75rem',
      'border': '1px solid #d1d5db',
      'border-radius': '5px',
      'font-size': '0.875rem',
      'color': '#374151',
      'margin': '0.25rem',
      'cursor': 'pointer',
      'display': 'inline-block',
      'background': 'white'
    });

    $('.pagination .paginate_button.current').css({
      'background': '#16213E',
      'color': '#ffffff',
      'border-color': '#16213E'
    });

    $('.pagination .paginate_button.disabled').css({
      'color': '#9ca3af',
      'cursor': 'not-allowed',
      'border-color': '#e5e7eb'
    });

    $('.pagination .paginate_button')
      .not('.current')
      .not('.disabled')
      .off('mouseenter mouseleave')
      .on('mouseenter', function() { $(this).css('background', '#f3f4f6'); })
      .on('mouseleave', function() { $(this).css('background', 'white'); });
  }

  function styleControls() {
    stylePagination();

    $('.dataTables_filter input')
      .attr('placeholder', 'Search teachers...')
      .addClass('border ml-2 rounded-md px-3 py-1 w-full sm:w-64 focus:ring-1 focus:ring-blue-300 focus:outline-none text-sm');

    $('.dataTables_length select')
      .addClass('border w-40 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer');

    $('.dataTables_filter label').addClass('mb-0').css('display', 'flex');
  }

  export function loadTeachers() {
    const url = '/Smart-Eval/app/handlers/get_teachers.php' + (department ? `?department=${department}` : '');

    fetch(url)
      .then(res => res.json())
      .then(data => {
        table.clear();

        data.teachers.forEach(teacher => {
          let rowNode = table.row.add([
            `<img class="rounded-full max-w-[35px] h-auto object-cover" src="/Smart-Eval/public/uploads/teachers/${teacher.image_path}">`,
              teacher.employee_id,
              teacher.full_name,
              teacher.department,
              teacher.is_active ? 'Active' : 'Inactive',
              `<div class="flex gap-2">
                  <button class="viewBtn bg-blue-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-eye"></i></button>
                  <button class="editBtn bg-green-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-edit"></i></button>
                  <button class="deleteBtn bg-red-500 text-white px-2 py-1 rounded hover:opacity-50 transition-all duration-200" data-teacher-id="${teacher.teacher_id}"><i class="fas fa-trash"></i></button>
              </div>`
          ]).draw(false).node();

          rowNode.classList.add( 'hover:bg-gray-100', 'p-6', 'cursor-pointer');
          rowNode.querySelectorAll('td').forEach(td => td.classList.add('px-6', 'py-2', 'border-b-1', 'border-gray-200', 'text-sm'));
          rowNode.querySelectorAll('button').forEach(btn => btn.classList.add('cursor-pointer'));
        });
      })
      .catch(err => console.error('Error loading teachers:', err));
  }

  //load teacher handles
export function loadTeacherHandles(teacherId) {
    const tbody = document.querySelector('#handleTable tbody');
    tbody.innerHTML = ''; // clear previous rows

    fetch(`/Smart-Eval/app/handlers/get_teachers.php?id=${teacherId}`)
      .then(res => res.json())
      .then(data => {
        const handles = data.handles || [];

        if (handles.length === 0) {
          tbody.innerHTML = `<tr><td colspan="3" class="text-center p-3">No handles assigned</td></tr>`;
          return;
        }

        handles.forEach(h => {
          const tr = document.createElement('tr');
          tr.classList.add('border-b', 'border-gray-400');
          tr.innerHTML = `
            <td class="py-3 px-5">
              <p class="bg-[#5B21B6] text-white px-2 py-1 rounded inline-block w-max">
                ${h.year_level} ${h.year_level <= 4 ? 'Year' : 'Grade'}
              </p>
            </td>
            <td>
              <p class="bg-[#A78BFA]/50 inline-block px-2 py-1 rounded w-max">${h.program_name}</p>
            </td>
            <td class="pr-5">
              <div class="flex justify-end items-center">
                <button class="deleteHandleBtn" data-year="${h.year_level}" data-program="${h.program_name}" data-department="${department}">
                  <i class="fas fa-trash text-red-900 cursor-pointer transform transition-transform duration-200 hover:scale-110"></i>
                </button>
              </div>
            </td>
          `;
          tbody.appendChild(tr);
        });
      })
      .catch(err => console.error(err));
  }

export function loadCard() {
  const container = document.getElementById('card-container');
  const card_department = container.dataset.department ?? '';

  console.log(department);
  fetch(`/Smart-Eval/app/handlers/get_teachers.php?department=${card_department}`)
  .then(res => res.json())
  .then(data => {

    if (!data.counts) return;

    const counts = data.counts;
    
    document.getElementById('total-teachers').textContent = Number(counts.total) || 0;
    document.getElementById('total-active').textContent = Number(counts.active) || 0;
    document.getElementById('total-inactive').textContent = Number(counts.inactive) || 0;

    console.log('Teachers count:', counts); // debugging

  })
  .catch(err => console.log(err));
}