import { openModal, closeModal } from "../../modal/modal.js";
import {
  initAddProgramForm,
  initEditProgramForm,
  requestDeleteProgram,
} from "./components/programForm.js";
import { logout } from "../shared/logout.js";

initAddProgramForm();
initEditProgramForm();
logout();

document.addEventListener("click", (e) => {
  const addBtn = e.target.closest(".addProgram");
  const editBtn = e.target.closest(".editProgram");
  const deleteBtn = e.target.closest(".deleteProgram");
  const closeBtn = e.target.closest("[data-close-modal]");

  if (addBtn) return openModal("addProgramModal");
  if (editBtn) return handleEditClick(editBtn);
  if (deleteBtn) return requestDeleteProgram(deleteBtn.dataset.programId);
  if (closeBtn) return closeModal(closeBtn.dataset.closeModal);
});

function handleEditClick(editBtn) {
  const { programId, programCode, programName, programDepartment } =
    editBtn.dataset;

  document.getElementById("edit_program_id").value = programId || "";
  document.getElementById("edit_program_code").value = programCode || "";
  document.getElementById("edit_program_name").value = programName || "";
  document.getElementById("edit_department").value = programDepartment || "";

  openModal("editProgramModal");
}
