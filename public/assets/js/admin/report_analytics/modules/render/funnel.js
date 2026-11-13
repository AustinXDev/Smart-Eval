import { countUp } from "../../../shared/count_up.js";

function setWidth(id, percent) {
  const el = document.getElementById(id);
  if (el) el.style.width = `${percent}%`;
}

export function renderParticipationFunnel(data) {
  const funnelContainer = document.getElementById("funnel-container");

  if (!data) {
    if (funnelContainer) {
      funnelContainer.innerHTML = `
        <div class="flex h-full min-h-48 w-full flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-gray-200 bg-gray-50 px-6 py-10 text-center">
          <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gray-100">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4 19V5m0 14h16M8 15l3-3 3 2 5-6" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">No participation data yet</p>
            <p class="mt-1 text-xs text-gray-400">
              Participation statistics will appear here when evaluation data is available.
            </p>
          </div>
        </div>
      `;
    }

    ["totalEnrolled", "totalStudents", "totalNeverStarted", "totalAbandoned", "totalCompleted"].forEach(
      (id) => countUp(id, 0),
    );
    ["completionRate", "abandonedRate", "neverStartedRate"].forEach((id) =>
      countUp(id, 0, { decimals: 1, suffix: "%" }),
    );
    setWidth("funnel-fill-TotalEnrolled", 0);
    setWidth("funnel-fill-Unresponsive", 0);
    setWidth("funnel-fill-InProgress", 0);
    setWidth("funnel-fill-Completed", 0);
    return;
  }

  const total = Number(data.total_enrolled) || 0;
  const unresponsive = Number(data.total_unresponsive) || 0;
  const incomplete = Number(data.total_incomplete) || 0;
  const completed = Number(data.total_completed) || 0;

  countUp("totalEnrolled", total);
  countUp("totalStudents", total);
  countUp("totalNeverStarted", unresponsive);
  countUp("totalAbandoned", incomplete);
  countUp("totalCompleted", completed);

  const unresponsiveRate = total > 0 ? (unresponsive / total) * 100 : 0;
  const incompleteRate = total > 0 ? (incomplete / total) * 100 : 0;
  const completionRate = total > 0 ? (completed / total) * 100 : 0;

  setWidth("funnel-fill-TotalEnrolled", 100);
  setWidth("funnel-fill-Unresponsive", unresponsiveRate);
  setWidth("funnel-fill-InProgress", incompleteRate);
  setWidth("funnel-fill-Completed", completionRate);

  const rate = { decimals: 1, suffix: "%" };
  countUp("completionRate", completionRate, rate);
  countUp("abandonedRate", incompleteRate, rate);
  // Was `unresponsive.toFixed(1)`: showed the student count with a % sign.
  countUp("neverStartedRate", unresponsiveRate, rate);
}
