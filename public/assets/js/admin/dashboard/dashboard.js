import { nameToInitials } from "../shared/utils.js";
import {
  createPieChart,
  createScoreDoughnutChart,
  createProgramBarChart,
} from "../../charts/chart-config.js";

const queryString = new URL(window.location.href);
const department = queryString.searchParams.get("dept");

function convertDateStr(date) {
  const dateStr = date;
  const dateObj = new Date(dateStr);

  const formattedDate = dateObj.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  });

  return formattedDate;
}

let isFetching = false;

const POLL_INTERVAL = 30000;
let pollTimer = null;
let isVisible = true;
let lastData = null;

//////////// Polling Controls /////////////
function startPolling() {
  stopPolling();
  pollTimer = setInterval(async () => {
    if (isVisible) await fetchDashboardBundle();
  }, POLL_INTERVAL);
}

function stopPolling() {
  if (pollTimer) {
    clearInterval(pollTimer);
    pollTimer = null;
  }
}

//////////// Polling pause when tab is hidden /////////////
document.addEventListener("visibilitychange", () => {
  isVisible = !document.hidden;
  if (isVisible) {
    fetchDashboardBundle(); // refresh immediately on tab focus
    startPolling();
  } else {
    stopPolling();
  }
});

//////////// Change Detection /////////////
function hasChanged(newData, key) {
  if (!lastData) return true;
  return JSON.stringify(lastData[key]) !== JSON.stringify(newData[key]);
}

///////// Connection Error ////////////
function showConnectionError() {
  const el = document.getElementById("connection-error");
  if (!el) return;
  el.classList.remove("hidden");
  setTimeout(() => el.classList.add("hidden"), 4000);
}

async function fetchDashboardBundle() {
  if (isFetching) return;

  isFetching = true;

  try {
    const res = await fetch(
      `/Smart-Eval/app/Controllers/dashboard/dashboard.php?req=dashboard_bundle&department=${encodeURIComponent(department)}`,
    );

    if (!res.ok) throw new Error(`HTTP ${res.status}`);

    const data = await res.json();

    if (!data || Array.isArray(data) || data.error === "no_active_period") {
      console.warn("No active evaluation period.");
      renderCards(null);
      renderParticipationCards(null);
      renderTeacherRanking([]);
      renderCharts(null);
      lastData = null;
      return;
    }

    if (hasChanged(data, "cards")) {
      renderCards(data.cards);
    }

    if (hasChanged(data, "participation_chart")) {
      renderParticipationCards({
        ...data.participation_chart,
        total_submitted: data.cards.total_submitted,
      });
    }

    if (hasChanged(data, "teacher_ranking")) {
      renderTeacherRanking(data.teacher_ranking);
    }

    if (
      hasChanged(data, "score_chart") ||
      hasChanged(data, "participation_chart") ||
      hasChanged(data, "program_chart")
    ) {
      setTimeout(() => {
        renderCharts(data);
      }, 50);
    }

    lastData = data;
  } catch (error) {
    console.error("Dashboard bundle fetch failed:", error);
  } finally {
    isFetching = false;
  }
}

function renderCards(cards) {
  // safe setter — silently skips if element doesn't exist
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  // guard: no cards data at all
  if (!cards) {
    set("totalStudents", "0");
    set("totalTeachers", "0");
    set("academic_year", "No active period");
    set("semester", "--");
    set("start-date", "--");
    set("end-date", "--");
    set("percentage", "0%");
    return;
  }

  set("totalStudents", cards.student_total ?? 0);
  set("totalTeachers", cards.teacher_total ?? 0);

  const period = cards.evaluation_period;

  if (period) {
    set("academic_year", period.academic_year || "--");
    set("semester", period.semester || "--");
    set(
      "start-date",
      period.start_date ? convertDateStr(period.start_date) : "--",
    );
    set("end-date", period.end_date ? convertDateStr(period.end_date) : "--");
  } else {
    set("academic_year", "No active period");
    set("semester", "--");
    set("start-date", "--");
    set("end-date", "--");
  }

  const pct =
    cards.student_total > 0
      ? Math.round((cards.completed_student * 100) / cards.student_total)
      : 0;

  set("percentage", `${pct}%`);

  // progress bar
  const fill = document.getElementById("progress-fill");
  if (fill) {
    setTimeout(() => {
      fill.style.width = `${pct}%`;
    }, 200);
  }
}

