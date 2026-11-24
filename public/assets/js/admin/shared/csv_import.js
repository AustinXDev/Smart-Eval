import { openModal, closeModal } from "../../modal/modal.js";

/**
 * Shared CSV import (Teachers, Students).
 * Modals: views/partials/modals/csv_import_modal.php. StatusModal is global (common/modal.js).
 *
 * initCsvImport({
 *   department,            sent with the file
 *   headers,               required first row, e.g. ["employee_id", "full_name", "email"]
 *   noun,                  "teachers" / "students"
 *   idField,               key of the ID in each error row, e.g. "employee_id"
 *   upload(formData),      POST; must throw (error.message) when the whole file is rejected
 *   onImported(result),    refresh the page after at least one row was saved
 *   extraText(result),     optional line under the totals
 * })
 *
 * Server response: { total, success, failed, message, errors: [{ row, <idField>, full_name, message }] }
 */

const MAX_BYTES = 2 * 1024 * 1024;
const $ = (id) => document.getElementById(id);

export function initCsvImport(options) {
  const form = $("csvImportForm");
  const input = $("csvImportInput");
  const drop = $("csvImportDrop");
  if (!form || !input || !drop) return;

  const config = { idField: "id", noun: "records", ...options };
  let lastErrors = [];

  input.addEventListener("change", () => selectFile(input.files[0]));
  $("csvImportClear")?.addEventListener("click", clearFile);

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
      `Import ${capitalize(config.noun)}`,
      `Import ${config.noun} from "${file.name}"? Rows with duplicate or invalid details will be skipped.`,
      () => upload(file),
    );
  });

  $("csvImportErrorsDownload")?.addEventListener("click", downloadErrors);

  // ── File selection ───────────────────────────────
  async function selectFile(file) {
    showFile(file ?? null);
    showError(file ? await checkFile(file) : "");
  }

  function clearFile() {
    input.value = "";
    showFile(null);
    showError("");
  }

  function showFile(file) {
    $("csvImportFile").hidden = !file;
    $("csvImportFileName").textContent = file
      ? `${file.name} · ${formatSize(file.size)}`
      : "";
  }

  function showError(message) {
    const el = $("csvImportError");
    el.textContent = message || "";
    el.hidden = !message;
    drop.classList.toggle("border-rose-300", Boolean(message));
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
      if (columns.join(",") !== config.headers.join(",")) {
        return `The first row must be: ${config.headers.join(", ")}. Download the template to start.`;
      }
    } catch {
      /* unreadable here; let the server decide */
    }
    return "";
  }

  // ── Upload ───────────────────────────────────────
  async function upload(file) {
    const formData = new FormData();
    formData.append("csv", file);
    formData.append("department", config.department);

    setBusy(true);
    openModal("csvImportLoading");
    const started = Date.now();

    try {
      const result = await config.upload(formData);
      // Keep the progress modal up briefly so it doesn't flash.
      await new Promise((r) => setTimeout(r, Math.max(0, 700 - (Date.now() - started))));

      closeModal("csvImportLoading");
      closeModal("csvImportModal");
      renderSummary(result, file.name);
      openModal("csvImportSummary");

      if (result.success > 0) config.onImported?.(result);
    } catch (error) {
      closeModal("csvImportLoading");
      showError(error.message || "Import failed. Please try again.");
    } finally {
      setBusy(false);
    }
  }

  function setBusy(busy) {
    const button = $("csvImportSubmit");
    button.disabled = busy;
    button.querySelector("span").textContent = busy
      ? "Importing…"
      : `Import ${capitalize(config.noun)}`;
    input.disabled = busy;
  }

  // ── Summary ──────────────────────────────────────
  function renderSummary(result, fileName) {
    const errors = Array.isArray(result.errors) ? result.errors : [];
    lastErrors = errors;

    $("csvImportSummaryText").textContent = result.message || fileName;
    $("csvImportTotal").textContent = result.total ?? 0;
    $("csvImportSuccess").textContent = result.success ?? 0;
    $("csvImportFailed").textContent = result.failed ?? errors.length;

    const extra = config.extraText?.(result) || "";
    $("csvImportExtra").hidden = !extra;
    $("csvImportExtra").textContent = extra;

    $("csvImportErrors").hidden = errors.length === 0;
    $("csvImportErrorRows").replaceChildren(
      ...errors.map((err) => {
        const tr = document.createElement("tr");
        [
          [err.row, "whitespace-nowrap tabular-nums text-slate-500"],
          [err[config.idField] || "—", "whitespace-nowrap font-medium text-slate-800"],
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
      ["row", config.idField, "full_name", "reason"],
      ...lastErrors.map((e) => [e.row, e[config.idField], e.full_name, e.message]),
    ].map((row) => row.map(cell).join(","));

    const url = URL.createObjectURL(
      new Blob(["﻿" + lines.join("\r\n")], { type: "text/csv;charset=utf-8" }),
    );
    const a = Object.assign(document.createElement("a"), {
      href: url,
      download: `${config.noun}_import_errors.csv`,
    });
    document.body.appendChild(a);
    a.click();
    a.remove();
    URL.revokeObjectURL(url);
  }
}

function capitalize(text) {
  return text.charAt(0).toUpperCase() + text.slice(1);
}

function formatSize(bytes) {
  return bytes < 1024 ? `${bytes} B` : `${(bytes / 1024).toFixed(1)} KB`;
}
