import { fetchOverview } from "../api/api.js";
import { byId, announce, setOptions } from "./ui.js";
import * as render from "./archiveRender.js";
import { countUp } from "../../shared/count_up.js";

let overviewRequest = 0;
export async function loadOverview() {
  const current = ++overviewRequest;
  byId("archive-overview").setAttribute("aria-busy", "true");
  try {
    const { data } = await fetchOverview();
    if (current !== overviewRequest) return;
    for (const element of document.querySelectorAll("[data-metric]")) {
      const value = data.metrics[element.dataset.metric];
      if (value === null || !Number.isFinite(Number(value))) {
        element.dataset.countValue = "0";
        element.textContent = value === null ? "Unavailable" : render.count(value);
      } else {
        // Same count-up as the other management pages.
        countUp(element, Number(value));
      }
    }
    setOptions("archive-year", data.filters.years, String, String);
    setOptions("archive-semester", data.filters.semesters, String, String);
    setOptions(
      "logs-period_id",
      data.filters.periods,
      (p) =>
        `#${p.period_id} · ${p.academic_year} · ${render.department(p.target_dept)}`,
      (p) => String(p.period_id),
    );
  } catch (error) {
    if (current !== overviewRequest) return;
    document.querySelectorAll("[data-metric]").forEach((el) => {
      el.textContent = "Unavailable";
    });
    announce(error.message + " Use Refresh to try again.", true);
  } finally {
    if (current === overviewRequest)
      byId("archive-overview").setAttribute("aria-busy", "false");
  }
}
