import {
  createTrendLineChart,
  createYearLevelParticipationChart,
  createRadarChart,
} from "../../charts/chart-config.js";

import {
  initRankingTable,
  initNotEvaluatedTable,
  initAbandonedTable,
} from "../shared/table-config.js";

const urlParams = new URLSearchParams(window.location.search);
const dept = urlParams.get("dept");
const periodId = urlParams.get("period_id");

let isFetching = false;

const POLL_INTERVAL = 30000;
let pollTimer = null;
let isVisible = true;
let lastData = null;

const chartInstances = {
  trend: null,
  participation: null,
  category: null,
};

const tableInstances = {
  ranking: null,
  not_evaluated: null,
  abandoned: null,
};

function startLivePolling() {
  stopPolling();
  pollTimer = setInterval(async () => {
    if (isVisible) await fetchAnalytics(dept, periodId);
    console.log("refreshed");
  }, POLL_INTERVAL);
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

document.addEventListener("visibilitychange", () => {
  isVisible = !document.hidden;
  if (isVisible) {
    fetchAnalytics(dept, periodId);
    startLivePolling();
  } else {
    stopPolling();
  }
});

function hasChanged(newData, key) {
  if (!lastData) return true;
  return JSON.stringify(lastData[key]) !== JSON.stringify(newData[key]);
}

function destroyChart(key) {
  if (chartInstances[key]) {
    chartInstances[key].destroy();
    chartInstances[key] = null;
  }
}

async function fetchAnalytics(deptParam, pidParam) {
  if (isFetching) return;

  isFetching = true;

  try {
    let url = `/Smart-Eval/app/Controllers/reportAnalytics/AnalyticsController.php?dept=${deptParam}`;
    if (pidParam && pidParam !== "null") url += `&period_id=${pidParam}`;

    const response = await fetch(url);

    if (!response.ok) throw new Error(`HTTP ${response.status}`);

    const data = await response.json();

    console.log(data);
    console.log("not_evaluated key:", data?.not_evaluated);

    if (!data || Object.keys(data).length === 0) {
      renderHeaderInfo(null);
      renderParticipationFunnel(null);
      renderCharts(null);
      renderQuestionBreakDown(null);
      renderRanking(null);
      renderNotEvaluated(null);
      renderAbandoned(null);
      lastData = null;
      return;
    }

    if (data && data.meta) {
      if (hasChanged(data, "meta")) {
        renderHeaderInfo(data.meta);
      }
    }

    if (data && data.funnel) {
      if (hasChanged(data, "funnel")) {
        renderParticipationFunnel(data.funnel);
      }
    }

    if (
      hasChanged(data, "trend") ||
      hasChanged(data, "year_participation") ||
      hasChanged(data, "category")
    ) {
      setTimeout(() => {
        renderCharts(data);
      }, 50);
    }

    if (data && data.questions) {
      if (hasChanged(data, "questions")) {
        renderQuestionBreakDown(data);
      }
    }

    if (hasChanged(data, "teachers")) {
      renderRanking(data.teachers);
    }

    if (hasChanged(data, "not_evaluated")) {
      renderNotEvaluated(data.not_evaluated);
    }

    if (data.abandoned && hasChanged(data, "abandoned")) {
      renderAbandoned(data.abandoned);
    }

    lastData = data;
  } catch (error) {
    console.error("Error fetching analytics:", error);
  } finally {
    isFetching = false;
  }
}

function renderHeaderInfo(meta) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const liveContainer = document.getElementById("status");
  const periodText = document.getElementById("evaluationPeriod");
  const semesterText = document.getElementById("semester");

  if (!meta) {
    if (periodText) periodText.textContent = "N/A";
    if (semesterText) semesterText.textContent = "None";

    if (liveContainer) {
      liveContainer.textContent = "No Active Evaluation";
      liveContainer.classList.remove("text-green-700");
      liveContainer.classList.remove("bg-green-50");
      liveContainer.classList.remove("border-green-300");
      liveContainer.classList.add("text-gray-500");
      liveContainer.classList.add("bg-gray-50");
      liveContainer.classList.add("border-gray-300");
    }
    return;
  }

  set("evaluationPeriod", meta.academic_year);
  set("semester", meta.semester);
}

