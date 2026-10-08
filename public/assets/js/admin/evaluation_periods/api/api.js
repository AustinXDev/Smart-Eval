import { post, get } from "../../../services/http.js";

export async function fetchAllQuestionSets() {
  try {
    return await get("questionnaires/get_set.php");
  } catch (error) {
    return {
      status: error,
      message: error.message,
      data: [],
    };
  }
}

export async function fetchCreatedPeriod(periodId) {
  try {
    return await get(`evaluation/get.php?period_id=${periodId}`);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
      data: [],
    };
  }
}

export async function getDashboardData() {
  try {
    return await get("evaluation/get_dashboard.php");
  } catch (error) {
    return {
      status: "error",
      message: error.message,
      all_periods: [],
      active_periods: [],
    };
  }
}

export async function forceActivePeriod(periodId) {
  try {
    return await post("evaluation/force_active.php", { period_id: periodId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
      period: [],
    };
  }
}

export async function deletePeriod(periodId) {
  try {
    return await post("evaluation/delete.php", { period_id: periodId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function forceClosePeriod(periodId) {
  try {
    return await post("evaluation/force_close.php", { period_id: periodId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function createPeriod(formData) {
  try {
    return await post("evaluation/create.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
      data: [],
    };
  }
}

export async function updatePeriod(formData) {
  try {
    return await post("evaluation/update.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
      data: [],
    };
  }
}
