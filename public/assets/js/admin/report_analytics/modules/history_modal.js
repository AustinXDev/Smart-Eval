import { initHistoryTable } from "../../shared/table-config.js";
import { fetchHistory } from "../api/api.js";
import { state, dept } from "./state.js";

export async function populateHistoryModal() {
  try {
    const result = await fetchHistory(dept);

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
