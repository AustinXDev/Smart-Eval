import { openModal, closeModal } from "../../modal/modal.js";
import {
  loadQuestionSetList,
  initQuestionSetSearch,
  loadQuestions,
} from "./components/questionRender.js";
import { setCurrentSetId } from "./components/questionState.js";
import {
  initCreateSetForm,
  initEditSetForm,
  initAddQuestionForm,
  initEditQuestionForm,
  requestDeleteSet,
  requestDeleteQuestion,
} from "./components/questionForm.js";
import { logout } from "../shared/logout.js";

initCreateSetForm();
initEditSetForm();
initAddQuestionForm();
initEditQuestionForm();
logout();

document.addEventListener("click", (e) => {
  const addSetBtn = e.target.closest(".addSet");
  const manageQuestionBtn = e.target.closest(".manageQuestion");
  const editQuestionBtn = e.target.closest(".editQuestion");
  const deleteQuestionBtn = e.target.closest(".deleteQuestion");
  const editSetBtn = e.target.closest(".editSet");
  const deleteSetBtn = e.target.closest(".deleteSet");
  const closeBtn = e.target.closest("[data-close-modal]");

  if (addSetBtn) return openModal("createQuestionSetModal");
  if (manageQuestionBtn) return handleManageQuestionClick(manageQuestionBtn);
  if (editQuestionBtn) return handleEditQuestionClick(editQuestionBtn);
  if (deleteQuestionBtn) return handleDeleteQuestionClick(deleteQuestionBtn);
  if (editSetBtn) return handleEditSetClick(editSetBtn);
  if (deleteSetBtn) return handleDeleteSetClick(deleteSetBtn);
  if (closeBtn) return closeModal(closeBtn.dataset.closeModal);
});

function handleManageQuestionClick(btn) {
  const setId = btn.dataset.setId;
  setCurrentSetId(setId);

  document.getElementById("set_id_input").value = setId;

  openModal("manageQuestionsModal");
  loadQuestions(setId);
}

function handleEditQuestionClick(btn) {
  const { questionId, setId, questionText, category } = btn.dataset;

  document.getElementById("question_id_input").value = questionId;
  document.getElementById("set_input").value = setId;

  document.querySelector(
    '#editQuestionForm input[name="question_text"]',
  ).value = questionText;
  document.querySelector('#editQuestionForm select[name="category"]').value =
    category;

  openModal("editQuestionModal");
}

function handleDeleteQuestionClick(btn) {
  requestDeleteQuestion(btn.dataset.questionId);
}

function handleEditSetClick(btn) {
  const { setId, setName } = btn.dataset;

  document.getElementById("edit_set_id").value = setId;
  document.getElementById("set_name_input").value = setName;

  openModal("editQuestionSetModal");
}

function handleDeleteSetClick(btn) {
  const { setId, setName } = btn.dataset;
  requestDeleteSet(setId, setName);
}

initQuestionSetSearch("questionnaireSearch");

document.addEventListener("DOMContentLoaded", () => {
  loadQuestionSetList();
});
