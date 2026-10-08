import { fetchEvaluation } from "./api/api.js";

import {
  getState,
  setTeachers,
  setQuestions,
  setExistingEvaluations,
  setLoading,
} from "./components/state.js";

import {
  showLoading,
  hideLoading,
  renderTeacher,
  renderTeacherProgress,
  renderQuestions,
  renderQuestionProgress,
  updateNavigation,
  showError,
  showReviewModal,
} from "./components/ui.js";

import { initEvents } from "./components/events.js";

window.addEventListener("pageshow", (event) => {
  if (event.persisted) {
    console.log("Reload");
    window.location.reload();
  }
});

document.addEventListener("DOMContentLoaded", async () => {
  initEvents();

  await initializeEvaluation();
});

async function initializeEvaluation() {
  setLoading(true);

  showLoading();

  try {
    /*
     * DATABASE INTEGRATION:
     *
     * fetchEvaluation() calls:
     *
     * GET /api/evaluation/get.php
     *
     * PHP retrieves the authenticated student's
     * teachers and evaluation questions from MySQL.
     */

    const response = await fetchEvaluation();

    const teachers = response?.data?.teachers ?? [];

    const questions = response?.data?.questions ?? [];

    const evaluations = response?.data?.evaluations ?? [];

    if (!teachers.length) {
      throw new Error("There are no teachers available for evaluation.");
    }

    if (!questions.length) {
      throw new Error("There are no evaluation questions available.");
    }

    console.log(getState());

    setTeachers(teachers);

    setQuestions(questions);

    setExistingEvaluations(evaluations);

    /*
     * Initial UI
     */

    renderTeacher();

    renderTeacherProgress();

    renderQuestions();

    renderQuestionProgress();

    updateNavigation();
  } catch (error) {
    console.error("Evaluation initialization error:", error);

    hideLoading();

    showError(error.message || "Unable to load the evaluation.");
  } finally {
    setLoading(false);

    hideLoading();
  }
}
