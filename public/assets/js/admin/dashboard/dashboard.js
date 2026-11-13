import { escapeHTML } from "../../services/escape.js";
import { get } from "../../services/http.js";
import { logout } from "../shared/logout.js";
import { nameToInitials } from "../shared/utils.js";
import { countUp } from "../shared/count_up.js";
import { getAdjectiveRating } from "../report_analytics/modules/helpers/formatters.js";

// Sidebar/header logout buttons call window.logout().
window.logout = logout;

const LIVE_INTERVAL_MS = 30_000;
const { department } = window.dashboardConfig;
const $ = (id) => document.getElementById(id);

let trendChart = null;
let loading = false;
let lastUpdated = null;
let liveTimer = null;

const dateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric", year: "numeric" });
const shortDateFmt = new Intl.DateTimeFormat(undefined, { month: "short", day: "numeric" });

// MySQL "Y-m-d H:i:s" (Asia/Manila) -> Date
function parseDate(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

function setText(id, value) {
  const el = $(id);
  if (el) el.textContent = value;
}

// ================================================================
// HEADER + ACTIVE EVALUATION CARD
// ================================================================

function renderPeriod(period) {
  const status = $("hdr-status");
  const dates = $("hdr-dates");

  if (!period) {
    setText("hdr-year", "No active period");
    setText("hdr-semester", "—");
    dates.classList.add("hidden");
    status.className =
      "inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700";
    status.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Inactive';

    setText("kpi-active", "No Active Period");
    setText("kpi-active-detail", "No evaluation is currently running");
    return;
  }

  const start = parseDate(period.start_date);
  const end = parseDate(period.end_date);
  const range = start && end ? `${dateFmt.format(start)} – ${dateFmt.format(end)}` : "";

  setText("hdr-year", `A.Y. ${period.academic_year}`);
  setText("hdr-semester", period.semester);
  dates.textContent = range ? `· ${range}` : "";
  dates.classList.toggle("hidden", !range);
  status.className =
    "inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700";
  status.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>Active';

  setText("kpi-active", "Active");
  const daysLeft = end ? Math.ceil((end - Date.now()) / 86_400_000) : null;
  setText(
    "kpi-active-detail",
    `${period.semester} · ${
      daysLeft === null ? "" : daysLeft > 1 ? `${daysLeft} days left` : daysLeft === 1 ? "Ends within a day" : "Ending"
    }`,
  );
}

// ================================================================
// KPI CARDS
// ================================================================

function renderCards(cards = {}) {
  countUp("kpi-students", cards.students_total);
  countUp("kpi-teachers", cards.teachers_total);
  countUp("kpi-submitted", cards.submitted_evaluations);
}

// ================================================================
// CURRENT EVALUATION OVERVIEW
// ================================================================

function renderCurrent(period, p) {
  $("current-body").classList.toggle("hidden", !period);
  $("current-empty").classList.toggle("hidden", Boolean(period));
  if (!period || !p) return;

  const start = parseDate(period.start_date);
  const end = parseDate(period.end_date);
  setText("cur-name", `A.Y. ${period.academic_year} · ${period.semester}`);
  setText("cur-dates", start && end ? `${dateFmt.format(start)} – ${dateFmt.format(end)}` : "");

  countUp("cur-eligible", p.eligible);
  countUp("cur-eligible-2", p.eligible);
  countUp("cur-completed", p.completed);
  countUp("cur-remaining", p.remaining);
  countUp("cur-participating", p.participating);
  countUp("cur-legend-completed", p.completed);
  countUp("cur-legend-incomplete", p.incomplete);
  countUp("cur-legend-unresponsive", p.unresponsive);

  if (p.eligible > 0) {
    countUp("cur-rate", p.participation_rate, { decimals: 1, suffix: "%" });
  } else {
    $("cur-rate").dataset.countValue = "0";
    setText("cur-rate", "—");
  }

  const share = (n) => (p.eligible > 0 ? (n / p.eligible) * 100 : 0);
  $("cur-bar-completed").style.width = `${share(p.completed)}%`;
  $("cur-bar-incomplete").style.width = `${share(p.incomplete)}%`;
  $("cur-progress").setAttribute("aria-valuenow", String(p.participation_rate || 0));
}

// ================================================================
// SUBMISSION TRENDS (Chart.js line)
// ================================================================

function renderTrend(trend, period) {
  const labels = trend?.labels ?? [];
  const data = trend?.data ?? [];
  const total = data.reduce((sum, n) => sum + n, 0);
  const canvas = $("trend-chart");
  const empty = $("trend-empty");

  if (labels.length) {
    const first = parseDate(`${labels[0]} 00:00:00`);
    const last = parseDate(`${labels.at(-1)} 00:00:00`);
    setText("trend-range", labels.length === 1 ? dateFmt.format(first) : `${shortDateFmt.format(first)} – ${dateFmt.format(last)}`);
  } else {
    setText("trend-range", period ? "No data" : "No active period");
  }

  const showEmpty = total === 0;
  empty.classList.toggle("hidden", !showEmpty);
  empty.classList.toggle("flex", showEmpty);
  canvas.classList.toggle("invisible", showEmpty);
  setText(
    "trend-empty-text",
    period
      ? "Daily submissions will appear here as students submit evaluations."
      : "Submission trends are shown for the active evaluation period.",
  );
  if (showEmpty || typeof Chart === "undefined") return;

  const display = labels.map((d) => shortDateFmt.format(parseDate(`${d} 00:00:00`)));

  if (trendChart) {
    trendChart.data.labels = display;
    trendChart.data.datasets[0].data = data;
    trendChart.$fullDates = labels;
    trendChart.update();
    return;
  }

  const ctx = canvas.getContext("2d");
  const gradient = ctx.createLinearGradient(0, 0, 0, 280);
  gradient.addColorStop(0, "rgba(96, 16, 255, 0.22)");
  gradient.addColorStop(1, "rgba(96, 16, 255, 0)");

  trendChart = new Chart(ctx, {
    type: "line",
    data: {
      labels: display,
      datasets: [{
        label: "Submitted evaluations",
        data,
        borderColor: "#6010FF",
        backgroundColor: gradient,
        borderWidth: 2.5,
        fill: true,
        tension: 0.35,
        pointRadius: data.length > 31 ? 0 : 3,
        pointHoverRadius: 5,
        pointBackgroundColor: "#ffffff",
        pointBorderColor: "#6010FF",
        pointBorderWidth: 2,
      }],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: "#0f172a",
          padding: 10,
          cornerRadius: 8,
          displayColors: false,
          callbacks: {
            title: (items) => {
              const full = trendChart?.$fullDates?.[items[0].dataIndex];
              return full ? dateFmt.format(parseDate(`${full} 00:00:00`)) : items[0].label;
            },
            label: (item) => `${item.parsed.y} evaluation${item.parsed.y === 1 ? "" : "s"} submitted`,
          },
        },
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: "#94a3b8", font: { size: 11 }, maxRotation: 0, autoSkipPadding: 12 },
          title: { display: true, text: "Date", color: "#64748b", font: { size: 11, weight: "600" } },
        },
        y: {
          beginAtZero: true,
          grid: { color: "#f1f5f9" },
          border: { display: false },
          ticks: { color: "#94a3b8", font: { size: 11 }, precision: 0 },
          title: { display: true, text: "Submissions", color: "#64748b", font: { size: 11, weight: "600" } },
        },
      },
    },
  });
  trendChart.$fullDates = labels;
}

