import { openModal, closeModal } from "../../modal/modal.js";
import { loadCard, loadTeacherHandles } from "../teachers/table.js";
import { getTeacher } from "./api/api.js";
import { populateProgramSelect } from "./components/programSelect.js";
import {
  renderViewDetails,
  renderEditForm,
} from "./components/teacherRender.js";
import { setCurrentTeacherId } from "./components/state.js";
import { initImagePreview } from "./components/imagePreview.js";
import {
  initAddTeacherForm,
  initEditTeacherForm,
  initAddHandleForm,
  initDeleteHandleButtons,
  requestDeleteTeacher,
} from "./components/teacherForm.js";
import { logout } from "../shared/logout.js";

const wrapper = document.getElementById("tableWrapper");
const department = wrapper ? wrapper.dataset.department : "";

initAddTeacherForm();
initEditTeacherForm();
initAddHandleForm();
initDeleteHandleButtons();
logout();

initImagePreview("fileInput", "teacherPhotoPreview", {
  maxSizeMB: 5,
  fileNameId: "fileName",
  placeholderId: "photoPlaceholder",
  checkId: "photoCheck",
  formId: "addTeacherForm",
  emptyFileNameText: "No file selected",
});

document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".add-btn");
  const viewBtn = e.target.closest(".viewBtn");
  const editBtn = e.target.closest(".editBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest("[data-close-modal]");

  if (addBtn) return openModal("addTeacherModal");
  if (viewBtn) return handleViewClick(viewBtn);
  if (editBtn) return handleEditClick(editBtn);
  if (deleteBtn) return handleDeleteClick(deleteBtn);
  if (closeBtn) return closeModal(closeBtn.dataset.closeModal);
});

function handleViewClick(viewBtn) {
  const teacherId = viewBtn.dataset.teacherId;
  setCurrentTeacherId(teacherId);

  populateProgramSelect(department);

  getTeacher(teacherId).then((teacher) => {
    renderViewDetails(teacher);
    if (teacher?.teacher_id) loadTeacherHandles(teacher.teacher_id);
  });

  openModal("viewDetails");
}

async function handleEditClick(editBtn) {
  const teacherId = editBtn.dataset.teacherId;

  setCurrentTeacherId(teacherId);

  const teacher = await getTeacher(teacherId);

  console.log(teacher);

  renderEditForm(teacher);

  openModal("editTeacherModal");

  initImagePreview("editTeacherPhotoInput", "editTeacherPhotoPreview", {
    defaultSrc: `uploads/teachers/${teacher.image_path}`,
    maxSizeMB: 5,
    fileNameId: "fileName",
    placeholderId: "photoPlaceholder",
    checkId: "photoCheck",
    formId: "editTeacherForm",
    emptyFileNameText: "No file selected",
  });
}

function handleDeleteClick(deleteBtn) {
  requestDeleteTeacher(deleteBtn.dataset.teacherId);
}

window.addEventListener("DOMContentLoaded", () => {
  loadCard();
});
