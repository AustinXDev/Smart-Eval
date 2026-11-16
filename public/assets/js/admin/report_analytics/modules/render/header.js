// ================================================================
// PERIOD STATE (header strip) — same states and styling as
// Evaluation Monitoring (evaluation_monitoring/summary.js renderPeriod):
//   active period  -> "A.Y. 2026-2027 / 1st Semester"  [Live]      green, pulsing
//   closed period  -> "A.Y. 2025-2026 / 2nd Semester"  [Archived]  grey (history view)
//   no period      -> "No active period / —"           [Inactive]  amber
// ================================================================

const BADGE =
  "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-semibold";

const STATES = {
  live: {
    className: `${BADGE} border-emerald-100 bg-emerald-50 text-emerald-700`,
    dot: "bg-emerald-500 motion-safe:animate-pulse",
    label: "Live",
  },
  archived: {
    className: `${BADGE} border-slate-200 bg-slate-50 text-slate-600`,
    dot: "bg-slate-400",
    label: "Archived",
  },
  inactive: {
    className: `${BADGE} border-amber-200 bg-amber-50 text-amber-700`,
    dot: "bg-amber-500",
    label: "Inactive",
  },
};

function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setState(key) {
  const el = document.getElementById("status");
  if (!el) return;
  const { className, dot, label } = STATES[key];
  el.className = className;
  el.innerHTML = `<span class="h-1.5 w-1.5 rounded-full ${dot}"></span>${label}`;
}

export function renderHeaderInfo(data, meta) {
  if (!meta) {
    setText("period-label", "Active Period");
    setText("evaluationPeriod", "No active period");
    setText("semester", "—");
    setState("inactive");
    return;
  }

  const academicYear = String(meta.academic_year ?? "").trim();
  setText("evaluationPeriod", academicYear ? `A.Y. ${academicYear}` : "—");
  setText("semester", meta.semester || "—");

  // A closed period is a historical view (picked from View History).
  if (Boolean(data?.is_closed)) {
    setText("period-label", "Selected Period");
    setState("archived");
  } else {
    setText("period-label", "Active Period");
    setState("live");
  }
}
