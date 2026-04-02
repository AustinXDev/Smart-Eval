import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { loadQuestionSetList } from "./question_set_list.js";

document.addEventListener('click', (e) => {
  const addSet = e.target.closest('.addSet');


  if(addSet){
    openModal('createQuestionSetModal');
    return;
  }
});

//trigger close modal
document.addEventListener('click', (e) => {
  const closeBtn = e.target.closest('[data-close-modal]');
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

//create question set
const questionSetForm = document.getElementById('createQuestionSetForm');
questionSetForm.addEventListener('submit', (e) =>  {
  e.preventDefault();

  const formData = new FormData(questionSetForm);
  const setName = formData.get('set_name') || 'this question set';

  showConfirmation({
    title: 'Add Question Set',
    message: `Are you sure you want to add ${setName}?`,
    onConfirm: () => {
       fetch('/Smart-Eval/app/handlers/questionnaires/add_set.php', {
        method: 'POST',
        body: formData
       })
       .then(res => res.json())
       .then(data => {
        if(data.status === 'success'){
          alert(data.message);
          closeModal('createQuestionSetModal');
          questionSetForm.reset();
          loadQuestionSetList();
        } else {
          alert(data.message);
        }
       })
       .catch(err => console.log(err))
    } 
  })
});