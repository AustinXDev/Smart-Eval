import {
  openModal,
  closeModal,
  showConfirmation,
} from "../../../modal/modal.js";

import { uploadCsv } from "../api/api.js";
import { loadStudents, loadStudentCard } from "../table.js";

export function initCsvUpload(department) {
  const csvForm = document.getElementById("uploadCsvForm");
  const csvInput = document.getElementById("csvInput");
  const csvSummaryContent = document.getElementById("csvSummaryContent");
  const fileName = document.getElementById("fileName");
  const dropZone = document.getElementById("dropZone");
  const progressFill = document.getElementById("progressFill");
  const submitBtn = csvForm?.querySelector('[type="submit"]');

  if (!csvForm || !csvInput || !dropZone) return;

  const elements = {
    csvForm,
    csvInput,
    csvSummaryContent,
    fileName,
    dropZone,
    progressFill,
    submitBtn,
  };

  // ── File selection ──────────────────────────────
  csvInput.addEventListener("change", () => {
    showSelectedFile(csvInput, fileName);
  });

  // ── Drag & Drop ─────────────────────────────────
  dropZone.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropZone.classList.add("border-purple-500");
  });

  dropZone.addEventListener("dragleave", () => {
    dropZone.classList.remove("border-purple-500");
  });

  dropZone.addEventListener("drop", (e) => {
    e.preventDefault();

    dropZone.classList.remove("border-purple-500");

    csvInput.files = e.dataTransfer.files;

    showSelectedFile(csvInput, fileName);
  });

  // ── Submit ──────────────────────────────────────
  csvForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const file = csvInput.files[0];

    if (!file) {
      alert("Please select a CSV file.");
      return;
    }

    const formData = new FormData();

    formData.append("csv", file);
    formData.append("department", department);

    showConfirmation({
      title: "Upload CSV",
      message: "Are you sure you want to upload this CSV file?",

      onConfirm: () => submitCsv(formData, elements),
    });
  });
}

// ── Show selected file ─────────────────────────────
function showSelectedFile(input, fileNameEl) {
  if (!fileNameEl) return;

  if (input.files.length > 0) {
    fileNameEl.textContent = "Selected: " + input.files[0].name;

    fileNameEl.classList.remove("hidden");
  }
}

// ── Uploading state ────────────────────────────────
function setUploadingState(elements, isUploading) {
  const { csvInput, dropZone, submitBtn } = elements;

  csvInput.disabled = isUploading;

  dropZone.classList.toggle("pointer-events-none", isUploading);

  dropZone.classList.toggle("opacity-60", isUploading);

  if (submitBtn) {
    submitBtn.disabled = isUploading;

    if (isUploading) {
      submitBtn.dataset.originalText = submitBtn.textContent;

      submitBtn.innerHTML = `
        <span
          class="inline-block w-3.5 h-3.5 border-2
          border-white/40 border-t-white rounded-full
          animate-spin mr-2"
        ></span>
        Uploading…
      `;
    } else if (submitBtn.dataset.originalText) {
      submitBtn.textContent = submitBtn.dataset.originalText;

      delete submitBtn.dataset.originalText;
    }
  }
}

// ── Reset CSV form ─────────────────────────────────
function resetCsvForm(elements) {
  const { csvForm, csvInput, fileName, dropZone, progressFill } = elements;

  csvForm.reset();

  csvInput.value = "";

  if (fileName) {
    fileName.classList.add("hidden");
  }

  dropZone.classList.remove("border-purple-500");

  if (progressFill) {
    updateProgress(progressFill, 0);
  }
}

// ── Submit CSV ─────────────────────────────────────
async function submitCsv(formData, elements) {
  const { csvSummaryContent, progressFill } = elements;

  openModal("uploadLoading");

  setUploadingState(elements, true);

  if (progressFill) {
    updateProgress(progressFill, 0);
  }

  const startTime = Date.now();

  try {
    const data = await uploadCsv(formData);

    const elapsed = Date.now() - startTime;

    const delay = Math.max(1200 - elapsed, 0);

    csvSummaryContent.innerHTML = buildCsvSummaryHtml(data);

    closeModal("uploadCSVModal");

    resetCsvForm(elements);

    setTimeout(() => {
      closeModal("uploadLoading");

      openModal("csvSummaryModal");
    }, delay);

    if (data.success > 0) {
      loadStudents();
      loadStudentCard();
    }
  } catch (error) {
    console.error("CSV upload error:", error);

    closeModal("uploadLoading");

    alert(error.message || "Upload failed. Please try again.");
  } finally {
    setUploadingState(elements, false);
  }
}

// ── CSV Summary ────────────────────────────────────
function buildCsvSummaryHtml(data) {
  let html = `
    <p>
      <strong>Total Records:</strong>
      ${data.total}
    </p>

    <p>
      <strong>Successfully Imported:</strong>
      ${data.success}
    </p>

    <p>
      <strong>Failed:</strong>
      ${data.failed}
    </p>
  `;

  if (data.errors && data.errors.length > 0) {
    html += `
      <ul class="ml-4 list-disc text-red-600">
    `;

    data.errors.forEach((error) => {
      html += `<li>${error}</li>`;
    });

    html += "</ul>";
  }

  return html;
}
