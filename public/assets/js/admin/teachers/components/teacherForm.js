import { closeModal } from "../../../modal/modal.js";
import { loadTeachers, loadTeacherHandles, loadCard } from "../table.js";
import {
  addTeacher,
  editTeacher,
  deleteTeacher,
  addHandle,
  deleteHandle,
} from "../api/api.js";
import { getCurrentTeacherId } from "./state.js";
import { setLoading } from "../../shared/Loader.js";

// StatusModal is loaded globally via <script> (see StatusModal.js), not an ES module export.

// ── Add teacher ─────────────────────────────────────
export function initAddTeacherForm() {
  const form = document.getElementById("addTeacherForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Add Teacher",
      "Are you sure you want to add this teacher?",
      () => submitAddTeacher(formData, form),
    );
  });
}

async function submitAddTeacher(formData, form) {
  setLoading();
  try {
    const data = await addTeacher(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      closeModal("addTeacherModal");
      form.reset();
      loadTeachers();
      loadCard();
    } else {
      const message =
        data?.message ||
        data?.data?.message ||
        "An error occurred while processing your request.";
      StatusModal.show("Error", message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Edit teacher ────────────────────────────────────
export function initEditTeacherForm() {
  const form = document.getElementById("editTeacherForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    console.log(Object.fromEntries(formData));

    StatusModal.confirm(
      "Edit Teacher",
      "Are you sure you want to edit this teacher?",
      () => submitEditTeacher(formData, form),
    );
  });
}

async function submitEditTeacher(formData, form) {
  setLoading();
  try {
    const data = await editTeacher(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      closeModal("editTeacherModal");
      form.reset();
      loadTeachers();
    } else {
      StatusModal.show("Error", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete teacher ──────────────────────────────────
export function requestDeleteTeacher(teacherId) {
  if (!teacherId) return;

  StatusModal.confirm(
    "Delete Teacher",
    "Are you sure you want to delete this teacher?",
    () => submitDeleteTeacher(teacherId),
  );
}

async function submitDeleteTeacher(teacherId) {
  setLoading();
  try {
    const data = await deleteTeacher(teacherId);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      loadTeachers();
      loadCard();
    } else {
      StatusModal.show("Error", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Add course/year handle ──────────────────────────
export function initAddHandleForm() {
  const form = document.getElementById("addHandleForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Add Course and Year Handle",
      "Are you sure you want to add this handle?",
      () => submitAddHandle(formData, form),
    );
  });
}

async function submitAddHandle(formData, form) {
  setLoading();
  try {
    const data = await addHandle(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.data.message, "success");
      form.reset();
      loadTeacherHandles(getCurrentTeacherId());
      loadCard();
    } else {
      StatusModal.show("Error", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete handle (with two-step warning confirmation) ──
export function initDeleteHandleButtons() {
  document.addEventListener("click", (e) => {
    const deleteBtn = e.target.closest(".deleteHandleBtn");
    if (!deleteBtn) return;

    const loadId = deleteBtn.dataset.loadid;
    const teacherId = getCurrentTeacherId();

    requestDeleteHandle({
      teacherId,
      loadId,
    });
  });
}

function requestDeleteHandle({ teacherId, loadId }) {
  StatusModal.confirm(
    "Delete Handle",
    `Are you sure you want to remove this load from this teacher?`,
    () => submitDeleteHandle({ teacherId, loadId }),
  );
}

async function submitDeleteHandle({ teacherId, loadId }) {
  setLoading();
  try {
    const data = await deleteHandle({
      teacherId,
      loadId,
    });

    const message = data?.data?.message;

    if (data.status === "success") {
      StatusModal.show("Success", message, "success");
      loadTeacherHandles(teacherId);
      if (force) loadCard();
    } else {
      StatusModal.show("Error", message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}
