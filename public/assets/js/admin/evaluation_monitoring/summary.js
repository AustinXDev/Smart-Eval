// ================================================================
// KPI CARDS, PERIOD CONTEXT, AND PARTICIPATION DONUT
// ================================================================

const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
const numberFormat = new Intl.NumberFormat();

function pct(value, total) {
  return total > 0 ? (value / total) * 100 : 0;
}

function formatPct(value) {
  return `${Number.isInteger(value) ? value : value.toFixed(1)}%`;
}

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

// Count from the previously shown value to the new one.
function animateNumber(id, target) {
  const el = document.getElementById(id);
  if (!el) return;

  const from = Number(el.dataset.value) || 0;
  el.dataset.value = String(target);

  if (reduceMotion || from === target) {
    el.textContent = numberFormat.format(target);
    return;
  }

  const start = performance.now();
  const duration = 700;
  const step = (now) => {
    const t = Math.min(1, (now - start) / duration);
    const eased = 1 - Math.pow(1 - t, 3);
    el.textContent = numberFormat.format(Math.round(from + (target - from) * eased));
    if (t < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
}

export function renderPeriod(period) {
  const state = document.getElementById("period-state");

  if (!period) {
    setText("period-year", "No active period");
    setText("period-semester", "—");
    state.className =
      "inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700";
    state.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>Inactive';
    return;
  }

  setText("period-year", `A.Y. ${period.academic_year}`);
  setText("period-semester", period.semester);
  state.className =
    "inline-flex items-center gap-1.5 rounded-full border border-emerald-100 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700";
  state.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-emerald-500 motion-safe:animate-pulse"></span>Live';
}

export function renderSummary(summary = {}) {
  const eligible = Number(summary.eligible) || 0;
  const values = {
    eligible,
    today: Number(summary.submitted_today) || 0,
    completed: Number(summary.completed) || 0,
    unresponsive: Number(summary.unresponsive) || 0,
    incomplete: Number(summary.incomplete) || 0,
    remaining: Number(summary.remaining) || 0,
  };

  Object.entries(values).forEach(([key, value]) => animateNumber(`kpi-${key}`, value));

  // Share of eligible students, shown in the top-right of each KPI card.
  ["today", "completed", "unresponsive", "incomplete", "remaining"].forEach((key) => {
    setText(`kpi-${key}-share`, eligible ? formatPct(Math.round(pct(values[key], eligible) * 10) / 10) : "");
  });

  renderDonut(values, eligible, summary);
}

function renderDonut(values, eligible, summary) {
  const participating = Number(summary.participating) || 0;

  animateNumber("participation-count", participating);
  animateNumber("participation-total", eligible);
  animateNumber("participation-remaining", values.remaining);
  setText("participation-rate", eligible ? formatPct(Number(summary.participation_rate) || 0) : "—");

  let offset = 0;
  [
    ["completed", "Completed"],
    ["incomplete", "Incomplete"],
    ["unresponsive", "Unresponsive"],
  ].forEach(([key, label]) => {
    const count = values[key];
    const share = pct(count, eligible);
    const shareLabel = formatPct(Math.round(share * 10) / 10);

    const segment = document.getElementById(`donut-${key}`);
    if (segment) {
      segment.setAttribute("stroke-dasharray", `${share} ${100 - share}`);
      segment.setAttribute("stroke-dashoffset", String(-offset));
      segment.querySelector("title").textContent =
        `${label}: ${numberFormat.format(count)} student${count === 1 ? "" : "s"} (${shareLabel})`;
    }
    offset += share;

    animateNumber(`legend-${key}`, count);
    setText(`legend-${key}-pct`, eligible ? shareLabel : "—");
    const bar = document.getElementById(`legend-${key}-bar`);
    if (bar) bar.style.width = `${share}%`;
  });
}