function renderParticipationCards(participation) {
  const set = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value;
  };

  const arrow = document.getElementById("evaluated-arrow");

  if (!participation) {
    set("evaluated-total", "0");
    set("not-evaluated-total", "0");
    set("submitted-total", "0");

    if (arrow) {
      arrow.className =
        "flex items-center gap-1 bg-gray-100 text-gray-400 text-xs font-medium px-3 py-1.5 rounded-full";
      arrow.textContent = "N/A";
    }
    return;
  }

  set("evaluated-total", participation.finished ?? 0);
  set("not-evaluated-total", participation.not_finished ?? 0);
  set("submitted-total", participation.total_submitted ?? 0);

  const percent =
    participation.finished_change !== null &&
    participation.finished_change !== undefined
      ? `${participation.finished_change}%`
      : "N/A";

  if (!arrow) return;

  //change badge
  if (participation.is_up) {
    arrow.className =
      "flex items-center gap-1 bg-green-100 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full";
    arrow.innerHTML = `
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"/>
      </svg>
      ${percent}`;
  } else {
    arrow.className =
      "flex items-center gap-1 bg-red-100 text-red-700 text-xs font-medium px-3 py-1.5 rounded-full";
    arrow.innerHTML = `
      <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
        <path fill-rule="evenodd" d="M12 2.25a.75.75 0 0 1 .75.75v16.19l6.22-6.22a.75.75 0 1 1 1.06 1.06l-7.5 7.5a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 1 1 1.06-1.06l6.22 6.22V3a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>
      </svg>
      ${percent}`;
  }
}

function renderTeacherRanking(data) {
  const top_initials = document.getElementById("top_initials");
  const top_name = document.getElementById("highest-teacher-name");
  const top_score = document.getElementById("avg-score");
  const tbody = document.getElementById("tbody-ranking");

  // normalize to array — handles null, undefined, empty object, false
  const ranking = Array.isArray(data) ? data : [];

  const showFallback = () => {
    if (top_initials) top_initials.textContent = "--";
    if (top_name) top_name.textContent = "No active evaluation period";
    if (top_score) top_score.textContent = "--";
    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td colspan="5" class="px-4 py-6 text-center text-gray-400 text-sm">
            No ranking data available.
          </td>
        </tr>`;
    }
  };

  if (ranking.length === 0) {
    showFallback();
    return;
  }

  const top = ranking[0];
  if (!top || !top.teacher_name) {
    showFallback();
    return;
  }

  if (top_initials) top_initials.textContent = nameToInitials(top.teacher_name);
  if (top_name) top_name.textContent = top.teacher_name;
  if (top_score) top_score.textContent = top.overall_mean_score ?? "--";

  tbody.innerHTML = data
    .map(
      (d, i) => `
    <tr class="bg-amber-50 hover:bg-amber-100 transition-colors">
      <td class="px-4 py-3 text-center">
        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-white font-semibold text-xs">${i + 1}</span>
      </td>
      <td class="px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-full bg-purple-100 flex items-center justify-center text-xs font-semibold text-purple-800 shrink-0">
            ${nameToInitials(d.teacher_name)}
          </div>
          <span class="font-medium text-gray-800 whitespace-nowrap">${d.teacher_name}</span>
        </div>
      </td>
      <td class="px-4 py-3 text-center text-gray-600">${d.total_expected_students}</td>
      <td class="px-4 py-3 text-center text-gray-600">${d.total_evaluated_students}</td>
      <td class="px-4 py-3 text-center">
        <span class="inline-block bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-1 rounded-full">
          ${d.overall_mean_score}
        </span>
      </td>
    </tr>
  `,
    )
    .join("");
}

function renderCharts(data) {
  if (!data) return;

  const scoreCtx = document.getElementById("scoreChart").getContext("2d");
  const participationCtx = document
    .getElementById("participationChart")
    .getContext("2d");
  const programCtx = document.getElementById("programChart").getContext("2d");

  createScoreDoughnutChart(
    scoreCtx,
    data.score_chart.labels,
    data.score_chart.data,
  );
  createPieChart(
    participationCtx,
    data.participation_chart.labels,
    data.participation_chart.data,
  );
  createProgramBarChart(
    programCtx,
    data.program_chart.labels,
    data.program_chart.finished,
    data.program_chart.not_finished,
    data.program_chart.totals,
  );
}

document.addEventListener("DOMContentLoaded", async () => {
  if (!department) {
    console.error("Department not found in URL");
    return;
  }

  await fetchDashboardBundle();
  startPolling();
});
