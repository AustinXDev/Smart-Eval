import { initLogTable, tableInstances, ARCHIVE_FILTERS, LOG_FILTERS } from "../table.js";
import { loadOverview } from "./overview.js";
import { byId } from "./ui.js";

export function initNavigation() {
  const archiveTable = tableInstances.archive;
  function activateTab(id, focus = false) {
    for (const tab of document.querySelectorAll("[data-tab]")) {
      const selected = tab.dataset.tab === id;
      tab.setAttribute("aria-selected", String(selected));
      tab.tabIndex = selected ? 0 : -1;
      tab.classList.toggle("border-violet-600", selected);
      tab.classList.toggle("text-violet-700", selected);
      tab.classList.toggle("border-transparent", !selected);
      tab.classList.toggle("text-slate-500", !selected);
      byId("panel-" + tab.dataset.tab).classList.toggle("hidden", !selected);
      if (selected && focus) tab.focus();
    }
    if (id === "logs") initLogTable();
    if (id === "records") archiveTable.columns.adjust();
    if (id === "logs") tableInstances.logs.columns.adjust();
  }
  const tabs = Array.from(document.querySelectorAll("[data-tab]"));
  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => activateTab(tab.dataset.tab));
    tab.addEventListener("keydown", (event) => {
      let next;
      if (event.key === "ArrowRight") next = (index + 1) % tabs.length;
      if (event.key === "ArrowLeft")
        next = (index + tabs.length - 1) % tabs.length;
      if (event.key === "Home") next = 0;
      if (event.key === "End") next = tabs.length - 1;
      if (next !== undefined) {
        event.preventDefault();
        activateTab(tabs[next].dataset.tab, true);
      }
    });
  });
  function bindFilters(prefix, names, table) {
    let timer;
    names.forEach((name) => {
      const element = byId(prefix + "-" + name);
      element.addEventListener(name === "search" ? "input" : "change", () => {
        clearTimeout(timer);
        timer = setTimeout(
          () => table()?.ajax.reload(),
          name === "search" ? 300 : 0,
        );
      });
    });
  }
  bindFilters(
    "archive",
    ARCHIVE_FILTERS,
    () => archiveTable,
  );
  bindFilters("logs", LOG_FILTERS, () => tableInstances.logs);
  byId("refresh-archive").addEventListener("click", async (event) => {
    const button = event.currentTarget;
    button.disabled = true;
    byId("archive-notice").classList.add("hidden");
    await loadOverview();
    archiveTable.ajax.reload(null, false);
    tableInstances.logs?.ajax.reload(null, false);
    button.disabled = false;
  });
}
