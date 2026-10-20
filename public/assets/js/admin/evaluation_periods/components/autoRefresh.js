import { loadDashboard } from "../table.js";

let isUpdating = false;
let refreshTimer = null;

async function updatePeriodAndReload(refreshTable = true) {
  if (isUpdating) {
    return;
  }

  isUpdating = true;

  try {
    await loadDashboard({ refreshTable, resetPaging: false });
  } catch (err) {
    console.error("Error updating periods:", err);
  } finally {
    isUpdating = false;
  }
}

export function initAutoRefresh(intervalMs = 60000, refreshTableImmediately = true) {
  // Prevent multiple intervals
  if (refreshTimer !== null) {
    return;
  }

  // Run once immediately
  updatePeriodAndReload(refreshTableImmediately);

  // Start ONE interval
  refreshTimer = setInterval(() => {
    updatePeriodAndReload();
  }, intervalMs);
}

export function stopAutoRefresh() {
  if (refreshTimer !== null) {
    clearInterval(refreshTimer);
    refreshTimer = null;
  }
}
