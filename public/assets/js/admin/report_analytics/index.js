import {
  initRankingTable,
  initNotEvaluatedTable,
  initAbandonedTable,
  initHistoryTable,
} from "../shared/table-config.js";

import {
  renderRanking,
  renderNotEvaluated,
  renderAbandoned,
} from "./modules/render/table.js";

import { dept, periodId, state, tableInstances } from "./modules/state.js";
import { fetchAnalytics } from "./api/api.js";
import { startLivePolling, initVisibilityHandling } from "./modules/polling.js";
import { renderHistoricalBanner } from "./modules/render/historical_banner.js";
import { initDashboardBindings } from "./modules/binding.js";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchAnalytics(dept, periodId, false);

  tableInstances.ranking = initRankingTable();
  tableInstances.not_evaluated = initNotEvaluatedTable();
  tableInstances.abandoned = initAbandonedTable();
  state.historyTable = initHistoryTable(periodId);

  if (data) {
    console.log(data.teacher_ranking);
    renderRanking(data.teacher_ranking);
    renderNotEvaluated(data.not_evaluated);
    renderAbandoned(data.abandoned);
  }

  initDashboardBindings();
  initVisibilityHandling();

  startLivePolling();
  renderHistoricalBanner();
});
