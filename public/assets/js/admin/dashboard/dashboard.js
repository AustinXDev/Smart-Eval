import { nameToInitials } from "../shared/utils.js";
import {
  createPieChart,
  createScoreDoughnutChart,
  createProgramBarChart,
} from "../../charts/chart-config.js";

const queryString = new URL(window.location.href);
const department = queryString.searchParams.get("dept");

const POLL_INTERVAL = 30000;

let isFetching = false;
let pollTimer = null;
let isVisible = true;
let lastData = null;

const chartInstances = {
  score: null,
  participation: null,
  program: null,
};

/* =========================================================
   HELPERS
========================================================= */

function convertDateStr(date) {
  if (!date) return "--";

  const dateObj = new Date(date);

  if (Number.isNaN(dateObj.getTime())) {
    return "--";
  }

  return dateObj.toLocaleDateString("en-US", {
    month: "long",
    day: "numeric",
    year: "numeric",
  });
}

function safeNumber(value, fallback = 0) {
  const number = Number(value);

  return Number.isFinite(number) ? number : fallback;
}

function safeString(value, fallback = "--") {
  if (value === null || value === undefined || value === "") {
    return fallback;
  }

  return String(value);
}

function safeArray(value) {
  return Array.isArray(value) ? value : [];
}

function safeObject(value) {
  return value && typeof value === "object" && !Array.isArray(value)
    ? value
    : {};
}

function destroyChart(key) {
  if (chartInstances[key]) {
    chartInstances[key].destroy();
    chartInstances[key] = null;
  }
}

function hasChanged(newData, key) {
  if (!lastData) return true;

  return (
    JSON.stringify(lastData?.[key] ?? null) !==
    JSON.stringify(newData?.[key] ?? null)
  );
}

function showConnectionError() {
  const el = document.getElementById("connection-error");

  if (!el) return;

  el.classList.remove("hidden");

  setTimeout(() => {
    el.classList.add("hidden");
  }, 4000);
}

/* =========================================================
   POLLING
========================================================= */

function startPolling() {
  stopPolling();

  pollTimer = setInterval(async () => {
    if (isVisible) {
      await fetchDashboardBundle();
      console.log("Dashboard refreshed");
    }
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
    fetchDashboardBundle();
    startPolling();
  } else {
    stopPolling();
  }
});

/* =========================================================
   FETCH DASHBOARD
========================================================= */

