import { countUp } from "../../../shared/count_up.js";

/**
 * Evaluation Overview KPI cards, from the backend `overview` object:
 *   { teachers_evaluated, total_submissions, overall_average, overall_rating }
 * Numbers count up from the value currently shown (shared countUp helper).
 */
export function renderAnalyticsOverview(overview) {
  const container = document.getElementById("analyticsOverview");

  if (!container) return;

  const teachers = document.getElementById("teachersEvaluated");
  const submissions = document.getElementById("totalSubmissions");
  const average = document.getElementById("overallAverage");
  const rating = document.getElementById("overallRating");

  const data = overview ?? {};

  countUp(teachers, Number(data.teachers_evaluated ?? 0));

  countUp(submissions, Number(data.total_submissions ?? 0));

  if (average) {
    if (data.overall_average == null) {
      // Reset so the next real value counts up from 0, not from a stale number.
      average.dataset.countValue = "0";
      average.textContent = "—";
    } else {
      countUp(average, Number(data.overall_average), { decimals: 2 });
    }
  }

  if (rating) {
    rating.textContent = data.overall_rating ?? "No Rating";
  }
}
