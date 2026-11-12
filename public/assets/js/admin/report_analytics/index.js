import {
  initRankingTable,
  initHistoryTable,
} from "../shared/table-config.js";

import { renderRanking } from "./modules/render/table.js";

import { dept, periodId, state, tableInstances } from "./modules/state.js";
import { fetchAnalytics } from "./api/api.js";
import { startLivePolling, initVisibilityHandling } from "./modules/polling.js";
import { renderHistoricalBanner } from "./modules/render/historical_banner.js";
import { initDashboardBindings } from "./modules/binding.js";
import { logout } from "../shared/logout.js";

document.addEventListener("DOMContentLoaded", async () => {
  const data = await fetchAnalytics(dept, periodId, false);

  tableInstances.ranking = initRankingTable();
  state.historyTable = initHistoryTable(periodId);

  if (data) {
    renderRanking(data.teacher_ranking);
  }

  initDashboardBindings();
  initVisibilityHandling();

  startLivePolling();
  renderHistoricalBanner();
});

window.logout = logout;
