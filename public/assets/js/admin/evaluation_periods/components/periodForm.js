import { closeModal } from "../../../modal/modal.js";
import { loadDashboard } from "../table.js";
import {
  forceActivePeriod,
  deletePeriod,
  forceClosePeriod,
  createPeriod,
  updatePeriod,
} from "../api/api.js";
import { setLoading } from "../../shared/Loader.js";

// ── Force active ──────────────────────────────────────
export function requestForceActive(periodId) {
  StatusModal.confirm(
    "Force Active Evaluation",
    "Are you sure you want to active this evaluation?",
    () => submitForceActive(periodId),
  );
}

async function submitForceActive(periodId) {
  setLoading();
  try {
    const data = await forceActivePeriod(periodId);

    if (data.status === "success") {
      StatusModal.show("Force-Activated", data.message, "success");

      await loadDashboard();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Delete ────────────────────────────────────────────
export function requestDeletePeriod(periodId) {
  StatusModal.confirm(
    "Delete Period",
    "Are you sure you want to delete this period?",
    () => submitDeletePeriod(periodId),
  );
}

async function submitDeletePeriod(periodId) {
  setLoading();
  try {
    const data = await deletePeriod(periodId);

    if (data.status === "success") {
      StatusModal.show("Deleted", data.message, "success");
      await loadDashboard();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Force close ───────────────────────────────────────
export function requestForceClose(periodId) {
  StatusModal.confirm(
    "Force Close Period",
    "Are you sure you want to close this period?",
    () => submitForceClose(periodId),
  );
}

async function submitForceClose(periodId) {
  setLoading();
  try {
    const data = await forceClosePeriod(periodId);

    if (data.status === "success") {
      StatusModal.show("Force-Closed", data.message, "success");
      await loadDashboard();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error(err);
  } finally {
    setLoading("", false);
  }
}

// ── Create period ─────────────────────────────────────
export function initCreatePeriodForm() {
  const form = document.getElementById("createPeriodForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Confirm Creation",
      "Are you sure you want to create this evaluation period?",
      () => submitCreatePeriod(formData, form),
    );
  });
}

async function submitCreatePeriod(formData, form) {
  setLoading();
  try {
    const data = await createPeriod(formData);

    if (data.status === "success") {
      StatusModal.show("Added", data.message, "success");
      await loadDashboard();
      closeModal("createPeriodModal");
      form.reset();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error("Error while creating evaluation period", err);
  } finally {
    setLoading("", false);
  }
}

// ── Update period ─────────────────────────────────────
export function initUpdatePeriodForm() {
  const form = document.getElementById("updatePeriodForm");
  if (!form) return;

  form.addEventListener("submit", (e) => {
    e.preventDefault();
    const formData = new FormData(form);

    StatusModal.confirm(
      "Update Evaluation Period",
      "Are you sure you want to update?",
      () => submitUpdatePeriod(formData, form),
    );
  });
}

async function submitUpdatePeriod(formData, form) {
  setLoading();
  try {
    const data = await updatePeriod(formData);

    if (data.status === "success") {
      StatusModal.show("Updated", data.message, "success");
      await loadDashboard();
      closeModal("updatePeriodModal");
      form.reset();
    } else {
      StatusModal.show("Failed", data.message, "error");
    }
  } catch (err) {
    console.error("Error while updating evaluation period", err);
  } finally {
    setLoading("", false);
  }
}
