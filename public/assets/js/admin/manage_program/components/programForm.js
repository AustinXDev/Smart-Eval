import { closeModal } from "../../../modal/modal.js";
import { loadPrograms, loadProgramCard } from "../table.js";
import { addProgram, editProgram, deleteProgram } from "../api/api.js";
import { setLoading } from "../../shared/Loader.js";

// ── Add program ───────────────────────────────────────
export function initAddProgramForm() {
  const form = document.getElementById("addProgramForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Add Program",
      "Are you sure you want to add this program?",
      () => submitAddProgram(formData, form),
    );
  });
}

async function submitAddProgram(formData, form) {
  setLoading();
  try {
    const data = await addProgram(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      form.reset();
      await loadPrograms();
      closeModal("addProgramModal");
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Edit program ──────────────────────────────────────
export function initEditProgramForm() {
  const form = document.getElementById("editProgramForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm("Edit Program", "Are you sure you want to edit?", () =>
      submitEditProgram(formData),
    );
  });
}

async function submitEditProgram(formData) {
  setLoading();
  try {
    const data = await editProgram(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      await loadPrograms();
      closeModal("editProgramModal");
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete program ────────────────────────────────────
export function requestDeleteProgram(programId) {
  if (!programId) return;

  StatusModal.confirm(
    "Delete Program",
    "Are you sure you want to delete this program?",
    () => submitDeleteProgram(programId),
  );
}

async function submitDeleteProgram(programId) {
  setLoading();
  try {
    const data = await deleteProgram(programId);

    console.log(data);

    if (data.status === "success" || data.status === "warning") {
      StatusModal.show("Success", data.message, "success");
      await Promise.all([loadProgramCard(), loadPrograms()]);
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}
