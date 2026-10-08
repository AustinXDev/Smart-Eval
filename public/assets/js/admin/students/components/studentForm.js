import { closeModal, showConfirmation } from "../../../modal/modal.js";
import { loadStudents, loadStudentCard } from "../table.js";
import {
  addStudent,
  reactivateStudent,
  editStudent,
  deleteStudent,
  resetStudentPassword,
} from "../api/api.js";
import { setLoading } from "../../shared/Loader.js";

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
  setLoading();

  try {
    const response = await addStudent(formData);

    if (response.status === "success") {
      StatusModal.show("Success", response.message, "success");
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
      StatusModal.show("Failed", response.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

async function reactivateAndRefresh(studentId, addForm) {
  setLoading();
  try {
    const response = await reactivateStudent(studentId);

    if (response.status === "success") {
      StatusModal.show("Success", response.message, "success");
      closeModal("addStudentModal");
      addForm.reset();
      loadStudents();
      loadStudentCard();
    } else {
      StatusModal.show("Failed", response.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
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
  setLoading();
  try {
    const response = await editStudent(formData);

    if (response.status === "success") {
      StatusModal.show("Success", response.message, "success");
      closeModal("editStudentModal");
      loadStudents();
    } else {
      StatusModal.show("Failed", response.message, "error");
    }
  } catch (error) {
    console.error(error);
  } finally {
    setLoading("", false);
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
  setLoading();
  try {
    const response = await resetStudentPassword(studentId);

    if (response.status === "success") {
      StatusModal.show("Success", response.message, "success");
      closeModal("viewStudentModal");
    } else {
      StatusModal.show("Failed", response.message, "error");
    }
  } catch (error) {
    StatusModal.show("Error", response.message, "error");
  } finally {
    setLoading("", false);
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

async function submitDeleteStudent(studentId, force = false) {
  setLoading(force ? "Permanently deleting student..." : "Deleting student...");

  try {
    const response = await deleteStudent(studentId, force);

    // ============================================================
    // SUCCESS
    // ============================================================

    if (response.status === "success") {
      StatusModal.show("Success", response.message, "success");

      await Promise.all([loadStudents(), loadStudentCard()]);

      return;
    }

    // ============================================================
    // WARNING
    // ============================================================

    if (response.status === "warning" && !force) {
      // Stop loading before opening confirmation
      setLoading("", false);

      showConfirmation({
        title: "Warning",
        message: `${response.message} Do you want to proceed?`,

        onConfirm: () => {
          // Directly perform FORCE delete
          submitDeleteStudent(studentId, true);
        },
      });

      return;
    }

    // ============================================================
    // ERROR
    // ============================================================

    StatusModal.show("Failed", response.message, "error");
  } catch (error) {
    console.error("Delete student error:", error);

    StatusModal.show("Error", response.message, "error");
  } finally {
    setLoading("", false);
  }
}
