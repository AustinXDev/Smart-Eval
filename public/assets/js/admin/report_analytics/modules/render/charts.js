import {
  createTrendLineChart,
  createYearLevelParticipationChart,
  createRadarChart,
} from "../../../../charts/chart-config.js";
import { chartInstances, destroyChart } from "../state.js";
import {
  getAdjectiveRating,
  calculateGrowthRate,
  formatYearLevel,
} from "../helpers/formatters.js";
import { growthRateUI, meanScoreUi } from "../helpers/score_ui.js";

function renderTrendChart(data) {
  const container = document.getElementById("trendChart");
  const meanParentContainer = document.getElementById("meanParentContainer");
  const meanScoreContainer = document.getElementById("meanScore");

  if (!data?.mean_score_trend?.length) {
    destroyChart("trend");
    meanScoreContainer.innerHTML = `<div class="text-center text-gray-400 text-sm">No data availbale.</div>`;
    container.innerHTML = `<div class="text-center text-gray-400 text-sm">No trend data available.</div>`;
    document.querySelector(".adjectiveRating").innerText = "--";
    document.getElementById("trendGrowth").innerText = "0%";
    return;
  }

  if (!document.getElementById("trendChartCanvas")) {
    container.innerHTML = `<canvas id="trendChartCanvas"></canvas>`;
  }

  const ctx = document.getElementById("trendChartCanvas").getContext("2d");
  destroyChart("trend");

  const labels = data.mean_score_trend.map((t) => t.academic_year);
  const scores = data.mean_score_trend.map((t) => t.final_average);
  chartInstances.trend = createTrendLineChart(ctx, labels, scores);

  const trend = data.mean_score_trend;
  const latestTrend = trend.at(-1);
  const previousTrend = trend.at(-2);
  const latestMean = latestTrend.final_average ?? 0;
  const previousMean = previousTrend?.final_average ?? 0;

  meanScoreUi(
    latestMean,
    getAdjectiveRating(latestMean),
    meanParentContainer,
    meanScoreContainer,
  );

  const growth = calculateGrowthRate(latestMean, previousMean);
  growthRateUI(growth, "trendGrowth");
}

function renderParticipationChart(data) {
  const container = document.getElementById("participationContainer");

  if (!data?.year_participation?.length) {
    destroyChart("participation");
    container.innerHTML = `<div class="text-center text-gray-400 text-sm">No participation data available.</div>`;
    return;
  }

  if (!document.getElementById("participationChart")) {
    container.innerHTML = `<canvas id="participationChart"></canvas>`;
  }

  const ctx = document.getElementById("participationChart").getContext("2d");
  destroyChart("participation");

  const labels = data.year_participation.map((item) =>
    formatYearLevel(item.year_level),
  );

  const finished = data.year_participation.map((item) => {
    const enrolled = Number(item.total_enrolled) || 0;
    const done = Number(item.total_finished) || 0;
    return enrolled > 0 ? (done / enrolled) * 100 : 0;
  });

  const pending = data.year_participation.map((item) => {
    const enrolled = Number(item.total_enrolled) || 0;
    const notFinished = Number(item.total_not_finished) || 0;
    return enrolled > 0 ? (notFinished / enrolled) * 100 : 0;
  });

  chartInstances.participation = createYearLevelParticipationChart(
    ctx,
    labels,
    finished,
    pending,
  );
}

function renderCategoryChart(data) {
  console.log(data);
  const container = document.getElementById("categoryContainer");

  if (!data?.category_performance?.length) {
    destroyChart("category");
    container.innerHTML = `<div class="text-center text-gray-400 text-sm">No data available in this period.</div>`;
    return;
  }

  if (!document.getElementById("radarChartCanvas")) {
    container.innerHTML = `<canvas id="radarChartCanvas"></canvas>`;
  }

  const ctx = document.getElementById("radarChartCanvas").getContext("2d");
  destroyChart("category");

  const labels = data.category_performance.map((item) => item.category);
  const scores = data.category_performance.map((item) =>
    parseFloat(item.average_score),
  );

  chartInstances.category = createRadarChart(ctx, labels, scores);

  document.getElementById("highestCategory").innerText = data
    .category_highlights.highest
    ? data.category_highlights.highest.category
    : "No highest-performing category identified";

  document.getElementById("highestScore").innerText = data.category_highlights
    .highest
    ? data.category_highlights.highest.score
    : "—";
  document.getElementById("lowestCategory").innerText = data.category_highlights
    .lowest
    ? data.category_highlights.lowest.category
    : "No lower-performing category identified";
  document.getElementById("lowestScore").innerText = data.category_highlights
    .lowest
    ? data.category_highlights.lowest.score
    : "—";
}

export function renderCharts(data) {
  renderTrendChart(data);
  renderParticipationChart(data);
  renderCategoryChart(data);
}
