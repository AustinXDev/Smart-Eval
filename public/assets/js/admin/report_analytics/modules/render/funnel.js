function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

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

    setText("totalEnrolled", "0");
    setText("totalStudents", "0");
    setText("totalNeverStarted", "0");
    setText("totalAbandoned", "0");
    setText("totalCompleted", "0");
    setText("completionRate", "0%");
    setText("abandonedRate", "0%");
    setText("neverStartedRate", "0%");
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

  setText("totalEnrolled", total);
  setText("totalStudents", total);
  setText("totalNeverStarted", unresponsive);
  setText("totalAbandoned", incomplete);
  setText("totalCompleted", completed);

  const unresponsiveRate = total > 0 ? (unresponsive / total) * 100 : 0;
  const incompleteRate = total > 0 ? (incomplete / total) * 100 : 0;
  const completionRate = total > 0 ? (completed / total) * 100 : 0;

  setWidth("funnel-fill-TotalEnrolled", 100);
  setWidth("funnel-fill-Unresponsive", unresponsiveRate);
  setWidth("funnel-fill-InProgress", incompleteRate);
  setWidth("funnel-fill-Completed", completionRate);

  setText("completionRate", `${completionRate.toFixed(1)}%`);
  setText("abandonedRate", `${incompleteRate.toFixed(1)}%`);
  setText("neverStartedRate", `${unresponsive.toFixed(1)}%`);
}
