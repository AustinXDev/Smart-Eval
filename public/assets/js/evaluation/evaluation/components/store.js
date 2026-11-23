/**
 * Evaluation state for every teacher on the student's list.
 *
 * answers / comments hold what is on screen; saved holds what the server
 * last accepted, so "dirty" teachers can be re-saved before the final submit.
 */
export function createStore({ teachers, questions, saved, comment }) {
  const answers = {};
  const comments = {};
  const lastSaved = {};

  teachers.forEach((t) => {
    const s = saved?.[t.id] ?? {};
    answers[t.id] = { ...(s.answers ?? {}) };
    comments[t.id] = s.comment ?? "";
    lastSaved[t.id] = { answers: { ...(s.answers ?? {}) }, comment: s.comment ?? "" };
  });

  const itemTotal = questions.length + 1;

  const commentValid = (text) => {
    const length = text.trim().length;
    return length >= comment.min && length <= comment.max;
  };

  function progress(teacherId) {
    const a = answers[teacherId] ?? {};
    const answered = questions.filter((q) => a[q.id]).length;
    const commentOk = commentValid(comments[teacherId] ?? "");
    const done = answered + (commentOk ? 1 : 0);
    return { done, total: itemTotal, left: itemTotal - done, complete: done === itemTotal, commentOk };
  }

  /** Index of the first unanswered item (questions first, then the comment), or -1. */
  function firstOpenItem(teacherId) {
    const a = answers[teacherId] ?? {};
    const index = questions.findIndex((q) => !a[q.id]);
    if (index !== -1) return index;
    return commentValid(comments[teacherId] ?? "") ? -1 : questions.length;
  }

  function isDirty(teacherId) {
    const now = answers[teacherId];
    const was = lastSaved[teacherId];
    if ((comments[teacherId] ?? "").trim() !== (was.comment ?? "").trim()) return true;
    return questions.some((q) => Number(now[q.id] ?? 0) !== Number(was.answers[q.id] ?? 0));
  }

  function mean(teacherId) {
    const scores = questions.map((q) => Number(answers[teacherId]?.[q.id] ?? 0)).filter(Boolean);
    return scores.length ? scores.reduce((sum, s) => sum + s, 0) / scores.length : 0;
  }

  /** "progress" | "ready" | "submitted" */
  function status(teacher) {
    if (teacher.submitted && progress(teacher.id).complete && !isDirty(teacher.id)) return "submitted";
    return progress(teacher.id).complete ? "ready" : "progress";
  }

  return {
    teachers,
    questions,
    itemTotal,
    current: 0,
    commentValid,
    progress,
    firstOpenItem,
    isDirty,
    mean,
    status,
    getAnswer: (teacherId, questionId) => answers[teacherId]?.[questionId] ?? null,
    setAnswer(teacherId, questionId, score) {
      answers[teacherId][questionId] = Number(score);
    },
    getComment: (teacherId) => comments[teacherId] ?? "",
    setComment(teacherId, text) {
      comments[teacherId] = text;
    },
    payload: (teacherId) => ({ answers: { ...answers[teacherId] }, comment: (comments[teacherId] ?? "").trim() }),
    markSaved(teacher) {
      lastSaved[teacher.id] = { answers: { ...answers[teacher.id] }, comment: (comments[teacher.id] ?? "").trim() };
      teacher.submitted = true;
    },
    /** Teacher tabs after the first incomplete one are locked. */
    isUnlocked(index) {
      return teachers.slice(0, index).every((t) => progress(t.id).complete);
    },
    overall() {
      const done = teachers.reduce((sum, t) => sum + progress(t.id).done, 0);
      return done / (teachers.length * itemTotal);
    },
  };
}
