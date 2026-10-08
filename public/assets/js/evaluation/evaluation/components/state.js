let state = {
  studentId: window.SMART_EVAL.studentId,
  teachers: [],
  currentTeacherIndex: 0,

  questions: [],
  answers: {},
  comments: {},

  savedAnswers: {},
  savedComments: {},

  isLoading: false,
  isSaving: false,
};

export function getState() {
  return state;
}

export function setTeachers(teachers) {
  state.teachers = teachers;
}

export function setQuestions(questions) {
  state.questions = questions;
}

export function setIsSubmitted(isSubmitted) {
  state.isSubmitted = isSubmitted;
}

export function setLoading(value) {
  state.isLoading = value;
}

export function setSaving(value) {
  state.isSaving = value;
}

export function setCurrentTeacher(index) {
  state.currentTeacherIndex = index;
}

export function getCurrentTeacher() {
  return state.teachers[state.currentTeacherIndex] ?? null;
}

export function setAnswer(questionId, score) {
  const state = getState();
  const teacher = getCurrentTeacher();

  if (!teacher) return;

  const teacherId = teacher.teacher_id;

  if (!state.answers[teacherId]) {
    state.answers[teacherId] = {};
  }

  state.answers[teacherId][questionId] = Number(score);
}

export function getCurrentStudent() {
  return state.studentId;
}

export function getAnswer(questionId) {
  const teacher = getCurrentTeacher();

  if (!teacher) return null;

  const teacherId = teacher.teacher_id;

  return state.answers?.[teacherId]?.[questionId] ?? null;
}

export function getAnswers() {
  const teacher = getCurrentTeacher();

  if (!teacher) return {};

  const teacherId = teacher.teacher_id;

  return {
    ...(state.answers?.[teacherId] ?? {}),
  };
}

export function clearAnswers() {
  state.answers = {};
}

export function setComment(comment) {
  const teacher = getCurrentTeacher();

  if (!teacher) return;

  state.comments[teacher.teacher_id] = comment;
}

export function getComment() {
  const teacher = getCurrentTeacher();

  if (!teacher) return "";

  return state.comments[teacher.teacher_id] ?? "";
}

export function saveTeacherComment(comment) {
  const state = getState();
  const teacher = getCurrentTeacher();

  if (!teacher) return;

  state.comments[teacher.teacher_id] = comment.trim();
  console.log("Successfully save comment.");
}

export function setSavedAnswers(teacherId, answers) {
  state.savedAnswers[teacherId] = {
    ...answers,
  };
}

export function getSavedAnswers(teacherId) {
  return state.savedAnswers?.[teacherId] ?? {};
}

export function setSavedComment(teacherId, comment) {
  state.savedComments[teacherId] = comment ?? "";
}

export function getSavedComment(teacherId) {
  return state.savedComments?.[teacherId] ?? "";
}

export function setExistingEvaluations(evaluations) {
  state.answers = {};
  state.comments = {};

  for (const [teacherId, evaluation] of Object.entries(evaluations)) {
    state.answers[teacherId] = {
      ...(evaluation.answers ?? {}),
    };

    state.comments[teacherId] = evaluation.comment ?? "";
  }
}
