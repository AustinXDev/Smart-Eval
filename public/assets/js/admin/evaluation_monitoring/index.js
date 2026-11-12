import { initMonitoringTable, populateYearFilter, reloadSilently } from "./table.js";
import { renderPeriod, renderSummary } from "./summary.js";
import { initNotifyAll } from "./notify.js";

// Live updates: poll while the tab is visible, pause while hidden,
// and catch up immediately when the admin returns.
const LIVE_INTERVAL_MS = 10_000;

const { department, departmentLabel } = window.monitoringConfig;
const notifyAll = initNotifyAll(department, departmentLabel);
const content = document.getElementById("monitoring-content");
const noPeriod = document.getElementById("no-period-state");
const errorState = document.getElementById("error-state");
const live = document.getElementById("live-indicator");
const lastUpdatedEl = document.getElementById("last-updated");

let loading = false;
let liveTimer = null;
let lastUpdated = null;

const table = initMonitoringTable(department);

// ---------------------------------------------------------------
// Live polling
// ---------------------------------------------------------------

function liveRefresh() {
  if (!loading && document.visibilityState === "visible") reloadSilently(table);
}

function startLive() {
  stopLive();
  liveTimer = setInterval(liveRefresh, LIVE_INTERVAL_MS);
}

function stopLive() {
  clearInterval(liveTimer);
  liveTimer = null;
}

document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "visible") {
    liveRefresh();
    startLive();
  } else {
    stopLive();
  }
});

// ---------------------------------------------------------------
// Live indicator ("Updated 5s ago")
// ---------------------------------------------------------------

function setLiveState(state) {
  const styles = {
    live: ["bg-emerald-400", "bg-emerald-500", "Live", true],
    paused: ["bg-slate-300", "bg-slate-400", "Paused", false],
    offline: ["bg-rose-400", "bg-rose-500", "Reconnecting", true],
  };
  const [ping, dot, label, pulse] = styles[state];
  live.querySelector("[data-live-ping]").className =
    `absolute inline-flex h-full w-full rounded-full opacity-75 ${ping} ${pulse ? "motion-safe:animate-ping" : "hidden"}`;
  live.querySelector("[data-live-dot]").className = `relative inline-flex h-2 w-2 rounded-full ${dot}`;
  live.querySelector("[data-live-label]").textContent = label;
}

function renderLastUpdated() {
  if (!lastUpdated) return;
  const seconds = Math.max(0, Math.round((Date.now() - lastUpdated) / 1000));
  lastUpdatedEl.textContent =
    seconds < 5 ? "just now" : seconds < 60 ? `${seconds}s ago` : `${Math.floor(seconds / 60)}m ago`;
  lastUpdatedEl.title = new Date(lastUpdated).toLocaleTimeString();
}

setInterval(renderLastUpdated, 1000);

// ---------------------------------------------------------------
// Data events from the table
// ---------------------------------------------------------------

function showError(message) {
  document.getElementById("error-message").textContent =
    message || "Participation monitoring is temporarily unavailable. Retrying automatically.";
  errorState.classList.remove("hidden");
  errorState.classList.add("flex");
}

function hideError() {
  errorState.classList.add("hidden");
  errorState.classList.remove("flex");
}

document.addEventListener("monitoring:loading", () => {
  loading = true;
});

document.addEventListener("monitoring:updated", ({ detail }) => {
  loading = false;
  hideError();

  const hasPeriod = Boolean(detail.period);
  renderPeriod(detail.period);
  noPeriod.classList.toggle("hidden", hasPeriod);
  content.classList.toggle("hidden", !hasPeriod);

  renderSummary(detail.summary);
  populateYearFilter(detail.year_levels || []);
  notifyAll.update(detail.period, detail.summary);

  // Stay polling without an active period so the page lights up as soon as one opens.
  setLiveState(document.visibilityState === "visible" ? "live" : "paused");
  lastUpdated = Date.now();
  renderLastUpdated();
});

document.addEventListener("monitoring:error", ({ detail }) => {
  loading = false;
  setLiveState("offline");
  showError(detail);
});

errorState.querySelector("[data-retry]").addEventListener("click", () => {
  if (!loading) table.ajax.reload(null, false);
});

document.addEventListener("visibilitychange", () => {
  if (document.visibilityState !== "visible") setLiveState("paused");
});

startLive();