// ================================================================
// TEACHER PERFORMANCE SNAPSHOT
// ================================================================

const RATING_STYLES = {
  Outstanding: "border-emerald-200 bg-emerald-50 text-emerald-700",
  "Very Satisfactory": "border-blue-200 bg-blue-50 text-blue-700",
  Satisfactory: "border-violet-200 bg-violet-50 text-violet-700",
  Fair: "border-amber-200 bg-amber-50 text-amber-700",
  Poor: "border-rose-200 bg-rose-50 text-rose-700",
};

function renderPerformance(perf) {
  const average = perf?.overall_average ?? null;
  const ratingEl = $("perf-rating");

  if (average === null) {
    $("perf-average").dataset.countValue = "0";
    setText("perf-average", "—");
    ratingEl.className = "rounded-full border border-slate-200 bg-white px-2.5 py-1 text-xs font-semibold text-slate-500";
    ratingEl.textContent = "No Rating";
  } else {
    countUp("perf-average", average, { decimals: 2 });
    const label = getAdjectiveRating(average);
    ratingEl.className = `rounded-full border px-2.5 py-1 text-xs font-semibold ${RATING_STYLES[label] ?? ""}`;
    ratingEl.textContent = label;
  }

  const list = $("perf-top");
  const top = perf?.top_teachers ?? [];
  if (!top.length) {
    list.innerHTML = `
      <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
        <p class="text-sm font-semibold text-slate-600">No ratings yet</p>
        <p class="mt-1 text-xs text-slate-400">Top teachers appear once students complete their evaluations.</p>
      </li>`;
    return;
  }

  const medals = ["bg-amber-100 text-amber-700", "bg-slate-200 text-slate-700", "bg-orange-100 text-orange-700"];
  list.innerHTML = top
    .map(
      (t, i) => `
      <li class="flex items-center gap-3 rounded-xl border border-slate-100 p-3 transition hover:border-slate-200 hover:bg-slate-50/60">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-xs font-bold ${medals[i] ?? medals[2]}" title="Rank ${i + 1}">${i + 1}</span>
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#6010FF]/10 text-[11px] font-bold text-[#6010FF]">${escapeHTML(nameToInitials(t.full_name))}</span>
        <span class="min-w-0 flex-1">
          <span class="block truncate text-sm font-semibold text-slate-900">${escapeHTML(t.full_name)}</span>
          <span class="block text-xs text-slate-500">${Number(t.total_evaluated) || 0} student${Number(t.total_evaluated) === 1 ? "" : "s"} · ${escapeHTML(getAdjectiveRating(Number(t.mean_score)))}</span>
        </span>
        <span class="text-sm font-bold tabular-nums text-slate-900">${Number(t.mean_score).toFixed(2)}</span>
      </li>`,
    )
    .join("");
}

