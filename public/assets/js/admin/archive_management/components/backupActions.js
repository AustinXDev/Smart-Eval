import { verifyBackup } from "../api/api.js";
import { byId, announce } from "./ui.js";
import { initArchiveActionMenu } from "./archiveActionMenu.js";
import { deleteArchive } from "../api/api.js";
import { setLoading } from "../../shared/Loader.js";

const verifying = new Set();
const deleting = new Set();

export function initBackupActions() {
  const actionMenu = initArchiveActionMenu();
  document.addEventListener("click", async (event) => {
    const verify = event.target.closest("[data-verify-period]");

    if (!verify || verify.disabled) return;

    const id = verify.dataset.verifyPeriod;

    if (verifying.has(id)) return;

    actionMenu.dismissFor(verify);
    verifying.add(id);
    verify.disabled = true;
    verify.textContent = "Verifying…";

    announce("Downloading the encrypted backup to verify its checksum…");

    try {
      const { data } = await verifyBackup(id);

      if (data.valid !== true || data.checksum_valid !== true)
        throw new Error("Backup integrity was not confirmed.");
      announce(
        `Period #${id}: encrypted backup checksum verified. This does not test decryption or restoration.`,
      );
    } catch (error) {
      announce(error.message, true);
    } finally {
      verifying.delete(id);
      verify.disabled = false;
      verify.textContent = "Verify Backup";
    }
  });

  document.addEventListener("click", (event) => {
    const del = event.target.closest("[data-delete-period]");

    if (!del || del.disabled) return;

    const periodId = Number(del.dataset.deletePeriod);

    if (!Number.isSafeInteger(periodId) || periodId <= 0) {
      announce("Invalid evaluation period.", true);
      return;
    }

    if (deleting.has(periodId)) return;

    actionMenu.dismissFor(del);

    StatusModal.confirm(
      "Delete Archived Records",
      `Are you sure you want to delete the archived ` +
        `records for Period #${periodId}? ` +
        `The verified encrypted backup and recovery ` +
        `metadata will be retained.`,
      async () => {
        if (deleting.has(periodId)) return;

        deleting.add(periodId);

        const buttons = document.querySelectorAll(
          `[data-delete-period="${periodId}"]`,
        );

        buttons.forEach((button) => {
          button.disabled = true;
          button.dataset.originalText = button.textContent;
          button.textContent = "Deleting…";
        });

        setLoading(`Deleting archived records for Period #${periodId}…`);

        try {
          const { data, message } = await deleteArchive(periodId);

          if (data.archive_data_status !== "deleted") {
            throw new Error("The archive deletion was not confirmed.");
          }

          StatusModal.show(
            "Success",
            `${message || "Period #${periodId} deleted successfully."}`,
            data.status,
          );

          // Refresh archive dashboard and table.
          byId("refresh-archive")?.click();
        } catch (error) {
          StatusModal.show("Failed", error.message, "error");
        } finally {
          setLoading("", false);
          deleting.delete(periodId);

          buttons.forEach((button) => {
            if (!button.isConnected) return;

            button.disabled = false;
            button.textContent =
              button.dataset.originalText || "Delete Archive";

            delete button.dataset.originalText;
          });
        }
      },
    );
  });
}