async function fetchDashboardBundle() {
  if (isFetching) return;

  if (!department) {
    console.error("Department not found.");
    return;
  }

  isFetching = true;

  try {
    const url =
      `/Smart-Eval/api/admin/dashboard/dashboard.php` +
      `?req=dashboard_bundle` +
      `&department=${encodeURIComponent(department)}`;

    const res = await fetch(url, {
      method: "GET",
      headers: {
        Accept: "application/json",
      },
    });

    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`);
    }

    const response = await res.json();

    console.log("API Response:", response);

    /*
     * API RESPONSE SHOULD LOOK LIKE:
     *
     * {
     *   status: "success",
     *   data: {
     *      cards: {...},
     *      teacher_ranking: [],
     *      participation_chart: null,
     *      score_chart: null,
     *      program_chart: null,
     *      categorical_breakdown: []
     *   }
     * }
     */

    if (!response || typeof response !== "object") {
      throw new Error("Invalid API response.");
    }

    if (response.status !== "success") {
      console.warn("API did not return success.");

      renderCards(null);
      renderParticipationCards(null);
      renderTeacherRanking([]);
      renderCharts(null);
      renderCategoricalBreakdown([]);

      lastData = null;

      return;
    }

    const data = safeObject(response.data);

    /*
     * No dashboard data
     */
    if (Object.keys(data).length === 0) {
      console.warn("Dashboard data is empty.");

      renderCards(null);
      renderParticipationCards(null);
      renderTeacherRanking([]);
      renderCharts(null);
      renderCategoricalBreakdown([]);

      lastData = null;

      return;
    }

    console.log("Dashboard Data:", data);

    /* -------------------------------------------------------
       CARDS
    ------------------------------------------------------- */

    if (hasChanged(data, "cards")) {
      renderCards(data.cards);
    }

    /* -------------------------------------------------------
       PARTICIPATION
    ------------------------------------------------------- */

    if (hasChanged(data, "participation_chart")) {
      const participation = safeObject(data.participation_chart);
      const cards = safeObject(data.cards);

      /*
       * Even when participation_chart is NULL,
       * we can still display values from cards.
       */
      renderParticipationCards({
        ...participation,

        total_submitted: safeNumber(cards.total_submitted, 0),

        not_evaluated: safeNumber(cards.not_evaluated, 0),
      });
    }

    /* -------------------------------------------------------
       TEACHER RANKING
    ------------------------------------------------------- */

    if (hasChanged(data, "teacher_ranking")) {
      renderTeacherRanking(data.teacher_ranking);
    }

    /* -------------------------------------------------------
       CHARTS
    ------------------------------------------------------- */

    if (
      hasChanged(data, "score_chart") ||
      hasChanged(data, "participation_chart") ||
      hasChanged(data, "program_chart")
    ) {
      setTimeout(() => {
        renderCharts(data);
      }, 50);
    }

    /* -------------------------------------------------------
       CATEGORICAL BREAKDOWN
    ------------------------------------------------------- */

    if (hasChanged(data, "categorical_breakdown")) {
      renderCategoricalBreakdown(data.categorical_breakdown);
    }

    lastData = data;
  } catch (error) {
    console.error("Dashboard bundle fetch failed:", error);

    showConnectionError();
  } finally {
    isFetching = false;
  }
}

/* =========================================================
   CARDS
========================================================= */

function renderCards(cards) {
  const set = (id, value) => {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = value;
    }
  };

  /*
   * No cards
   */
  if (!cards || typeof cards !== "object") {
    set("totalStudents", "0");
    set("totalTeachers", "0");
    set("academic_year", "No active period");
    set("semester", "--");
    set("start-date", "--");
    set("end-date", "--");
    set("percentage", "0%");

    const fill = document.getElementById("progress-fill");

    if (fill) {
      fill.style.width = "0%";
    }

    return;
  }

  /*
   * IMPORTANT:
   *
   * PHP:
   * students_total
   *
   * NOT:
   * student_total
   */

  const studentsTotal = safeNumber(cards.students_total, 0);

  const teachersTotal = safeNumber(cards.teacher_total, 0);

  const completedStudent = safeNumber(cards.completed_student, 0);

  set("totalStudents", studentsTotal);
  set("totalTeachers", teachersTotal);

  /* -------------------------------------------------------
     Evaluation Period
  ------------------------------------------------------- */

  const period = safeObject(cards.evaluation_period);

  if (Object.keys(period).length > 0) {
    set("academic_year", safeString(period.academic_year, "--"));

    set("semester", safeString(period.semester, "--"));

    set("start-date", convertDateStr(period.start_date));

    set("end-date", convertDateStr(period.end_date));
  } else {
    set("academic_year", "No active period");
    set("semester", "--");
    set("start-date", "--");
    set("end-date", "--");
  }

  /* -------------------------------------------------------
     Completion Percentage
  ------------------------------------------------------- */

  const pct =
    studentsTotal > 0
      ? Math.round((completedStudent * 100) / studentsTotal)
      : 0;

  set("percentage", `${pct}%`);

  const fill = document.getElementById("progress-fill");

  if (fill) {
    setTimeout(() => {
      fill.style.width = `${pct}%`;
    }, 200);
  }
}

/* =========================================================
   PARTICIPATION
========================================================= */

function renderParticipationCards(participation) {
  const set = (id, value) => {
    const el = document.getElementById(id);

    if (el) {
      el.textContent = value;
    }
  };

  const arrow = document.getElementById("evaluated-arrow");

  /*
   * No participation object
   */
  if (!participation || typeof participation !== "object") {
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

  const finished = safeNumber(participation.finished, 0);

  const notEvaluated = safeNumber(participation.not_evaluated, 0);

  const totalSubmitted = safeNumber(participation.total_submitted, 0);

  set("evaluated-total", finished);

  set("not-evaluated-total", notEvaluated);

  set("submitted-total", totalSubmitted);

  /*
   * Change percentage
   */

  const finishedChange = participation.finished_change;

  const percent =
    finishedChange !== null &&
    finishedChange !== undefined &&
    finishedChange !== ""
      ? `${finishedChange}%`
      : "N/A";

  if (!arrow) return;

  const isUp = participation.is_up === true;

  if (isUp) {
    arrow.className =
      "flex items-center gap-1 bg-green-100 text-green-700 text-xs font-medium px-3 py-1.5 rounded-full";

    arrow.innerHTML = `
      <svg
        class="w-3.5 h-3.5"
        fill="none"
        stroke="currentColor"
        stroke-width="2"
        viewBox="0 0 24 24"
      >
        <path
          stroke-linecap="round"
          stroke-linejoin="round"
          d="M4.5 10.5 12 3m0 0 7.5 7.5M12 3v18"
        />
      </svg>

      ${percent}
    `;
  } else {
    arrow.className =
      "flex items-center gap-1 bg-red-100 text-red-700 text-xs font-medium px-3 py-1.5 rounded-full";

    arrow.innerHTML = `
      <svg
        class="w-3.5 h-3.5"
        fill="currentColor"
        viewBox="0 0 24 24"
      >
        <path
          fill-rule="evenodd"
          d="M12 2.25a.75.75 0 0 1 .75.75v16.19l6.22-6.22a.75.75 0 1 1 1.06 1.06l-7.5 7.5a.75.75 0 0 1-1.06 0l-7.5-7.5a.75.75 0 1 1 1.06-1.06l6.22 6.22V3a.75.75 0 0 1 .75-.75Z"
          clip-rule="evenodd"
        />
      </svg>

      ${percent}
    `;
  }
}

/* =========================================================
   TEACHER RANKING
========================================================= */

function renderTeacherRanking(data) {
  const topInitials = document.getElementById("top_initials");

  const topName = document.getElementById("highest-teacher-name");

  const topScore = document.getElementById("avg-score");

  const tbody = document.getElementById("tbody-ranking");

  const ranking = safeArray(data);

  const showFallback = () => {
    if (topInitials) {
      topInitials.textContent = "--";
    }

    if (topName) {
      topName.textContent = "No ranking data available";
    }

    if (topScore) {
      topScore.textContent = "--";
    }

    if (tbody) {
      tbody.innerHTML = `
        <tr>
          <td
            colspan="5"
            class="px-4 py-6 text-center text-gray-400 text-sm"
          >
            No ranking data available.
          </td>
        </tr>
      `;
    }
  };

  if (ranking.length === 0) {
    showFallback();
    return;
  }

  const top = safeObject(ranking[0]);

  const teacherName = safeString(top.teacher_name, "");

  if (!teacherName) {
    showFallback();
    return;
  }

  if (topInitials) {
    topInitials.textContent = nameToInitials(teacherName);
  }

  if (topName) {
    topName.textContent = teacherName;
  }

  if (topScore) {
    topScore.textContent = safeString(top.overall_mean_score, "--");
  }

  if (!tbody) return;

  tbody.innerHTML = ranking
    .map((item, i) => {
      const d = safeObject(item);

      const name = safeString(d.teacher_name, "Unknown Teacher");

      const expected = safeNumber(d.total_expected_students, 0);

      const evaluated = safeNumber(d.total_evaluated_students, 0);

      const score = safeString(d.overall_mean_score, "--");

      return `
        <tr
          class="bg-amber-50 hover:bg-amber-100 transition-colors"
        >
          <td class="px-4 py-3 text-center">
            <span
              class="inline-flex items-center justify-center
              w-7 h-7 rounded-full bg-amber-400
              text-white font-semibold text-xs"
            >
              ${i + 1}
            </span>
          </td>

          <td class="px-4 py-3">
            <div class="flex items-center gap-3">

              <div
                class="w-8 h-8 rounded-full bg-purple-100
                flex items-center justify-center text-xs
                font-semibold text-purple-800 shrink-0"
              >
                ${nameToInitials(name)}
              </div>

              <span
                class="font-medium text-gray-800 whitespace-nowrap"
              >
                ${name}
              </span>

            </div>
          </td>

          <td
            class="px-4 py-3 text-center text-gray-600"
          >
            ${expected}
          </td>

          <td
            class="px-4 py-3 text-center text-gray-600"
          >
            ${evaluated}
          </td>

          <td class="px-4 py-3 text-center">

            <span
              class="inline-block bg-green-100
              text-green-800 text-xs font-semibold
              px-2.5 py-1 rounded-full"
            >
              ${score}
            </span>

          </td>
        </tr>
      `;
    })
    .join("");
}

/* =========================================================
   CHARTS
========================================================= */

function renderCharts(data) {
  const scoreCanvas = document.getElementById("scoreChart");

  const participationCanvas = document.getElementById("participationChart");

  const programCanvas = document.getElementById("programChart");

  /*
   * Destroy existing charts first
   */
  destroyChart("score");
  destroyChart("participation");
  destroyChart("program");

  /*
   * No data
   */
  if (!data || typeof data !== "object") {
    return;
  }

  /* -------------------------------------------------------
     SCORE CHART
  ------------------------------------------------------- */

  const scoreChart = safeObject(data.score_chart);

  const scoreLabels = safeArray(scoreChart.labels);

  const scoreData = safeArray(scoreChart.data).map((value) =>
    safeNumber(value, 0),
  );

  if (scoreCanvas && scoreLabels.length > 0 && scoreData.length > 0) {
    const ctx = scoreCanvas.getContext("2d");

    if (ctx) {
      chartInstances.score = createScoreDoughnutChart(
        ctx,
        scoreLabels,
        scoreData,
      );
    }
  }

  /* -------------------------------------------------------
     PARTICIPATION CHART
  ------------------------------------------------------- */

  const participationChart = safeObject(data.participation_chart);

  const participationLabels = safeArray(participationChart.labels);

  const participationData = safeArray(participationChart.data).map((value) =>
    safeNumber(value, 0),
  );

  if (
    participationCanvas &&
    participationLabels.length > 0 &&
    participationData.length > 0
  ) {
    const ctx = participationCanvas.getContext("2d");

    if (ctx) {
      chartInstances.participation = createPieChart(
        ctx,
        participationLabels,
        participationData,
      );
    }
  }

  /* -------------------------------------------------------
     PROGRAM CHART
  ------------------------------------------------------- */

  const programChart = safeObject(data.program_chart);

  const programLabels = safeArray(programChart.labels);

  const programFinished = safeArray(programChart.finished).map((value) =>
    safeNumber(value, 0),
  );

  const programNotFinished = safeArray(programChart.not_finished).map((value) =>
    safeNumber(value, 0),
  );

  const programTotals = safeArray(programChart.totals).map((value) =>
    safeNumber(value, 0),
  );

  if (programCanvas && programLabels.length > 0) {
    const ctx = programCanvas.getContext("2d");

    if (ctx) {
      chartInstances.program = createProgramBarChart(
        ctx,
        programLabels,
        programFinished,
        programNotFinished,
        programTotals,
      );
    }
  }
}

/* =========================================================
   CATEGORICAL BREAKDOWN
========================================================= */

function renderCategoricalBreakdown(data) {
  const container = document.getElementById("categorical-breakdown");

  if (!container) return;

  container.innerHTML = "";

  const categories = safeArray(data);

  if (categories.length === 0) {
    container.innerHTML = `
      <div
        class="px-4 py-6 text-center
        text-gray-400 text-sm"
      >
        No categorical breakdown data available.
      </div>
    `;

    return;
  }

  const COLORS = [
    "#6366f1",
    "#0ea5e9",
    "#10b981",
    "#f59e0b",
    "#ec4899",
    "#8b5cf6",
  ];

  const MAX_SCORE = 5;

  categories.forEach((item, i) => {
    const cat = safeObject(item);

    const category = safeString(cat.category, "Unknown Category");

    const avg = safeNumber(cat.cat_avg, 0);

    const barPct = Math.min(
      100,
      Math.max(0, Math.round((avg / MAX_SCORE) * 100)),
    );

    const color = COLORS[i % COLORS.length];

    const row = document.createElement("div");

    row.innerHTML = `
      <div
        class="flex items-center
        justify-between mb-1.5"
      >
        <span
          class="text-xs font-medium
          text-gray-700"
        >
          ${category}
        </span>

        <span
          class="text-[11px] font-semibold
          tabular-nums"
          style="color:${color}"
        >
          ${avg.toFixed(2)}

          <span
            class="text-gray-300
            font-normal"
          >
            / ${MAX_SCORE}.00
          </span>
        </span>
      </div>

      <div
        class="w-full h-2 bg-gray-100
        rounded-full overflow-hidden mb-4"
      >
        <div
          class="h-full rounded-full"
          style="
            width:0%;
            background-color:${color};
            transition:
              width 700ms
              cubic-bezier(
                0.34,
                1.56,
                0.64,
                1
              ) ${i * 80}ms;
          "
          data-width="${barPct}"
        >
        </div>
      </div>
    `;

    container.appendChild(row);

    const bar = row.querySelector("[data-width]");

    if (bar) {
      setTimeout(
        () => {
          bar.style.width = `${barPct}%`;
        },
        i * 80 + 100,
      );
    }
  });
}

/* =========================================================
   INITIALIZE
========================================================= */

document.addEventListener("DOMContentLoaded", async () => {
  if (!department) {
    console.error("Department not found in URL.");

    renderCards(null);
    renderParticipationCards(null);
    renderTeacherRanking([]);
    renderCharts(null);
    renderCategoricalBreakdown([]);

    return;
  }

  await fetchDashboardBundle();

  startPolling();
});
