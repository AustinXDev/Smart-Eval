import { openModal, closeModal } from "../../modal/modal.js";
import { getStudent } from "./api/api.js";
import { populateProgramSelect } from "./components/programSelect.js";
import {
  renderViewModal,
  renderEditModal,
} from "./components/studentRender.js";
import { initCsvUpload } from "./components/csvUpload.js";
import {
  initAddForm,
  initEditForm,
  initResetPasswordButton,
  requestDeleteStudent,
} from "./components/studentForm.js";

const wrapper = document.getElementById("tableWrapper");
const department = wrapper ? wrapper.dataset.department : "";

initCsvUpload(department);
initAddForm();
initEditForm();
initResetPasswordButton();

document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".add-btn");
  const csvBtn = e.target.closest(".csv-btn");
  const viewBtn = e.target.closest(".viewBtn");
  const editBtn = e.target.closest(".editBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest("[data-close-modal]");

  if (addBtn) return handleAddClick();
  if (csvBtn) return openModal("uploadCSVModal");
  if (viewBtn) return handleViewClick(viewBtn);
  if (editBtn) return handleEditClick(editBtn);
  if (deleteBtn) return handleDeleteClick(deleteBtn);
  if (closeBtn) return closeModal(closeBtn.dataset.closeModal);
});

function handleAddClick() {
  populateProgramSelect(department);
  openModal("addStudentModal");
}

async function handleViewClick(viewBtn) {
  const studentId = viewBtn.dataset.studentId;

  const data = await getStudent(studentId);

  console.log(data);

  renderViewModal(data.student);
  openModal("viewStudentModal");
}

function handleEditClick(editBtn) {
  const studentId = editBtn.dataset.studentId;

  getStudent(studentId).then((data) => renderEditModal(data.student));
  openModal("editStudentModal");
}

function handleDeleteClick(deleteBtn) {
  const studentId = deleteBtn.dataset.studentId;
  if (!studentId) return;

  requestDeleteStudent(studentId);
}
