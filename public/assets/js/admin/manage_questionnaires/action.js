import { openModal, closeModal, showConfirmation } from "../../modal/modal.js";
import { loadQuestionSetList, loadQuestions } from "./list.js";

let currentSetId = null;

document.addEventListener("click", (e) => {
  const addSet = e.target.closest(".addSet");
  const manageQuestionBtn = e.target.closest(".manageQuestion");
  const editBtn = e.target.closest(".editQuestion");
  const deleteQuestionBtn = e.target.closest(".deleteQuestion");
  const editSetBtn = e.target.closest(".editSet");
  const deleteSetBtn = e.target.closest(".deleteSet");

  //oped add set modal
  if (addSet) {
    openModal("createQuestionSetModal");
    return;
  }

  //open manage set modal
  if (manageQuestionBtn) {
    currentSetId = manageQuestionBtn.dataset.setId;

    document.getElementById("set_id_input").value = currentSetId;

    openModal("manageQuestionsModal");
    loadQuestions(currentSetId);
    return;
  }

  //open edit modal
  if (editBtn) {
    const questionId = editBtn.dataset.questionId;
    const editSetId = editBtn.dataset.setId;
    const questionText = editBtn.dataset.questionText;
    const category = editBtn.dataset.category;

    // DEBUG: check that values are correct
    console.log({ questionId, editSetId, questionText, category });

    // Populate the hidden inputs
    document.getElementById("question_id_input").value = questionId;
    document.getElementById("set_input").value = editSetId;

    // Populate the form fields
    document.querySelector(
      '#editQuestionForm input[name="question_text"]',
    ).value = questionText;
    document.querySelector('#editQuestionForm select[name="category"]').value =
      category;

    // Open modal
    openModal("editQuestionModal");
  }

  //delete question
  if (deleteQuestionBtn) {
    const question_id = deleteQuestionBtn.dataset.questionId;

    showConfirmation({
      title: "Delete Question",
      message: "Are you sure you want to delete this question?",
      onConfirm: () => {
        fetch("/Smart-Eval/app/handlers/questionnaires/delete_question.php", {
          method: "POST",
          body: new URLSearchParams({ question_id: question_id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success" || data.status === "warning") {
              alert(data.message);
              loadQuestions(currentSetId);
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.log(err));
      },
    });
  }

  //triggers edit set modal
  if (editSetBtn) {
    const set_id = editSetBtn.dataset.setId;
    const set_name = editSetBtn.dataset.setName;

    document.getElementById("edit_set_id").value = set_id;
    document.getElementById("set_name_input").value = set_name;

    openModal("editQuestionSetModal");
  }

  if (deleteSetBtn) {
    const set_id = deleteSetBtn.dataset.setId;
    const set_name = deleteSetBtn.dataset.setName;

    showConfirmation({
      title: "Delete Set",
      message: `Are you sure you want to delete ${set_name} set?`,
      onConfirm: () => {
        fetch("/Smart-Eval/app/handlers/questionnaires/delete_set.php", {
          method: "POST",
          body: new URLSearchParams({ set_id: set_id }),
        })
          .then((res) => res.json())
          .then((data) => {
            if (data.status === "success") {
              alert(data.message);
              loadQuestionSetList();
            } else if (data.status === "warning") {
              alert(data.message);
              loadQuestionSetList();
            } else {
              alert(data.message);
            }
          })
          .catch((err) => console.log(err));
      },
    });
  }
});

//trigger close modal
document.addEventListener("click", (e) => {
  const closeBtn = e.target.closest("[data-close-modal]");
  if (closeBtn) closeModal(closeBtn.dataset.closeModal);
});

//create question set
const questionSetForm = document.getElementById("createQuestionSetForm");
questionSetForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(questionSetForm);
  const setName = formData.get("set_name") || "this question set";

  showConfirmation({
    title: "Add Question Set",
    message: `Are you sure you want to add ${setName}?`,
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/questionnaires/add_set.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            closeModal("createQuestionSetModal");
            questionSetForm.reset();
            loadQuestionSetList();
          } else {
            alert(data.message);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//add question
const addQuestionForm = document.getElementById("addQuestionForm");

addQuestionForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(addQuestionForm);

  showConfirmation({
    title: "Add Question",
    message: "Are you sure you want to add this question?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/questionnaires/add_question.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            loadQuestionSetList();
            loadQuestions(currentSetId);
            addQuestionForm.reset();
          } else if (data.status === "warning") {
            showConfirmation({
              title: "Active Question",
              message: data.message,
              onConfirm: () => {
                fetch(
                  "/Smart-Eval/app/handlers/questionnaires/active_question.php",
                  {
                    method: "POST",
                    body: new URLSearchParams({
                      question_id: data.question_id,
                    }),
                  },
                )
                  .then((res) => res.json())
                  .then((data) => {
                    if (data.status === "success") {
                      alert(data.message);
                      loadQuestionSetList();
                      loadQuestions(currentSetId);
                    } else {
                      alert(data.message);
                    }
                  })
                  .catch((err) => console.log(err));
              },
            });
          } else {
            alert(data.message);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//edit questions
const editQuestionForm = document.getElementById("editQuestionForm");

editQuestionForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(editQuestionForm);

  showConfirmation({
    title: "Edit Question",
    message: "Are you sure you want to edit?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/questionnaires/edit_question.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            loadQuestions(currentSetId);
            closeModal("editQuestionForm");
            editQuestionForm.reset();
          } else {
            alert(data.message);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});

//Edit Set
const editSetForm = document.getElementById("editQuestionSetForm");
editSetForm.addEventListener("submit", (e) => {
  e.preventDefault();

  const formData = new FormData(editSetForm);

  showConfirmation({
    title: "Edit Set Name",
    message: "Are you sure you want to edit this set?",
    onConfirm: () => {
      fetch("/Smart-Eval/app/handlers/questionnaires/edit_set.php", {
        method: "POST",
        body: formData,
      })
        .then((res) => res.json())
        .then((data) => {
          if (data.status === "success") {
            alert(data.message);
            loadQuestionSetList();
            closeModal("editQuestionSetModal");
          } else {
            alert(data.message);
          }
        })
        .catch((err) => console.log(err));
    },
  });
});
