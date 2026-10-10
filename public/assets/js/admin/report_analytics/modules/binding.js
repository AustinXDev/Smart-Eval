import {
  openModal,
  closeModal,
  showConfirmation,
} from "../../../modal/modal.js";
import { initTableButtonEvents } from "../../shared/table-config.js";
import { state } from "./state.js";
import { dept, periodId, tableInstances } from "./state.js";
import { refreshNotEvaluatedTable } from "./render/table.js";
import { populateHistoryModal } from "./history_modal.js";
import { setLoading } from "../../shared/Loader.js";
import { notifyAll } from "../api/api.js";

function bindSearchInputs() {
  const searchMap = [
    { inputId: "search-ranking", table: tableInstances.ranking },
    { inputId: "search-not-evaluated", table: tableInstances.not_evaluated },
    { inputId: "search-abandoned", table: tableInstances.abandoned },
  ];

  searchMap.forEach(({ inputId, table }) => {
    const input = document.getElementById(inputId);
    if (!input || !table) return;
    input.addEventListener("input", function () {
      table.search(this.value).draw();
    });
  });
}

function bindTabButtons() {
  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      const target = btn.dataset.target;

      document.querySelectorAll(".tab-panel").forEach((panel) => {
        panel.classList.toggle("hidden", panel.id !== target);
      });

      const tableMap = {
        "panel-ranking": tableInstances.ranking,
        "panel-not-evaluated": tableInstances.not_evaluated,
        "panel-abandoned": tableInstances.abandoned,
      };

      setTimeout(() => {
        tableMap[target]?.columns.adjust().draw(false);
      }, 100);
    });
  });
}

function bindExportPdf() {
  document.getElementById("exportPdfBtn").addEventListener("click", (e) => {
    e.preventDefault();

    if (!dept) {
      alert("Please select a department first.");
      return;
    }

    const url = `report/evaluation-summary&dept=${dept}&period_id=${periodId || ""}`;

    showConfirmation({
      title: "Export to PDF",
      message: "Are you sure you want to export to PDF?",
      onConfirm: () => window.open(url, "_blank"),
    });
  });
}

function bindExportRankingExcel() {
  document
    .getElementById("btn-export-ranking")
    .addEventListener("click", (e) => {
      e.preventDefault();

      if (!dept) {
        alert("Missing department parameter. Please try Again.");
        return;
      }

      const url = `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?action=exportExcel&dept=${dept}&period_id=${periodId || ""}`;

      showConfirmation({
        title: "Export to Excel",
        message: "Are you sure you want to export teacher ranking to excel?",
        onConfirm: () => {
          window.location.href = url;
        },
      });
    });
}

function bindNotifyAll() {
  const notifyButton = document.getElementById("btn-notify-all");

  // Prevent duplicate event listeners when polling refreshes analytics.
  if (notifyButton.dataset.bound === "true") {
    updateNotifyAllButtonState();
    return;
  }

  notifyButton.dataset.bound = "true";

  notifyButton.addEventListener("click", (e) => {
    e.preventDefault();

    if (!dept) {
      alert("Missing department parameter. Please try Again.");
      return;
    }

    StatusModal.confirm(
      "Notify All Non-participants",
      `Are you sure you want to notify all non-participants for ${dept}?`,
      async () => {
        setLoading();

        try {
          const response = await notifyAll(dept);

          if (response.code === 405) {
            window.location.href = `${window.BASE_URL}admin-login`;
            throw new Error("Your session has expired. Please log in again.");
          }

          if (response.status === "error") {
            StatusModal.show(
              "Failed",
              response.message || "Failed to queue reminders.",
              "error",
            );
            return;
          }

          refreshNotEvaluatedTable();

          StatusModal.show(
            "Success",
            response.message || "Notification reminders have been queued.",
            "success",
          );
        } catch (error) {
          console.error("Failed to queue reminders:", error);

          StatusModal.show(
            "Failed",
            error.message || "Unable to process notification reminders.",
            "error",
          );
        } finally {
          setLoading("", false);
        }
      },
    );
  });
}

function bindHistoryModal() {
  document.getElementById("viewHistoryBtn").addEventListener("click", () => {
    openModal("viewHistoryModal");
    document.getElementById("closeModal").addEventListener("click", () => {
      closeModal("viewHistoryModal");
    });
    setTimeout(() => populateHistoryModal(), 200);
  });
}

function bindReturnToCurrent() {
  document
    .getElementById("btn-return-current")
    .addEventListener("click", () => {
      const url = new URL(window.location.href);
      url.searchParams.delete("period_id");
      window.location.href = url.toString();
    });
}

export function initDashboardBindings() {
  initTableButtonEvents(dept, periodId);
  bindSearchInputs();
  bindTabButtons();
  bindExportPdf();
  bindExportRankingExcel();
  bindNotifyAll();
  bindHistoryModal();
  bindReturnToCurrent();
  //updateNotifyButtonState();
}

/**
 * Helpers
 */
export function updateNotifyAllButtonState() {
  const notifyButton = document.getElementById("btn-notify-all");
  if (!notifyButton) return;

  const lastData = [
    ...(state.lastData?.not_evaluated ?? []),
    ...(state.lastData?.abandoned ?? []),
  ];

  // Enable only when at least one student has not been queued.
  const canNotify = lastData.some(
    ({ notification_status }) => notification_status == null,
  );

  notifyButton.disabled = !canNotify;
  notifyButton.classList.toggle("opacity-50", !canNotify);
  notifyButton.classList.toggle("cursor-not-allowed", !canNotify);
}
