import { closeModal, showConfirmation } from "../../../modal/modal.js";
import { loadStudents, loadStudentCard } from "../table.js";
import {
  addStudent,
  reactivateStudent,
  editStudent,
  deleteStudent,
  resetStudentPassword,
} from "../api/api.js";

// ── Add student ─────────────────────────────────────
export function initAddForm() {
  const addForm = document.getElementById("addStudentForm");
  if (!addForm) return;

  addForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(addForm);

    StatusModal.confirm(
      "Add Student",
      "Do you want to add this student?",
      async () => {
        await submitAddStudent(formData, addForm);
      },
    );
  });
}

async function submitAddStudent(formData, addForm) {
  try {
    const response = await addStudent(formData);

    if (response.status === "success") {
      alert(response.message);
      closeModal("addStudentModal");
      addForm.reset();
      loadStudents();
      loadStudentCard();
    } else if (response.status === "inactive") {
      showConfirmation({
        title: "Reactivate Student",
        message:
          "This student exists but is inactive. Do you want to reactivate?",
        onConfirm: () => reactivateAndRefresh(response.student_id, addForm),
      });
    } else {
      alert(`Error: ${response.message}`);
    }
  } catch (err) {
    console.error(err);
  }
}

async function reactivateAndRefresh(studentId, addForm) {
  const response = await reactivateStudent(studentId);

  try {
    if (response.status === "success") {
      alert(response.message);
      closeModal("addStudentModal");
      addForm.reset();
      loadStudents();
      loadStudentCard();
    } else {
      alert(`Error: ${response.message}`);
    }
  } catch (err) {
    console.error(err);
  }
}

// ── Edit student ────────────────────────────────────
export function initEditForm() {
  const editForm = document.getElementById("editStudentForm");
  if (!editForm) return;

  editForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(editForm);
    const studentName = formData.get("full_name");

    showConfirmation({
      title: "Edit Student",
      message: `Are you sure you want to save changes for ${studentName}?`,
      onConfirm: () => submitEditStudent(formData),
    });
  });
}

async function submitEditStudent(formData) {
  try {
    const response = await editStudent(formData);

    if (response.status === "success") {
      alert(response.message);
      closeModal("editStudentModal");
      loadStudents();
    } else {
      alert(`Error: ${response.message}`);
    }
  } catch (error) {
    console.error(error);
  }
}

// ── Reset password ──────────────────────────────────
export function initResetPasswordButton() {
  const resetPasswordBtn = document.getElementById("resetPasswordBtn");
  if (!resetPasswordBtn) return;

  resetPasswordBtn.addEventListener("click", (e) => {
    const studentId = e.target.dataset.studentId;

    showConfirmation({
      title: "Reset Password",
      message:
        "Are you sure you want to reset the password of this student? The new password will be the same as their student ID.",
      onConfirm: () => submitResetPassword(studentId),
    });
  });
}

async function submitResetPassword(studentId) {
  try {
    const response = await resetStudentPassword(studentId);

    if (response.status === "success") {
      alert(response.message);
      closeModal("viewStudentModal");
    } else {
      alert(`Error: ${response.message}`);
    }
  } catch (error) {
    alert(err);
  }
}

// ── Delete student (with two-step warning confirmation) ──
export function requestDeleteStudent(studentId) {
  sendDeleteRequest(studentId, false);
}

function sendDeleteRequest(studentId, force = false) {
  showConfirmation({
    title: "Delete Student",
    message: "Are you sure you want to delete this student?",
    onConfirm: () => submitDeleteStudent(studentId, force),
  });
}

async function submitDeleteStudent(studentId, force) {
  try {
    const response = await deleteStudent(studentId, force);

    if (response.status === "success") {
      alert(response.message);
      loadStudents();
      loadStudentCard();
    } else if (response.status === "warning") {
      showConfirmation({
        title: "Warning",
        message: `${response.message} Do you want to proceed?`,
        onConfirm: () => sendDeleteRequest(studentId, true),
      });
    } else {
      alert(`Error: ${response.message}`);
    }
  } catch (error) {
    console.error(error);
  }
}