// ================================================================
// RECENT SYSTEM ACTIVITIES
// ================================================================

const ACTIVITY = {
  submission: {
    icon: "fa-clipboard-check",
    chip: "bg-emerald-50 text-emerald-600",
    text: (a) =>
      `<strong class="font-semibold text-slate-900">${escapeHTML(a.title)}</strong> submitted ${a.total} teacher evaluation${a.total === 1 ? "" : "s"}`,
    meta: (a) => a.detail,
  },
  student: {
    icon: "fa-user-plus",
    chip: "bg-blue-50 text-blue-600",
    text: (a) => `New student registered: <strong class="font-semibold text-slate-900">${escapeHTML(a.title)}</strong>`,
    meta: (a) => a.detail,
  },
  opened: {
    icon: "fa-play",
    chip: "bg-[#6010FF]/10 text-[#6010FF]",
    text: (a) => `Evaluation period opened: <strong class="font-semibold text-slate-900">A.Y. ${escapeHTML(a.title)}</strong>`,
    meta: (a) => a.detail,
  },
  closed: {
    icon: "fa-flag-checkered",
    chip: "bg-slate-100 text-slate-600",
    text: (a) => `Evaluation period closed: <strong class="font-semibold text-slate-900">A.Y. ${escapeHTML(a.title)}</strong>`,
    meta: (a) => a.detail,
  },
};

function timeAgo(date) {
  if (!date) return "";
  const seconds = Math.round((Date.now() - date.getTime()) / 1000);
  if (seconds < 0) return dateFmt.format(date);
  if (seconds < 60) return "just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  if (seconds < 86_400) return `${Math.floor(seconds / 3600)}h ago`;
  if (seconds < 7 * 86_400) return `${Math.floor(seconds / 86_400)}d ago`;
  return dateFmt.format(date);
}