function renderParticipationFunnel(data) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const setWidth = (id, percent) => {
    const el = document.getElementById(id);
    if (el) el.style.width = `${percent}%`;
  };

  const funnelContainer = document.getElementById("funnel-container");

  if (!data || !data.rates) {
    funnelContainer.innerHTML = `<p class="text-center text-gray-500 text-sm">No data available for this department</p>`;

    console.warn("Using fallback values: Data is null or incomplete.");
    set("totalEnrolled", "0");
    set("totalStudents", "0");
    set("totalNeverStarted", "0");
    set("totalAbandoned", "0");
    set("totalCompleted", "0");
    set("completionRate", "0%");
    set("abandonedRate", "0%");
    set("neverStartedRate", "0%");
    setWidth("funnel-fill-TotalEnrolled", 0);
    setWidth("funnel-fill-Unresponsive", 0);
    setWidth("funnel-fill-InProgress", 0);
    setWidth("funnel-fill-Completed", 0);
    return;
  }

  set("totalEnrolled", data.total);
  set("totalStudents", data.total);
  set("totalNeverStarted", data.never_started);
  set("totalAbandoned", data.abandoned);
  set("totalCompleted", data.completed);

  setWidth("funnel-fill-TotalEnrolled", 100);
  setWidth("funnel-fill-Unresponsive", data.rates.never_started);
  setWidth("funnel-fill-InProgress", data.rates.abadoned);
  setWidth("funnel-fill-Completed", (data.completed / data.total) * 100);

  set("completionRate", `${data.rates.completion}%`);
  set("abandonedRate", `${data.rates.abadoned}%`);
  set("neverStartedRate", `${data.rates.never_started}%`);
}

function renderCharts(data) {
  const trendChartContainer = document.getElementById("trendChart");
  const participationChartContainer = document.getElementById(
    "participationContainer",
  );
  const meanParentContainer = document.getElementById("meanParentContainer");
  const meanScoreContainer = document.getElementById("meanScore");
  const radarChartContainer = document.getElementById("radarChart");

  if (
    !data ||
    !data.trend ||
    !data.trend.trend ||
    data.trend.trend.length === 0
  ) {
    destroyChart("trend");
    meanScoreContainer.innerHTML = `<div class="text-center text-gray-400 text-sm">No data availbale.</div>`;
    trendChartContainer.innerHTML = `<div class="text-center text-gray-400 text-sm">No trend data available.</div>`;
    document.getElementById("trendGrowth").innerText = `0%`;
  } else {
    const trendCtx = document
      .getElementById("trendChartCanvas")
      .getContext("2d");
    destroyChart("trend");
    const labels = data.trend.trend.map((t) => t.academic_year);
    const scores = data.trend.trend.map((t) => t.final_average);
    chartInstances.trend = createTrendLineChart(trendCtx, labels, scores);

    const latestMean = data.trend.trend.at(-1)?.final_average ?? 0;
    const adjectiveRating = data.trend.adjectiveRating;

    meanScoreUi(
      latestMean,
      adjectiveRating,
      meanParentContainer,
      meanScoreContainer,
    );
    growthRateUI(data.trend.growth, "trendGrowth");
  }

  if (
    !data ||
    !data.year_participation ||
    data.year_participation.length === 0
  ) {
    destroyChart("participation");
    participationChartContainer.innerHTML = `<div class="text-center text-gray-400 text-sm">No participation data available.</div>`;
  } else {
    const partCtx = document
      .getElementById("participationChart")
      .getContext("2d");

    destroyChart("participation");

    const labels = data.year_participation.map((item) =>
      formatYearLevel(item.year_level),
    );
    const finished = data.year_participation.map((item) =>
      parseFloat(item.completion_percentage),
    );
    const pending = data.year_participation.map(
      (item) => 100 - parseFloat(item.completion_percentage),
    );

    chartInstances.participation = createYearLevelParticipationChart(
      partCtx,
      labels,
      finished,
      pending,
    );
  }

  if (
    !data ||
    !data.category.category_performance ||
    data.category.category_performance.length === 0
  ) {
    destroyChart("category");
    participationChartContainer.innerHTML = `<div class="text-center text-gray-400 text-sm">No data available in this period.</div>`;
  } else {
    const radarCtx = document
      .getElementById("radarChartCanvas")
      .getContext("2d");
    destroyChart("category");
    const highestCategory = document.getElementById("highestCategory");
    const hishestScore = document.getElementById("highestScore");
    const lowestCategory = document.getElementById("lowestCategory");
    const lowestScore = document.getElementById("lowestScore");

    const labels = data.category.category_performance.map(
      (item) => item.category,
    );
    const scores = data.category.category_performance.map((item) =>
      parseFloat(item.average_score),
    );

    chartInstances.category = createRadarChart(radarCtx, labels, scores);

    //highest  category
    highestCategory.innerText =
      data.category.performance_highlights.highest.category;
    highestScore.innerText =
      data.category.performance_highlights.highest.average_score;

    //lowest category
    lowestCategory.innerText =
      data.category.performance_highlights.lowest.category;
    lowestScore.innerText =
      data.category.performance_highlights.lowest.average_score;
  }
}

