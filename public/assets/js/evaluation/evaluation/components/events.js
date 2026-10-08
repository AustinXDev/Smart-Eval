import {
  getState,
  getCurrentStudent,
  getCurrentTeacher,
  setAnswer,
  setSavedAnswers,
  getSavedAnswers,
  setComment,
  setSavedComment,
  getComment,
  setCurrentTeacher,
  setSaving,
  getAnswers,
} from "./state.js";

import {
  updateRatingSelection,
  renderQuestionProgress,
  renderTeacher,
  renderTeacherProgress,
  renderQuestions,
  updateNavigation,
  setNextButtonLoading,
  showSuccess,
  showReviewModal,
} from "./ui.js";

import { saveEvaluation } from "../api/api.js";

export function initEvents() {
  /*
   * Rating selection
   */

  document.addEventListener("click", handleRatingClick);

  /*
   * Previous teacher
   */

  document
    .getElementById("previousBtn")
    ?.addEventListener("click", handlePrevious);

  /*
   * Next teacher
   */

  document.getElementById("nextBtn")?.addEventListener("click", handleNext);

  /*
   * Student dropdown
   */

  document
    .getElementById("studentMenuButton")
    ?.addEventListener("click", handleStudentMenu);

  /**
   * Handle comment input
   */
  document
    .getElementById("teacherComment")
    ?.addEventListener("input", handleCommentInput);
}

function handleRatingClick(event) {
  const button = event.target.closest(".rating-option");

  if (!button) return;

  const questionId = button.dataset.questionId;

  const score = Number(button.dataset.score);

  setAnswer(questionId, score);

  updateRatingSelection(questionId, score);

  renderQuestionProgress();
}

async function handleNext() {
  const state = getState();

  /*
   * Prevent moving forward while saving.
   */

  if (state.isSaving) return;

  const currentTeacher = getCurrentTeacher();

  if (!currentTeacher) return;

  const teacherId = currentTeacher.teacher_id;

  /*
   * Require every question to be answered.
   */

  const currentAnswered = state.answers?.[teacherId] ?? {};

  const currentComment = state.comments?.[teacherId] ?? "";

  const totalQuestions = state.questions.length + 1;

  const answeredQuestions =
    Object.keys(currentAnswered).length +
    (currentComment.trim() !== "" ? 1 : 0);

  if (answeredQuestions < totalQuestions) {
    alert(`Please answer all ${totalQuestions} questions before continuing.`);

    return;
  }

  /*
   * If this is the final teacher,
   * submit the final evaluation.
   */

  if (state.currentTeacherIndex >= state.teachers.length - 1) {
    const review = showReviewModal();

    if (!review) return;

    review.modal.addEventListener(
      "confirm",
      async () => {
        review.close();

        await submitCurrentTeacher();
      },
      { once: true },
    );

    return;
  }

  /*
   * Save current teacher first.
   */

  await submitCurrentTeacher({
    moveNext: true,
  });
}

function handleCommentInput(event) {
  setComment(event.target.value);

  renderQuestionProgress();
}

async function submitCurrentTeacher({ moveNext = false } = {}) {
  const state = getState();

  const teacher = getCurrentTeacher();

  if (!teacher) return;

  const teacherId = Number(teacher.teacher_id);

  const isSubmitted = Number(teacher.is_submitted) === 1;

  const answers = getAnswers();
  const comment = getComment();

  setSaving(true);

  setNextButtonLoading(true);

  try {
    await saveEvaluation({
      teacherId: teacher.teacher_id ?? 0,
      evalId: teacher.eval_id ?? 0,

      answers,
      comment,
      update: isSubmitted,
    });

    teacher.is_submitted = 1;

    /*
     * Update local saved.
     */
    setSavedAnswers(teacherId, answers);
    setSavedComment(teacherId, comment);

    showSuccess(
      isSubmitted
        ? "Evaluation changes saved successfully."
        : "Evaluation saved successfully.",
    );

    /*
     * Move to next teacher.
     */

    if (moveNext) {
      setCurrentTeacher(state.currentTeacherIndex + 1);

      renderTeacher();

      renderTeacherProgress();

      renderQuestions();

      renderQuestionProgress();

      updateNavigation();

      document.getElementById("questionsContainer")?.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    } else {
      /*
       * Final submission.
       *
       */
      window.location.href = "evaluation-done";
    }
  } catch (error) {
    console.error("Evaluation save error:", error);

    alert(error.message || "Unable to save the evaluation.");
  } finally {
    setSaving(false);

    setNextButtonLoading(false);

    updateNavigation();
  }
}

function handlePrevious() {
  const state = getState();

  if (state.currentTeacherIndex <= 0) {
    return;
  }

  setCurrentTeacher(state.currentTeacherIndex - 1);

  /*
   * Reload previous teacher's questions.
   *
   * If your backend stores draft answers,
   * load them here before rendering.
   */

  renderTeacher();

  renderTeacherProgress();

  renderQuestions();

  renderQuestionProgress();

  updateNavigation();

  setNextButtonLoading(false);

  document.getElementById("questionsContainer")?.scrollTo({
    top: 0,
    behavior: "smooth",
  });
}

function handleStudentMenu(event) {
  const button = event.currentTarget;

  const expanded = button.getAttribute("aria-expanded") === "true";

  button.setAttribute("aria-expanded", String(!expanded));

  /*
   * Add your existing dropdown implementation
   * here if your current project already has one.
   */
}
