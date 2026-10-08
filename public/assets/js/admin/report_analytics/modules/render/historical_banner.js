import { state } from "../state.js";

export function renderHistoricalBanner() {
  const params = new URLSearchParams(window.location.search);
  const periodId = params.get("period_id");
  const banner = document.getElementById("historical-banner");

  if (!periodId || periodId === "null") {
    banner?.classList.add("hidden");
    return;
  }

  const label = document.getElementById("banner-period-label");
  const labelMobile = document.getElementById("banner-period-label-mobile");
  const text = state.lastData?.meta
    ? `${state.lastData.meta.academic_year} — ${state.lastData.meta.semester}`
    : "Historical Period";

  if (label) label.textContent = text;
  if (labelMobile) labelMobile.textContent = text;

  banner?.classList.remove("hidden");
}
