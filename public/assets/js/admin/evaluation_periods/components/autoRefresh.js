import { loadDashboard } from "../table.js";

let isUpdating = false;
let refreshTimer = null;

async function updatePeriodAndReload() {
  if (isUpdating) {
    return;
  }

  isUpdating = true;

  try {
    await loadDashboard();
  } catch (err) {
    console.error("Error updating periods:", err);
  } finally {
    isUpdating = false;
  }
}

export function initAutoRefresh(intervalMs = 60000) {
  // Prevent multiple intervals
  if (refreshTimer !== null) {
    return;
  }

  // Run once immediately
  updatePeriodAndReload();

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
