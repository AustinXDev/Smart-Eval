import { closeModal } from "../../../modal/modal.js";
import {
  addQuestionSet,
  editQuestionSet,
  deleteQuestionSet,
  addQuestion,
  editQuestion,
  deleteQuestion,
  activateQuestion,
} from "../api/api.js";
import { loadQuestionSetList, loadQuestions } from "./questionRender.js";
import { getCurrentSetId } from "./questionState.js";
import { setLoading } from "../../shared/Loader.js";

// ── Create question set ──────────────────────────────
export function initCreateSetForm() {
  const form = document.getElementById("createQuestionSetForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const setName = formData.get("set_name") || "this question set";

    StatusModal.confirm(
      "Add Question Set",
      `Are you sure you want to add ${setName}?`,
      () => submitAddSet(formData, form),
    );
  });
}

async function submitAddSet(formData, form) {
  setLoading();
  try {
    const data = await addQuestionSet(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      closeModal("createQuestionSetModal");
      form.reset();
      loadQuestionSetList();
    } else {
      StatusModal.show("Error", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Edit question set ────────────────────────────────
export function initEditSetForm() {
  const form = document.getElementById("editQuestionSetForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Edit Set Name",
      "Are you sure you want to edit this set?",
      () => submitEditSet(formData),
    );
  });
}

async function submitEditSet(formData) {
  setLoading();
  try {
    const data = await editQuestionSet(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      loadQuestionSetList();
      closeModal("editQuestionSetModal");
    } else {
      StatusModal.show("Error", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete question set ──────────────────────────────
export function requestDeleteSet(setId, setName) {
  if (!setId) return;

  StatusModal.confirm(
    "Delete Set",
    `Are you sure you want to delete ${setName} set?`,
    () => submitDeleteSet(setId),
  );
}

async function submitDeleteSet(setId) {
  setLoading();
  try {
    const data = await deleteQuestionSet(setId);

    if (data.status === "success" || data.status === "warning") {
      StatusModal.show(
        data.status === "success" ? "Successfully Deleted" : "Archived",
        data.message,
        "success",
      );
      loadQuestionSetList();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Add question ──────────────────────────────────────
export function initAddQuestionForm() {
  const form = document.getElementById("addQuestionForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Add Question",
      "Are you sure you want to add this question?",
      () => submitAddQuestion(formData, form),
    );
  });
}

async function submitAddQuestion(formData, form) {
  setLoading();
  try {
    const data = await addQuestion(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      loadQuestionSetList();
      loadQuestions(getCurrentSetId());
      form.reset();
    } else if (data.status === "warning") {
      StatusModal.confirm("Activate Question", data.message, () =>
        submitActivateQuestion(data.question_id),
      );
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

async function submitActivateQuestion(questionId) {
  setLoading();
  try {
    const data = await activateQuestion(questionId);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      loadQuestionSetList();
      loadQuestions(getCurrentSetId());
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Edit question ─────────────────────────────────────
export function initEditQuestionForm() {
  const form = document.getElementById("editQuestionForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm("Edit Question", "Are you sure you want to edit?", () =>
      submitEditQuestion(formData, form),
    );
  });
}

async function submitEditQuestion(formData, form) {
  setLoading();
  try {
    const data = await editQuestion(formData);

    if (data.status === "success") {
      StatusModal.show("Success", data.message, "success");
      loadQuestions(getCurrentSetId());
      closeModal("editQuestionModal");
      form.reset();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete question ───────────────────────────────────
export function requestDeleteQuestion(questionId) {
  StatusModal.confirm(
    "Delete Question",
    "Are you sure you want to delete this question?",
    () => submitDeleteQuestion(questionId),
  );
}

async function submitDeleteQuestion(questionId) {
  setLoading();
  try {
    const data = await deleteQuestion(questionId);

    if (data.status === "success" || data.status === "warning") {
      StatusModal.show(
        data.status === "success" ? "Success" : "Warning",
        data.message,
        "success",
      );
      loadQuestions(getCurrentSetId());
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}
