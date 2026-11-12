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
  const distribution = data?.rating_distribution ?? {};

  const ratings = [
    ["Outstanding", "#10B981"],
    ["Very Satisfactory", "#3B82F6"],
    ["Satisfactory", "#8B5CF6"],
    ["Fair", "#F59E0B"],
    ["Poor", "#F43F5E"],
  ];

  const total = ratings.reduce(
    (sum, [label]) => sum + (Number(distribution[label]) || 0),
    0,
  );

  const target = document.getElementById("rating-distribution");
  const totalTarget = document.getElementById("rating-distribution-total");
  const chartCanvas = document.getElementById("ratingDistributionChart");

  if (totalTarget) totalTarget.textContent = total;

  if (total === 0) {
    if (target) {
      target.innerHTML = `
      <div class="flex flex-col items-center justify-center rounded-xl border border-dashed border-slate-200 bg-slate-50 px-4 py-10 text-center">
        <div class="mb-3 flex h-11 w-11 items-center justify-center rounded-full bg-slate-100">
          <i class="fa-solid fa-chart-column text-lg text-slate-400"></i>
        </div>
        <p class="text-sm font-semibold text-slate-600">
          No rating data available
        </p>
        <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-400">
          Evaluated teacher ratings will appear here once evaluation results are available.
        </p>
      </div>
    `;
    }

    if (chartInstances.ratingDistribution) {
      chartInstances.ratingDistribution.destroy();
      chartInstances.ratingDistribution = null;
    }
  } else {
    if (target) {
      target.innerHTML = ratings
        .map(([label, color]) => {
          const count = Number(distribution[label]) || 0;
          const percentage = ((count / total) * 100).toFixed(1);

          return `
        <div class="group flex items-center justify-between gap-3 rounded-xl border border-slate-100 bg-white px-3.5 py-3 transition-all duration-200 hover:border-slate-200 hover:bg-slate-50 hover:shadow-sm">
          
          <div class="flex min-w-0 items-center gap-3">
            <span
              class="h-9 w-1 shrink-0 rounded-full"
              style="background-color: ${color}"
            ></span>

            <div class="min-w-0">
              <p class="truncate text-xs font-semibold text-slate-700">
                ${label}
              </p>
              <p class="mt-0.5 text-[11px] text-slate-400">
                ${percentage}% of teachers
              </p>
            </div>
          </div>

          <div class="shrink-0 text-right">
            <p class="text-base font-bold tabular-nums text-slate-800">
              ${count}
            </p>
            <p class="text-[10px] font-medium uppercase tracking-wide text-slate-400">
              Teachers
            </p>
          </div>

        </div>
      `;
        })
        .join("");
    }

    if (chartCanvas) {
      if (chartInstances.ratingDistribution) {
        chartInstances.ratingDistribution.destroy();
      }

      chartInstances.ratingDistribution = new Chart(
        chartCanvas.getContext("2d"),
        {
          type: "bar",

          data: {
            labels: ratings.map(([label]) => label),

            datasets: [
              {
                label: "Teachers",
                data: ratings.map(
                  ([label]) => Number(distribution[label]) || 0,
                ),
                backgroundColor: ratings.map(([, color]) => color),
                borderRadius: 6,
                borderSkipped: false,
                barPercentage: 0.65,
                categoryPercentage: 0.75,
                maxBarThickness: 32,
              },
            ],
          },

          options: {
            indexAxis: "y",
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 500,
              easing: "easeOutQuart",
            },

            layout: {
              padding: {
                right: 8,
                left: 4,
              },
            },

            plugins: {
              legend: {
                display: false,
              },

              tooltip: {
                backgroundColor: "#0F172A",
                titleColor: "#FFFFFF",
                bodyColor: "#E2E8F0",
                padding: 12,
                cornerRadius: 8,
                displayColors: true,

                callbacks: {
                  label: (context) => {
                    const count = context.parsed.x;
                    const percentage = ((count / total) * 100).toFixed(1);

                    return ` ${count} ${count === 1 ? "teacher" : "teachers"} (${percentage}%)`;
                  },
                },
              },
            },

            scales: {
              x: {
                beginAtZero: true,

                ticks: {
                  precision: 0,
                  color: "#94A3B8",
                  font: {
                    size: 11,
                  },
                  padding: 8,
                },

                grid: {
                  color: "#F1F5F9",
                  drawTicks: false,
                },

                border: {
                  display: false,
                },
              },

              y: {
                ticks: {
                  color: "#475569",
                  font: {
                    size: 11,
                    weight: "500",
                  },
                  padding: 10,
                },

                grid: {
                  display: false,
                },

                border: {
                  display: false,
                },
              },
            },
          },
        },
      );
    }
  }

  renderTrendChart(data);
  renderParticipationChart(data);
  renderCategoryChart(data);
}