function renderQuestionBreakDown(data) {
  const highest = data?.questions?.highest || [];
  const lowest = data?.questions?.lowest || [];

  const parentContainer = document.getElementById("parentContainer");
  const highestEl = document.getElementById("highestQuestions");
  const lowestEl = document.getElementById("lowestQuestions");

  if (highest.length === 0 && lowest.length === 0) {
    if (parentContainer) {
      parentContainer.innerHTML =
        '<div class="col-span-full text-center py-10"><p class="text-gray-400 text-sm">No performance highlights available for this period.</p></div>';
    }
    return;
  }

  if (highestEl) {
    highestEl.innerHTML = highest.length
      ? highest.map((item, i) => buildRow(item, i + 1, true)).join("")
      : `<p class="text-xs text-gray-400 italic py-2">No categories met the "Good" threshold.</p>`;
  }

  if (lowestEl) {
    lowestEl.innerHTML = lowest.length
      ? lowest.map((item, i) => buildRow(item, i + 1, false)).join("")
      : `<p class="text-xs text-gray-400 italic py-2">No categories fell below the "Fair" threshold.</p>`;
  }
}

function renderRanking(data) {
  if (!tableInstances.ranking) return;

  const cnt = document.getElementById("cnt-ranking");

  if (!data || data.length === 0) {
    if (cnt) cnt.innerText = 0;
    tableInstances.ranking.clear().draw();
    return;
  }

  if (cnt) cnt.innerText = data.length;

  setTimeout(() => {
    tableInstances.ranking.clear().rows.add(data).draw();
    tableInstances.ranking.columns.adjust().draw(false);
  }, 50);
}

function renderNotEvaluated(data) {
  if (!tableInstances.not_evaluated) return;

  const cnt = document.getElementById("cnt-not-evaluated");

  if (!data || data.length === 0) {
    if (cnt) cnt.innerText = 0;
    tableInstances.not_evaluated.clear().draw();
    return;
  }

  if (cnt) cnt.innerText = data.length;

  setTimeout(() => {
    tableInstances.not_evaluated.clear().rows.add(data).draw();
    tableInstances.not_evaluated.columns.adjust().draw(false);
  }, 50);
}

function renderAbandoned(data) {
  if (!tableInstances.abandoned) return;

  const cnt = document.getElementById("cnt-abandoned");

  if (!data || data.length === 0) {
    if (cnt) cnt.innerText = 0;
    tableInstances.abandoned.clear().draw();
    return;
  }

  if (cnt) cnt.innerText = data.length;

  setTimeout(() => {
    tableInstances.abandoned.clear().rows.add(data).draw();
    tableInstances.abandoned.columns.adjust().draw(false);
  }, 50);
}

///////////////////////////////////////////////////////////////////////////////
/*                                 HELPERS                                   */
///////////////////////////////////////////////////////////////////////////////

//growthRateUI helpers
function growthRateUI(growthRate, id) {
  const growthEl = document.getElementById(id);

  if (!growthEl) return;

  const rate = Number(growthRate);

  const prefix = growthRate > 0 ? "+" : "";
  growthEl.textContent = `${prefix}${rate}%`;

  if (rate > 0) {
    growthEl.className = `text-green-600 font-bold`;
  } else if (growthRate < 0) {
    growthEl.className = "text-red-600 font-bold";
  } else {
    growthEl.className = "text-gray-500";
    growthEl.textContent = "0%";
  }
}

