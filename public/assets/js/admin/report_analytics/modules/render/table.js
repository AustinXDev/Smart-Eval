import { tableInstances } from "../state.js";

function renderDataTable(tableKey, countElId, data, isClosed = false) {
  const table = tableInstances[tableKey];

  if (!table) return;

  const cnt = document.getElementById(countElId);
  const rows = Array.isArray(data) ? data : [];

  if (rows.length === 0) {
    if (cnt) cnt.innerText = 0;
    const searchInputId = {
      ranking: "search-ranking",
      not_evaluated: "search-not-evaluated",
      abandoned: "search-abandoned",
    }[tableKey];
    const searchInput = searchInputId
      ? document.getElementById(searchInputId)
      : null;

    if (searchInput) searchInput.value = "";

    table.search("");
    table.columns().search("");
    table.clear().draw();
    return;
  }

  if (cnt) cnt.innerText = rows.length;

  setTimeout(() => {
    table.clear().rows.add(rows).draw();
    table.columns.adjust().draw(false);
  }, 50);
}

export const renderRanking = (data, isClosed) => {
  return renderDataTable("ranking", "cnt-ranking", data, isClosed);
};
export const renderNotEvaluated = (data) =>
  renderDataTable("not_evaluated", "cnt-not-evaluated", data);
export const renderAbandoned = (data) =>
  renderDataTable("abandoned", "cnt-abandoned", data);
