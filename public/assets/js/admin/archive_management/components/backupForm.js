import { previewBackup, restoreBackup } from "../api/api.js";
import { byId } from "./ui.js";
import { previewDetails } from "./archiveRender.js";
import { setLoading } from "../../shared/Loader.js";

export function initBackupForm() {
  const form = byId("backup-upload-form");
  const fileInput = byId("backup-file");
  const validateButton = byId("validate-backup");
  const restoreButton = byId("restore-backup");
  const preview = byId("backup-preview");
  const state = byId("upload-state");
  const restoreHelp = byId("restore-unavailable");

  let validatedFile = null;
  let validatedPeriodId = null;
  let busy = false;

  function clearValidation() {
    validatedFile = null;
    validatedPeriodId = null;
    restoreButton.disabled = true;
    restoreHelp.textContent =
      "Validate a backup first. Restoration requires backend approval.";
  }

  function setBusy(value, restoring = false) {
    busy = value;
    fileInput.disabled = value;
    validateButton.disabled = value;
    restoreButton.disabled = value || !validatedFile;
    restoreButton.textContent = restoring ? "Restoring…" : "Restore to Archive";
    form.setAttribute("aria-busy", String(value));
  }

  clearValidation();

  fileInput.addEventListener("change", () => {
    clearValidation();
    preview.textContent = "Validate the selected backup to view its contents.";
    state.textContent = "";
  });

  form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (busy) return;

    clearValidation();
    preview.textContent = "No validated backup preview.";

    const file = fileInput.files[0];

    if (
      !file ||
      !/\.json\.enc$/i.test(file.name) ||
      file.size <= 36 ||
      file.size > 2 * 1024 * 1024
    ) {
      state.textContent = "Choose a .json.enc backup no larger than 2 MiB.";
      return;
    }

    setBusy(true);
    preview.setAttribute("aria-busy", "true");
    state.textContent = "Uploading, decrypting, and validating the backup…";

    try {
      const body = new FormData();
      body.append("backup", file);

      const { data } = await previewBackup(body);

      // Discard a response if the selection changed.
      if (fileInput.files[0] !== file) return;

      const periodId = Number(data.period_id);

      if (!Number.isSafeInteger(periodId) || periodId <= 0) {
        throw new Error("The preview returned an invalid period ID.");
      }

      preview.innerHTML = previewDetails(data);

      const eligible =
        data.restore_enabled === true && data.existing_period === false;

      if (eligible) {
        validatedFile = file;
        validatedPeriodId = periodId;
        restoreHelp.textContent = `Period #${periodId} is eligible for restoration.`;
        state.textContent =
          "Validation passed. Review the preview before restoring.";
      } else {
        restoreHelp.textContent =
          data.notice || "This backup is not eligible for restoration.";
        state.textContent = "Validation passed. Restoration is unavailable.";
      }
    } catch (error) {
      clearValidation();
      state.textContent = error.message || "Validation failed.";
    } finally {
      preview.setAttribute("aria-busy", "false");
      setBusy(false);
    }
  });

  restoreButton.addEventListener("click", () => {
    if (busy || !validatedFile || !validatedPeriodId) return;

    const file = validatedFile;
    const periodId = validatedPeriodId;

    StatusModal.confirm(
      "Restore Archive",
      `Restore period #${periodId} into the archive database?`,
      async () => {
        // Recheck because the selection may change while confirmation is open.
        if (busy) return;

        if (
          validatedFile !== file ||
          validatedPeriodId !== periodId ||
          fileInput.files[0] !== file
        ) {
          clearValidation();
          state.textContent = "The selection changed. Validate it again.";
          return;
        }

        setBusy(true, true);
        setLoading("Restoring the backup. Please wait…");

        try {
          const body = new FormData();
          body.append("backup_file", file);
          body.append("period_id", String(periodId));

          const result = await restoreBackup(body);

          fileInput.value = "";

          preview.textContent =
            "Upload a backup to inspect its evaluation period and record counts.";
          preview.setAttribute("aria-busy", "false");
          state.textContent = "";
          clearValidation();

          StatusModal.show(
            `${result.status === "success" ? "Success" : "Failed"}`,
            `${result.message}`,
            `${result.status}`,
          );

          byId("refresh-archive")?.click();
        } catch (error) {
          state.textContent =
            `${error.message || "Restoration failed."} ` +
            "Refresh the archive and validate again before retrying.";
        } finally {
          clearValidation();
          setBusy(false);
          setLoading("", false);
        }
      },
    );
  });
}
