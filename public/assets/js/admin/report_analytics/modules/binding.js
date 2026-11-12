import {
  openModal,
  closeModal,
  showConfirmation,
} from "../../../modal/modal.js";
import { initTableButtonEvents } from "../../shared/table-config.js";
import { dept, periodId, tableInstances } from "./state.js";
import { populateHistoryModal } from "./history_modal.js";

function bindSearchInputs() {
  const searchMap = [
    { inputId: "search-ranking", table: tableInstances.ranking },
  ];

  searchMap.forEach(({ inputId, table }) => {
    const input = document.getElementById(inputId);
    if (!input || !table) return;
    input.addEventListener("input", function () {
      table.search(this.value).draw();
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

      const url = `report/teacher-ranking?dept=${dept}&period_id=${periodId}`;

      showConfirmation({
        title: "Export to Excel",
        message: "Are you sure you want to export teacher ranking to excel?",
        onConfirm: () => {
          window.location.href = url;
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
  bindExportPdf();
  bindExportRankingExcel();
  bindHistoryModal();
  bindReturnToCurrent();
}
