import { tableInstances } from "../state.js";

function renderDataTable(tableKey, countElId, data, isClosed = false) {
  const table = tableInstances[tableKey];

  if (!table) return;

  const cnt = document.getElementById(countElId);

  if (!data || data.length === 0) {
    if (cnt) cnt.innerText = 0;
    table.clear().draw();
    return;
  }

  if (cnt) cnt.innerText = data.length;

  setTimeout(() => {
    table.clear().rows.add(data).draw();
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
