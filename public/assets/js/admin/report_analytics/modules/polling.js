import { fetchAnalytics } from "../api/api.js";
import { dept } from "./state.js";

const POLL_INTERVAL = 60000;

let pollTimer = null;
let isVisible = true;

export function startLivePolling() {
  stopPolling();
  pollTimer = setInterval(async () => {
    if (isVisible) {
      const currentPeriodId = new URLSearchParams(window.location.search).get(
        "period_id",
      );
      await fetchAnalytics(dept, currentPeriodId);
      console.log("refreshed");
    }
  }, POLL_INTERVAL);
}

export function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

export function initVisibilityHandling() {
  document.addEventListener("visibilitychange", () => {
    isVisible = !document.hidden;
    if (isVisible) {
      fetchAnalytics(
        dept,
        new URLSearchParams(window.location.search).get("period_id"),
      );
      startLivePolling();
    } else {
      stopPolling();
    }
  });
}
