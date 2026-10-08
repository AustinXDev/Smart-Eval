import {
  openModal,
  closeModal,
  showConfirmation,
} from "../../../modal/modal.js";
import { initTableButtonEvents } from "../../shared/table-config.js";
import { dept, periodId, tableInstances } from "./state.js";
import {
  processNotificationBatches,
  updateNotifyButtonState,
} from "./notification.js";
import { populateHistoryModal } from "./history_modal.js";

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

  notifyButton.addEventListener("click", (e) => {
    e.preventDefault();

    if (!dept) {
      alert("Missing department parameter. Please try Again.");
      return;
    }

    showConfirmation({
      title: "Notify All Non-participants",
      message: `Are you sure you want to notify all non-participants for ${dept}?`,
      onConfirm: async () => {
        try {
          const prepareUrl = `/Smart-Eval/app/Controllers/notification/NotificationController.php?action=prepare&dept=${dept}`;
          const prepResponse = await fetch(prepareUrl);
          const prepData = await prepResponse.json();

          if (prepData.status === "success") {
            await processNotificationBatches(dept);
          } else {
            alert(prepData.message);
          }
        } catch (err) {
          console.error("Initialization Error:", err);
          alert("An error occurred while initializing notifications.");
        }
      },
    });
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
  updateNotifyButtonState();
}
