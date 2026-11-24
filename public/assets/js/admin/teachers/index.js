import { openModal, closeModal } from "../../modal/modal.js";
import { loadTeacherHandles } from "../teachers/table.js";
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
import { initCsvImport } from "./components/csvImport.js";

const wrapper = document.getElementById("tableWrapper");
const department = wrapper ? wrapper.dataset.department : "";

initAddTeacherForm();
initEditTeacherForm();
initAddHandleForm();
initDeleteHandleButtons();
initCsvImport(department);
window.logout = logout;

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
  const csvBtn = e.target.closest(".csv-btn");
  const viewBtn = e.target.closest(".viewBtn");
  const editBtn = e.target.closest(".editBtn");
  const deleteBtn = e.target.closest(".deleteBtn");
  const closeBtn = e.target.closest("[data-close-modal]");

  if (addBtn) return openModal("addTeacherModal");
  if (csvBtn) return openModal("csvImportModal");
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
    // The details response already carries the handles; render them directly
    // (no second request). The row's id is used for later refreshes.
    if (teacher) loadTeacherHandles(teacherId, teacher.handles);
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
    // The edit modal's own elements (these used to point at the Add modal).
    fileNameId: "editTeacherFileName",
    checkId: "editPhotoCheck",
    formId: "editTeacherForm",
    emptyFileNameText: "No new photo selected",
  });
}

function handleDeleteClick(deleteBtn) {
  requestDeleteTeacher(deleteBtn.dataset.teacherId);
}

// The KPI cards are loaded by table.js on page ready.
