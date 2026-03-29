import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { fetchAllQuestionSets } from "../evaluation_periods/question_set_api.js";
import { loadEvaluationPeriods } from "./table.js";


// Populate question set option in the create evaluation periods modal
async function populateQuestionsSet() {
    const questionSetSelect = document.getElementById('questionSetSelect');
    if (!questionSetSelect) return;

    questionSetSelect.innerHTML = '<option disabled selected>Select Question Bank</option>';

    const questionSets = await fetchAllQuestionSets();
    //console.log(questionSets);
    questionSets.forEach(qs => {
        const option = document.createElement('option');
        option.value = qs.set_id;
        option.textContent = qs.set_name;
        questionSetSelect.appendChild(option);
    });
}

// Event trigger to open modal
document.addEventListener('click', (e) => {
  const createBtn = e.target.closest('.createPeriodBtn');

  if(createBtn) {
    openModal('createPeriodModal');
    populateQuestionsSet();
    return;
  }
});

document.addEventListener('click', (e) => {
  const closeBtn = e.target.closest('[data-close-modal]');
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});


// Additional event listeners for form submission and other interactions

const createPeriodForm = document.getElementById('createPeriodForm');
createPeriodForm.addEventListener('submit', (e) => {
  e.preventDefault();

  //gather form data
  const formData = new FormData(createPeriodForm);

  showConfirmation({
    title: 'Confirm Creation',
    message: 'Are you sure you want to create this evaluation period?',
    onConfirm: () => {
        fetch('/Smart-Eval/app/handlers/periods/create_period.php', {
          method: 'POST',
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          console.log(data);
          if(data.status === 'success') {
            alert(data.message);
            createPeriodForm.reset();
          } else {
            alert(data.message);
          }
        })
        .catch(err => {
          console.error('Error while creating evaluation period', err);
          alert('An error occured. Please try again later.');
        })
    }
  })

});

//auto load active evaluation
function updatePeriodAndReload(){
  fetch('/Smart-Eval/scripts/auto_active_period.php')
  .then(res => res.text())
  .then(() => {
    loadEvaluationPeriods();
  })
  .catch(err => console.log('Error updating periods:', err));
}

//load page
$(document).ready(() => {
  updatePeriodAndReload();

  setTimeout(() => {
    updatePeriodAndReload();
  }, 3600000);
})