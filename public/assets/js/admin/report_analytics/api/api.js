import { get, post } from "../../../services/http.js";
import { state, hasChanged } from "../modules/state.js";
import { renderHeaderInfo } from "../modules/render/header.js";
import { renderParticipationFunnel } from "../modules/render/funnel.js";
import { renderCharts } from "../modules/render/charts.js";
import { renderQuestionBreakDown } from "../modules/render/question_breakdown.js";
import {
  renderRanking,
  renderNotEvaluated,
  renderAbandoned,
} from "../modules/render/table.js";
import { renderHistoricalBanner } from "../modules/render/historical_banner.js";
import { updateNotifyAllButtonState } from "../modules/binding.js";

function renderEmptyState() {
  renderHeaderInfo(null);
  renderParticipationFunnel(null);
  renderCharts(null);
  renderQuestionBreakDown(null);
  renderRanking(null);
  renderNotEvaluated(null);
  renderAbandoned(null);
  state.lastData = null;
}

export async function fetchAnalytics(deptParam, pidParam, renderTables = true) {
  if (state.isFetching) return null;

  state.isFetching = true;

  try {
    let url = `admin/analytics/analytics.php?dept=${deptParam}`;

    if (pidParam) {
      url += `&period_id=${pidParam}`;
    }

    const response = await get(url);

    if (response.status === "error") {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = response.data;

    if (!data || Object.keys(data).length === 0 || data.error) {
      renderEmptyState();
      return null;
    }

    // Set state BEFORE table initialization
    state.isClosed = Boolean(data.is_closed);
    state.isActive = Boolean(data.period?.is_active);

    // ------------------------------------------------
    // Other analytics rendering
    // ------------------------------------------------

    if (data.period && hasChanged(data, "period")) {
      renderHeaderInfo(data, data.period);
    }

    if (data.funnel && hasChanged(data, "funnel")) {
      renderParticipationFunnel(data.funnel);
    }

    if (
      hasChanged(data, "mean_score_trend") ||
      hasChanged(data, "year_participation") ||
      hasChanged(data, "category_performance")
    ) {
      setTimeout(() => renderCharts(data), 50);
    }

    if (data.question_breakdown && hasChanged(data, "question_breakdown")) {
      renderQuestionBreakDown(data);
    }

    // ------------------------------------------------
    // Tables
    // ------------------------------------------------

    if (renderTables) {
      if (hasChanged(data, "teacher_ranking")) {
        renderRanking(data.teacher_ranking);
      }

      if (hasChanged(data, "not_evaluated")) {
        renderNotEvaluated(data.not_evaluated);
      }

      if (hasChanged(data, "abandoned")) {
        renderAbandoned(data.abandoned);
      }
    }

    renderHistoricalBanner();

    state.lastData = data;

    updateNotifyAllButtonState();

    return data;
  } catch (error) {
    console.error("Error fetching analytics:", error);
    return null;
  } finally {
    state.isFetching = false;
  }
}

export async function notifyAll(department) {
  try {
    return await post("notification/notification.php", {
      department: department,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
