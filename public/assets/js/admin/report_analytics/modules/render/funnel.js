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
    funnelContainer.innerHTML = `<p class="text-center text-gray-500 text-sm">No data available for this department</p>`;

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
