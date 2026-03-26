import { openModal, closeModal, showConfirmation } from '../modal/modal.js';
import { loadTeachers, loadTeacherHandles } from './data_table.js';

let teacherId; //global teacher id 

// Open Add Teacher modal
document.addEventListener('click', (e) => {
  const addBtn = e.target.closest('.add-btn');
  const viewBtn = e.target.closest('.viewBtn');

  if (addBtn) {
    openModal('addTeacherModal');

  }
  if (viewBtn){
    teacherId = viewBtn.dataset.teacherId;
    
    fetch(`/Smart-Eval/app/handlers/get_teachers.php?id=${teacherId}`)
      .then(res => res.json())
      .then(data => {
        const teacher = data[0]; // teacher info
        //const handles = data.handles; // handles array

        if(teacher && teacher.teacher_id){
          document.getElementById('name').textContent = teacher.full_name;
          document.getElementById('id').textContent = teacher.employee_id;
          document.getElementById('department').textContent = teacher.department.toUpperCase() + ' ' + 'Department';
          document.getElementById('email').textContent = teacher.email;
          document.getElementById('handle_teacher_id').value = teacher.teacher_id;

          loadTeacherHandles(teacher.teacher_id);

          const img = document.querySelector('#viewDetails img');
          img.src = teacher.image_path 
            ? `/Smart-Eval/public/uploads/teachers/${teacher.image_path}`
            : `/Smart-Eval/public/uploads/teachers/default_teacher.png`;

          }
      })

    openModal('viewDetails');

  }

  return;
});

document.addEventListener('click', (e) => {
  const closeBtn = e.target.closest('[data-close-modal]');
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

// Add Teacher form submission
const form = document.getElementById('addTeacherForm');
form.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(form);

  showConfirmation({
    title: "Add Teacher",
    message: "Are you sure you want to add this teacher?",
    onConfirm: () => {
      fetch('/Smart-Eval/app/handlers/add_teacher.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.status === "success") {
          alert(data.message);
          closeModal('addTeacherModal');
          form.reset();
          loadTeachers();
        } else {
          alert(data.errors ? data.errors.join("\n") : data.message);
        }
      })
      .catch(err => console.error(err));
    }
  });
});


//Add handles 
const Handleform = document.getElementById('addHandleForm');

Handleform.addEventListener('submit' , (e) => {
  e.preventDefault();
  const formData = new FormData(Handleform);

  showConfirmation({
    title: "Add Course and Year Handle",
    message: "Are you sure want to add this teacher",
    onConfirm: () => {
      fetch('/Smart-Eval/app/handlers/add_handle.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success'){
          alert(data.message);
          //closeModal('viewDetails');
          Handleform.reset();
          loadTeacherHandles(teacherId);
        } else {
          alert(data.errors ? data.errors.join("\n") : data.message);
        }
      })
      .catch(err => console.log(err));
    }
  })
})

//delete handles
document.addEventListener('click', (e) => {
  const deleteBtn = e.target.closest('.deleteHandleBtn');
  if(!deleteBtn) return;

  const year = deleteBtn.dataset.year;
  const program = deleteBtn.dataset.program;
  const department = deleteBtn.dataset.department;

  console.log(department)

  showConfirmation({
    title: 'Delete Handle',
    message: `Are you sure you want to remove ${program} - ${year} year from this teacher?`,
    onConfirm: () => {
      fetch('/Smart-Eval/app/handlers/delete_handle.php', {
        method: 'POST',
        body: JSON.stringify({
          teacher_id: teacherId,
          year_level: year,
          program_name: program,
          department: department
        }),
        headers: {
          'Content-Type': 'application/json'
        }
      })
      .then(res => res.json())
      .then(data => {
        if(data.status === 'success'){
          alert(data.message);
          loadTeacherHandles(teacherId);
        } else if (data.status === 'warning') {
          if (confirm(data.message)) {
              fetch(`/Smart-Eval/app/handlers/delete_handle.php`, {
                  method: 'POST',
                  body: JSON.stringify({
                      teacher_id: teacherId,
                      year_level: year,
                      program_name: program,
                      department: department,
                      force_delete: true
                  }),
                  headers: {
                      'Content-Type': 'application/json'
                  }
              })
              .then(r => r.json())
              .then(res => {
                  if(res.status === 'success'){
                      alert(res.message);
                      loadTeacherHandles(teacherId);
                  }
              });
            }
          } else {
            alert(data.message);
          }
      });
    } 
  });

})