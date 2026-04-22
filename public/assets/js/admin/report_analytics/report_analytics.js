const urlParams = new URLSearchParams(window.location.search);
const dept = urlParams.get("dept");
const periodId = urlParams.get("period_id");

let isFetching = false;

const POLL_INTERVAL = 30000;
let pollTimer = null;
let isVisible = true;
let lastData = null;

function startLivePolling(data, periodId) {
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
    startLivePolling(dept, periodId);
  } else {
    stopPolling();
  }
});

function hasChanged(newData, key) {
  if (!lastData) return true;
  return JSON.stringify(lastData[key]) !== JSON.stringify(newData[key]);
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
      renderParticipationFunnel(null);
      lastData = null;
    }

    if (data && data.funnel) {
      if (hasChanged(data, "funnel")) {
        renderParticipationFunnel(data.funnel);
      }
    }

    lastData = data;
  } catch (error) {
    console.error("Error fetching analytics:", error);
  } finally {
    isFetching = false;
  }
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

document.addEventListener("DOMContentLoaded", async () => {
  await fetchAnalytics(dept, periodId);
  startLivePolling(dept, periodId);
});
