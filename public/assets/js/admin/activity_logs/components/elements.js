// DOM lookups for the Activity Logs page (looked up lazily, never cached stale).
export const el = {
  table: () => document.getElementById("activity-log-table"),
  tabs: () => document.querySelectorAll("[data-log-tab]"),
  tabCounts: () => document.querySelectorAll("[data-log-count]"),
  panel: () => document.getElementById("logs-panel"),
  search: () => document.getElementById("log-search"),
  clearSearch: () => document.getElementById("clear-search"),
  module: () => document.getElementById("log-module"),
  department: () => document.getElementById("log-department"),
  from: () => document.getElementById("log-from"),
  to: () => document.getElementById("log-to"),
  reset: () => document.getElementById("reset-filters"),
  refresh: () => document.getElementById("refresh-logs"),
};