function renderActivities(activities = []) {
  const list = $("activity-list");
  if (!activities.length) {
    list.innerHTML = `
      <li class="px-6 py-10 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-slate-50 text-slate-400"><i class="fa-solid fa-clock-rotate-left text-lg" aria-hidden="true"></i></div>
        <p class="mt-3 text-sm font-semibold text-slate-700">No recent activity</p>
        <p class="mt-1 text-xs text-slate-400">Submissions, registrations, and period changes will appear here.</p>
      </li>`;
    return;
  }

  list.innerHTML = activities
    .map((a) => {
      const kind = ACTIVITY[a.type];
      if (!kind) return "";
      const when = parseDate(a.occurred_at);
      return `
        <li class="flex items-start gap-3 px-5 py-3.5 sm:px-6">
          <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg ${kind.chip}"><i class="fas ${kind.icon} text-xs" aria-hidden="true"></i></span>
          <span class="min-w-0 flex-1">
            <span class="block text-[13px] leading-snug text-slate-600">${kind.text(a)}</span>
            <span class="mt-0.5 block text-xs text-slate-400">${escapeHTML(kind.meta(a) || "")}${kind.meta(a) ? " · " : ""}<time datetime="${escapeHTML(a.occurred_at)}" title="${when ? escapeHTML(when.toLocaleString()) : ""}">${timeAgo(when)}</time></span>
          </span>
        </li>`;
    })
    .join("");
}

// ================================================================
// LOAD + LIVE UPDATES
// ================================================================

function setLive(state) {
  const styles = {
    live: ["bg-emerald-400", "bg-emerald-500", "Live", true],
    paused: ["bg-slate-300", "bg-slate-400", "Paused", false],
    offline: ["bg-rose-400", "bg-rose-500", "Reconnecting", true],
  };
  const [ping, dot, label, pulse] = styles[state];
  $("live-ping").className = `absolute inline-flex h-full w-full rounded-full opacity-75 ${ping} ${pulse ? "motion-safe:animate-ping" : "hidden"}`;
  $("live-dot").className = `relative inline-flex h-2 w-2 rounded-full ${dot}`;
  setText("live-label", label);
}

function renderLastUpdated() {
  if (!lastUpdated) return;
  const seconds = Math.max(0, Math.round((Date.now() - lastUpdated) / 1000));
  setText("last-updated", seconds < 5 ? "just now" : seconds < 60 ? `${seconds}s ago` : `${Math.floor(seconds / 60)}m ago`);
}

async function loadDashboard() {
  if (loading) return;
  loading = true;

  try {
    const response = await get(`admin/dashboard/dashboard.php?department=${encodeURIComponent(department)}`);
    if (response?.status !== "success" || !response.data) {
      throw new Error(response?.message || "Invalid dashboard response.");
    }

    const d = response.data;
    renderPeriod(d.period);
    renderCards(d.cards);
    renderCurrent(d.period, d.participation);
    renderTrend(d.trend, d.period);
    renderPerformance(d.performance);
    renderActivities(d.activities);

    $("error-state").classList.add("hidden");
    $("error-state").classList.remove("flex");
    setLive(document.visibilityState === "visible" ? "live" : "paused");
    lastUpdated = Date.now();
    renderLastUpdated();
  } catch (error) {
    console.error("Dashboard load failed:", error);
    $("error-state").classList.remove("hidden");
    $("error-state").classList.add("flex");
    setLive("offline");
  } finally {
    loading = false;
  }
}

function startLive() {
  clearInterval(liveTimer);
  liveTimer = setInterval(() => {
    if (document.visibilityState === "visible") loadDashboard();
  }, LIVE_INTERVAL_MS);
}

document.addEventListener("visibilitychange", () => {
  if (document.visibilityState === "visible") {
    loadDashboard();
    startLive();
  } else {
    clearInterval(liveTimer);
    setLive("paused");
  }
});

$("retry-dashboard").addEventListener("click", loadDashboard);
setInterval(renderLastUpdated, 1000);

loadDashboard();
startLive();
