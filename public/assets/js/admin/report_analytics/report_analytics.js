import {
  createTrendLineChart,
  createYearLevelParticipationChart,
} from "../../charts/chart-config.js";

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

    if (!data || Object.keys(data).length === 0) {
      renderHeaderInfo(null);
      renderParticipationFunnel(null);
      renderCharts(null);
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

    if (hasChanged(data, "trend")) {
      setTimeout(() => {
        renderCharts(data);
      }, 50);
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
    funnelContainer.innerHTML = `<p class="text-center text-gray-500">No data available for this department</p>`;

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

  if (
    !data ||
    !data.trend ||
    !data.trend.trend ||
    data.trend.trend.length === 0
  ) {
    destroyChart("trend");
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

    const labels = data.year_participation.map((item) => item.year_level);
    const finished = data.year_participation.map((item) =>
      parseFloat(item.completion_percentage),
    );
    const pending = data.year_participation.map(
      (item) => 100 - parseFloat(item.completion_percentage),
    );

    chartInstances.trend = createYearLevelParticipationChart(
      partCtx,
      labels,
      finished,
      pending,
    );
  }
}

function growthRateUI(growthRate, id) {
  const growthEl = document.getElementById(id);

  if (!growthEl) return;

  const prefix = growthRate > 0 ? "+" : "";
  growthEl.textContent = `${prefix}${growthRate}%`;

  if (growthRate > 0) {
    growthEl.className = `text-green-600 font-bold`;
  } else if (growthRate < 0) {
    growthEl.className = "text-red-600 font-bold";
  } else {
    growthEl.className = "text-gray-500";
    growthEl.textContent = "0%";
  }
}

document.addEventListener("DOMContentLoaded", async () => {
  await fetchAnalytics(dept, periodId);
  startLivePolling();
});
