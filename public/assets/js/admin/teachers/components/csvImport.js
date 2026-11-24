import { openModal, closeModal } from "../../../modal/modal.js";
import { importTeachersCsv } from "../api/api.js";
import { loadTeachers, loadCard } from "../table.js";

/**
 * Import teachers from CSV (employee_id, full_name, email).
 * Modals: views/partials/modals/teachers_modal/import_csv_modal.php
 * StatusModal is loaded globally (common/modal.js).
 */

const MAX_BYTES = 2 * 1024 * 1024;
const HEADERS = ["employee_id", "full_name", "email"];

const $ = (id) => document.getElementById(id);

export function initCsvImport(department) {
  const form = $("teacherCsvForm");
  const input = $("teacherCsvInput");
  const drop = $("teacherCsvDrop");
  if (!form || !input || !drop) return;

  input.addEventListener("change", () => selectFile(input.files[0]));
  $("teacherCsvClear")?.addEventListener("click", () => clearFile());

  // closeModal() resets the form; also clear the file label and error.
  form.addEventListener("reset", () => {
    showFile(null);
    showError("");
  });

  // ── Drag & drop ─────────────────────────────────
  ["dragenter", "dragover"].forEach((type) =>
    drop.addEventListener(type, (e) => {
      e.preventDefault();
      drop.classList.add("border-violet-500", "bg-violet-50/50");
    }),
  );
  ["dragleave", "drop"].forEach((type) =>
    drop.addEventListener(type, (e) => {
      e.preventDefault();
      drop.classList.remove("border-violet-500", "bg-violet-50/50");
    }),
  );
  drop.addEventListener("drop", (e) => {
    const file = e.dataTransfer?.files?.[0];
    if (!file) return;
    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
    selectFile(file);
  });

  // ── Submit ──────────────────────────────────────
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const file = input.files[0];
    const problem = await checkFile(file);
    if (problem) return showError(problem);

    StatusModal.confirm(
      "Import Teachers",
      `Import teachers from "${file.name}"? Rows with duplicate Employee ID, email or name will be skipped.`,
      () => upload(file, department),
    );
  });

  $("teacherCsvErrorsDownload")?.addEventListener("click", downloadErrors);
}

// ── File selection ───────────────────────────────
async function selectFile(file) {
  showFile(file ?? null);
  showError(file ? await checkFile(file) : "");
}

function clearFile() {
  $("teacherCsvInput").value = "";
  showFile(null);
  showError("");
}

function showFile(file) {
  const box = $("teacherCsvFile");
  box.hidden = !file;
  $("teacherCsvFileName").textContent = file
    ? `${file.name} · ${formatSize(file.size)}`
    : "";
}

function showError(message) {
  const el = $("teacherCsvError");
  el.textContent = message || "";
  el.hidden = !message;
  $("teacherCsvDrop").classList.toggle("border-rose-300", Boolean(message));
}

/** Quick checks before uploading; the server checks everything again. */
async function checkFile(file) {
  if (!file) return "Choose a CSV file to import.";
  if (!/\.csv$/i.test(file.name)) return "Only .csv files are accepted.";
  if (file.size === 0) return "The file is empty.";
  if (file.size > MAX_BYTES) return "The file must be smaller than 2 MB.";

  try {
    const head = await file.slice(0, 1024).text();
    const columns = head
      .replace(/^﻿/, "")
      .split(/\r?\n/)[0]
      .split(",")
      .map((h) => h.trim().replace(/^"|"$/g, "").toLowerCase());
    while (columns.length && columns[columns.length - 1] === "") columns.pop();
    if (columns.join(",") !== HEADERS.join(",")) {
      return `The first row must be: ${HEADERS.join(", ")}. Download the template to start.`;
    }
  } catch {
    /* unreadable here; let the server decide */
  }
  return "";
}

// ── Upload ───────────────────────────────────────
async function upload(file, department) {
  const formData = new FormData();
  formData.append("csv", file);
  formData.append("department", department);

  setBusy(true);
  openModal("teacherCsvLoading");
  const started = Date.now();

  try {
    const result = await importTeachersCsv(formData);
    // Keep the progress modal up briefly so it doesn't flash.
    await new Promise((r) => setTimeout(r, Math.max(0, 700 - (Date.now() - started))));

    closeModal("teacherCsvLoading");
    closeModal("teacherCsvModal");
    renderSummary(result, file.name);
    openModal("teacherCsvSummary");

    if (result.success > 0) {
      loadTeachers();
      loadCard();
    }
  } catch (error) {
    closeModal("teacherCsvLoading");
    showError(error.message || "Import failed. Please try again.");
  } finally {
    setBusy(false);
  }
}

function setBusy(busy) {
  const button = $("teacherCsvSubmit");
  button.disabled = busy;
  button.querySelector("span").textContent = busy ? "Importing…" : "Import Teachers";
  $("teacherCsvInput").disabled = busy;
}

// ── Summary ──────────────────────────────────────
let lastErrors = [];

function renderSummary(result, fileName) {
  const errors = Array.isArray(result.errors) ? result.errors : [];
  lastErrors = errors;

  $("teacherCsvSummaryFile").textContent = result.message || fileName;
  $("teacherCsvTotal").textContent = result.total ?? 0;
  $("teacherCsvSuccess").textContent = result.success ?? 0;
  $("teacherCsvFailed").textContent = result.failed ?? errors.length;

  const reactivated = Number(result.reactivated) || 0;
  const note = $("teacherCsvReactivated");
  note.hidden = reactivated === 0;
  note.textContent = reactivated
    ? `${reactivated} of the imported ${reactivated === 1 ? "teacher was a deactivated record" : "teachers were deactivated records"} and ${reactivated === 1 ? "has" : "have"} been reactivated.`
    : "";

  $("teacherCsvErrors").hidden = errors.length === 0;
  const body = $("teacherCsvErrorRows");
  body.replaceChildren(
    ...errors.map((err) => {
      const tr = document.createElement("tr");
      [
        [err.row, "whitespace-nowrap tabular-nums text-slate-500"],
        [err.employee_id || "—", "whitespace-nowrap font-medium text-slate-800"],
        [err.full_name || "—", "text-slate-700"],
        [err.message, "text-rose-700"],
      ].forEach(([text, cls]) => {
        const td = document.createElement("td");
        td.className = `px-3 py-2 align-top ${cls}`;
        td.textContent = String(text ?? ""); // CSV values are never rendered as HTML
        tr.appendChild(td);
      });
      return tr;
    }),
  );
}

function downloadErrors() {
  if (!lastErrors.length) return;
  const cell = (v) => {
    let s = String(v ?? "");
    if (/^[=+\-@]/.test(s)) s = `'${s}`; // keep spreadsheet apps from running it as a formula
    return /[",\r\n]/.test(s) ? `"${s.replace(/"/g, '""')}"` : s;
  };
  const lines = [
    ["row", "employee_id", "full_name", "reason"],
    ...lastErrors.map((e) => [e.row, e.employee_id, e.full_name, e.message]),
  ].map((row) => row.map(cell).join(","));

  const url = URL.createObjectURL(
    new Blob(["﻿" + lines.join("\r\n")], { type: "text/csv;charset=utf-8" }),
  );
  const a = Object.assign(document.createElement("a"), {
    href: url,
    download: "teacher_import_errors.csv",
  });
  document.body.appendChild(a);
  a.click();
  a.remove();
  URL.revokeObjectURL(url);
}

function formatSize(bytes) {
  return bytes < 1024 ? `${bytes} B` : `${(bytes / 1024).toFixed(1)} KB`;
}
