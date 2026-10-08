import { get } from "../../../services/http.js";

export async function fetchEvaluationSummary() {
  try {
    return await get(`evaluation/get_evaluation_summary.php`);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
