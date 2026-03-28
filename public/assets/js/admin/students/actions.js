import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { loadStudents } from "./table.js";
import { fetchAllPrograms } from "./student_api.js";

const wrapper = document.getElementById('tableWrapper');
const department = wrapper ? wrapper.dataset.department : '';

const csvForm = document.getElementById('uploadCsvForm');
const csvInput = document.getElementById('csvInput');
const csvSummaryContent = document.getElementById('csvSummaryContent');

//populate the select program
async function populateProgramSelect(department){
  const select = document.getElementById('programSelect');
  if(!select) return;

  select.innerHTML = '<option value="" disabled> SELECT Program </option>';

  const programs = await fetchAllPrograms(department);

  console.log(programs);

  programs.forEach(program => {
    const option = document.createElement('option');
    option.value = program.program_id;
    option.textContent = program.program_name;
    select.appendChild(option);
  });
}

document.addEventListener('click', (e) => {
  const addBtn = e.target.closest('.add-btn');
  const csvBtn = e.target.closest('.csv-btn');

  if(addBtn){
    populateProgramSelect(department);
    openModal('addStudentModal');
    return;
  }

  if(csvBtn){
    openModal('uploadCSVModal');
    return;
  }

})

document.addEventListener('click', (e) => {
  const closeBtn = e.target.closest('[data-close-modal]');
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

//handle add student fetch
const addForm = document.getElementById('addStudentForm');
addForm.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(addForm);

  showConfirmation({
    title: 'Add Student',
    message: 'Do you want to add this student?',
    onConfirm: () => {
      fetch('/Smart-Eval/app/handlers/students/add_students.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        console.log(data);
        if (data.status === 'success'){
          alert(data.message);
          closeModal('addStudentModal');
          addForm.reset();
          loadStudents();
        } else if (data.status === 'inactive') {
          showConfirmation({
            title: 'Reactive Student',
            message: 'This student exist but is inactive. Do you want to reactive?',
            onConfirm: () => {
              fetch('/Smart-Eval/app/handlers/students/reactive_student.php', {
                method: 'POST',
                body: new URLSearchParams({ student_id: data.student_id })
              })
              .then(res => res.json())
              .then(resp => {
                if(resp.status === 'success'){
                  alert(resp.message);
                  close('addStudentModal');
                  addForm.reset();
                  loadStudents();
                } else {
                  alert(`Error: ${resp.message}`);
                }
              })
               .catch(err => console.log(err));
            }
          })
        }    
        else {
          alert(`Error: ${data.message}`);
        }
      })
      .catch(err => console.log(err));
    }

  })
});

//csv upload
csvForm.addEventListener('submit', (e) => {
  e.preventDefault();

  const file = csvInput.files[0];
  if (!file) return alert('Please select a CSV file.');

  const formData = new FormData();
  formData.append('csv', file);
  formData.append('department', department);

  showConfirmation({
    title: 'Upload CSV',
    message: 'Are you sure you want to upload this CSV file?',
    onConfirm: () => {
      openModal('uploadLoading');
      const startTime = Date.now();

      fetch('/Smart-Eval/app/handlers/students/csv_upload.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        const elapsed = Date.now() - startTime;
        const delay = Math.max(3000 - elapsed, 0);

        let html = `<p><strong>Total Records:</strong> ${data.total}</p>
                    <p><strong>Successfully Imported:</strong> ${data.success}</p>
                    <p><strong>Failed:</strong> ${data.failed}</p>`;

        if (data.errors && data.errors.length > 0) {
          html += '<ul class="ml-4 list-disc text-red-600">';
          data.errors.forEach(err => html += `<li>${err}</li>`);
          html += '</ul>';
        }

        csvSummaryContent.innerHTML = html;

        closeModal('uploadCSVModal'); // close upload modal

        setTimeout(() => {
          closeModal('uploadLoading'); // hide loader after delay
          openModal('csvSummaryModal');
        }, delay);

        if (data.success > 0) loadStudents(); // refresh table if any imported
      })
      .catch(err => console.log(err));
    }
  });
});
