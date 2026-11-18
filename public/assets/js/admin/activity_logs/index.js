import { logout } from "../shared/logout.js";
import { initFilters } from "./components/filters.js";
import { initTabs } from "./components/tabs.js";
import { initTable, reload } from "./table.js";

// Sidebar / header logout buttons call window.logout().
window.logout = logout;

$(document).ready(() => {
  initTable();
  initTabs(reload);
  initFilters(reload);
});
