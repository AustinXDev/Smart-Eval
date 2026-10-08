import { fetchEvaluationSummary } from "./api/api.js";
import {
  showLoadingDone,
  showError,
  showEmptyState,
  renderTeachersList,
} from "./components/evaluation_view.js";
import { generateEvaluationPNG } from "./png/png_generator.js";

document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetchEvaluationSummary();

    const data = response.data;

    console.log(data);

    if (data.status === "error") {
      showError(data.error);
      return;
    }

    showLoadingDone();

    if (data.total_evaluated === 0) {
      showEmptyState(data.period_name);
      return;
    }

    renderTeachersList(data);

    const btn = document.getElementById("download");
    if (btn) {
      btn.addEventListener("click", () => generateEvaluationPNG(data));
    }
  } catch (err) {
    console.error("Error:", err);
    showError("Failed to load evaluation data. Please try again.");
  }
});
