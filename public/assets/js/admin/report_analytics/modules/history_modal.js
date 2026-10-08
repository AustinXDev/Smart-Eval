import { initHistoryTable } from "../../shared/table-config.js";
import { state, dept } from "./state.js";

export async function populateHistoryModal() {
  try {
    const response = await fetch(
      `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?action=getHistoryList&dept=${dept}`,
    );
    const result = await response.json();
    console.log("History result:", result);

    if (result.status === "success") {
      if ($.fn.DataTable.isDataTable("#tbl-history")) {
        $("#tbl-history").DataTable().destroy();
      }

      state.historyTable = initHistoryTable();
      state.historyTable.clear().rows.add(result.data).draw();

      setTimeout(() => {
        state.historyTable.columns.adjust().draw(false);
      }, 100);
    }
  } catch (error) {
    console.error("History data error:", error);
  }
}
