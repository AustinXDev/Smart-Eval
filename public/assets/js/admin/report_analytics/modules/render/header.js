function setText(id, value) {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function setLiveBadgeState(el, { text, active }) {
  if (!el) return;
  el.textContent = text;

  if (active) {
    el.classList.add("text-green-700", "bg-green-50", "border-green-300");
    el.classList.remove("text-gray-500", "bg-gray-50", "border-gray-300");
  } else {
    el.classList.remove("text-green-700", "bg-green-50", "border-green-300");
    el.classList.add("text-gray-500", "bg-gray-50", "border-gray-300");
  }
}

export function renderHeaderInfo(data, meta) {
  const liveContainer = document.getElementById("status");

  const isClosed = Boolean(data.is_closed);

  if (!meta) {
    setText("evaluationPeriod", "N/A");
    setText("semester", "None");
    setLiveBadgeState(liveContainer, {
      text: "No Active Evaluation",
      active: false,
    });
    return;
  }

  setText("evaluationPeriod", meta.academic_year);
  setText("semester", meta.semester);

  if (isClosed) {
    setLiveBadgeState(liveContainer, {
      text: "Previous Evaluation",
      active: false,
    });
  }
}
