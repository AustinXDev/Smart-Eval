export const urlParams = new URLSearchParams(window.location.search);
export const dept = urlParams.get("dept");
export const periodId = urlParams.get("period_id");

export const state = {
  isFetching: false,
  isActive: false,
  isClosed: false,
  lastData: null,
  historyTable: null,
};

export const chartInstances = {
  trend: null,
  participation: null,
  category: null,
};

export const tableInstances = {
  ranking: null,
  not_evaluated: null,
  abandoned: null,
};

export function hasChanged(newData, key) {
  if (!state.lastData) return true;
  return JSON.stringify(state.lastData[key]) !== JSON.stringify(newData[key]);
}

export function destroyChart(key) {
  if (chartInstances[key]) {
    chartInstances[key].destroy();
    chartInstances[key] = null;
  }
}