//mean score UI helpers
function meanScoreUi(mean, adjectiveRating, parentId, childId) {
  if (!parentId || !childId) {
    console.warn("Mean UI elements missing");
    return;
  }

  childId.textContent = Number(mean).toFixed(2);

  const states = [
    {
      min: 4.21,
      max: 5.0,
      bg: "#ECFDF5",
      text: "#065F46",
      label: "#34D399",
      sublabel: "#6EE7B7",
    },
    {
      min: 3.41,
      max: 4.2,
      bg: "#EDFAF1",
      text: "#1A7F3C",
      label: "#4ADE80",
      sublabel: "#86EFAC",
    },
    {
      min: 2.61,
      max: 3.4,
      bg: "#FFFBEB",
      text: "#B45309",
      label: "#F59E0B",
      sublabel: "#FCD34D",
    },
    {
      min: 1.81,
      max: 2.6,
      bg: "#FEF3C7",
      text: "#92400E",
      label: "#D97706",
      sublabel: "#FCD34D",
      adjectiveRating: "Fair",
    },
    {
      min: 1.0,
      max: 1.8,
      bg: "#FEF2F2",
      text: "#991B1B",
      label: "#F87171",
      sublabel: "#FCA5A5",
    },
  ];

  const state = states.find((s) => mean >= s.min && mean <= s.max) ?? states[0];

  parentId.style.background = state.bg;
  childId.style.color = state.text;

  const titleEl = parentId.querySelector(".mean-title");
  const sublabelEl = parentId.querySelector(".mean-sublabel");
  const adjectiveRatingEl = parentId.querySelector(".adjectiveRating");

  adjectiveRatingEl.style.color = state.text;

  if (titleEl) titleEl.style.color = state.label;
  if (adjectiveRatingEl) adjectiveRatingEl.textContent = adjectiveRating;
  if (sublabelEl) sublabelEl.style.color = state.sublabel;
}

//year formatting helper
function formatYearLevel(raw) {
  const num = parseInt(raw, 10);

  if (num === 11 || num === 12) return `Grade ${num}`;

  const suffixes = ["", "1st", "2nd", "3rd", "4th"];
  if (num >= 1 && num <= 4) return `${suffixes[num]} Year`;

  return String(raw);
}

//buildrow helper for question gap
function buildRow(item, rank, isHigh) {
  const pct = Math.min((item.average_score / 5) * 100, 100).toFixed(0);
  const bg = isHigh ? "#F0FDF4" : "#FEF2F2";
  const border = isHigh ? "border-green-100" : "border-red-100";
  const barBg = isHigh ? "bg-green-100" : "bg-red-100";
  const barFill = isHigh ? "bg-green-500" : "bg-red-500";
  const scoreClr = isHigh ? "text-green-700" : "text-red-600";
  const badgeClr = isHigh
    ? rank === 1
      ? "bg-green-500"
      : "bg-green-400"
    : rank === 1
      ? "bg-red-500"
      : "bg-red-400";

  return `
  <div class="flex items-start gap-2.5 p-2.5 rounded-lg border ${border}" style="background:${bg};">
    <span class="flex-shrink-0 w-5 h-5 rounded-full ${badgeClr} text-white text-xs font-bold flex items-center justify-center mt-0.5">
      ${rank}
    </span>
    <div class="flex-1 min-w-0">
      <p class="text-xs text-gray-700 leading-relaxed line-clamp-2">${item.question_text}</p>
      <div class="flex items-center gap-2 mt-1.5">
        <div class="flex-1 ${barBg} rounded-full h-1.5 overflow-hidden">
          <div class="h-full rounded-full ${barFill}" style="width:${pct}%;"></div>
        </div>
        <span class="text-xs font-bold ${scoreClr} flex-shrink-0">${Number(item.average_score).toFixed(2)}</span>
      </div>
    </div>
  </div>`;
}

document.addEventListener("DOMContentLoaded", async () => {
  tableInstances.ranking = initRankingTable();
  tableInstances.not_evaluated = initNotEvaluatedTable();
  tableInstances.abandoned = initAbandonedTable();

  const searchMap = [
    { inputId: "search-ranking", table: tableInstances.ranking },
    { inputId: "search-not-evaluated", table: tableInstances.not_evaluated },
    { inputId: "search-abandoned", table: tableInstances.abandoned },
  ];

  searchMap.forEach(({ inputId, table }) => {
    const input = document.getElementById(inputId);
    if (!input || !table) return;
    input.addEventListener("input", function () {
      table.search(this.value).draw();
    });
  });

  document.querySelectorAll(".tab-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      document
        .querySelectorAll(".tab-btn")
        .forEach((b) => b.classList.remove("active"));
      btn.classList.add("active");

      const target = btn.dataset.target;

      // show/hide panels
      document.querySelectorAll(".tab-panel").forEach((panel) => {
        if (panel.id === target) {
          panel.classList.remove("hidden");
        } else {
          panel.classList.add("hidden");
        }
      });

      const tableMap = {
        "panel-ranking": tableInstances.ranking,
        "panel-not-evaluated": tableInstances.not_evaluated,
        "panel-abandoned": tableInstances.abandoned,
      };

      setTimeout(() => {
        tableMap[target]?.columns.adjust().draw(false);
      }, 100);
    });
  });

  await fetchAnalytics(dept, periodId);
  startLivePolling();
});
