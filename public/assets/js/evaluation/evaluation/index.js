import { saveEvaluation } from "./api/api.js";
import { createStore } from "./components/store.js";
import { createForm } from "./components/form.js";
import { renderTabs, renderTeacherCard } from "./components/teacher.js";
import { createActionBar } from "./components/actionBar.js";
import { createReviewSheet } from "./components/reviewSheet.js";

// Coming back with the browser's Back button: reload so the server decides the page again.
window.addEventListener("pageshow", (event) => {
  if (event.persisted) window.location.reload();
});

function showError(message) {
  if (window.StatusModal) window.StatusModal.show("Failed", message, "error");
  else alert(message);
}

function init() {
  const page = window.EVAL_PAGE;
  const app = document.getElementById("evalApp");
  if (!page || !app) return;

  const store = createStore(page);
  let leaving = false;

  // Start on the first teacher that still needs work.
  const firstOpen = store.teachers.findIndex((t) => !t.submitted || !store.progress(t.id).complete);
  store.current = firstOpen === -1 ? 0 : firstOpen;

  const overallBar = document.getElementById("overallBar");
  const overallFill = document.getElementById("overallFill");

  function refreshChrome() {
    renderTabs(store, { onSelect: switchTo });
    renderTeacherCard(store);
    bar.update();
    const pct = Math.round(store.overall() * 100);
    overallFill.style.width = `${pct}%`;
    overallBar.setAttribute("aria-valuenow", String(pct));
  }

  const bar = createActionBar(store, { onDot: (i) => form.goToItem(i) });
  const form = createForm(store, { onChange: refreshChrome });

  // ---------- Saving ----------

  /** Saves one teacher if it's new or changed. Throws on failure. */
  async function saveTeacher(teacher) {
    if (teacher.submitted && !store.isDirty(teacher.id)) return;
    const { answers, comment } = store.payload(teacher.id);
    const result = await saveEvaluation({
      teacherId: teacher.id,
      evalId: teacher.evalId,
      answers,
      comment,
      update: teacher.submitted,
    });
    if (result?.status !== "success") {
      throw new Error(result?.message || "Unable to save the evaluation.");
    }
    store.markSaved(teacher);
  }

  /**
   * Shows another teacher. Never saves: answers are only sent when the student
   * clicks "Submit & Next Teacher", or "Submit all" in the review for the last one.
   * Edits made after switching stay on the page and are sent by "Submit all".
   */
  function switchTo(index) {
    if (bar.busy || index === store.current || index < 0 || index >= store.teachers.length) return;
    if (!store.isUnlocked(index)) return;

    store.current = index;
    form.load();
    window.scrollTo({ top: 0, behavior: window.matchMedia("(prefers-reduced-motion: reduce)").matches ? "auto" : "smooth" });
  }

  // ---------- Bottom bar ----------

  const sheet = createReviewSheet(store, page.ratings, {
    async onSubmit({ setBusy, close }) {
      const todo = store.teachers.filter((t) => !t.submitted || store.isDirty(t.id));
      setBusy(true);
      for (const [i, teacher] of todo.entries()) {
        if (todo.length > 1) setBusy(true, `Submitting ${i + 1} of ${todo.length}…`);
        try {
          await saveTeacher(teacher);
        } catch (error) {
          setBusy(false);
          close();
          store.current = store.teachers.indexOf(teacher);
          form.load();
          showError(`${teacher.name}: ${error.message}`);
          return;
        }
      }
      leaving = true;
      window.location.href = "evaluation-done";
    },
  });

  document.getElementById("prevBtn").addEventListener("click", () => switchTo(store.current - 1));

  const nextButton = document.getElementById("nextBtn");
  nextButton.addEventListener("click", async () => {
    if (bar.busy) return;
    const state = bar.nextState();

    if (state.mode === "incomplete") {
      form.jumpToOpen({ flag: true });
      return;
    }
    if (state.mode === "pending") {
      switchTo(state.pending);
      return;
    }
    if (state.mode === "review") {
      sheet.open(nextButton);
      return;
    }
    // "Submit & Next Teacher": send this teacher's answers, then move on.
    const teacher = store.teachers[store.current];
    bar.setBusy(true, "Submitting…");
    try {
      await saveTeacher(teacher);
    } catch (error) {
      bar.setBusy(false);
      showError(error.message);
      return;
    }
    bar.setBusy(false);
    switchTo(store.current + 1);
  });

  // Warn before leaving with changes that haven't been saved.
  window.addEventListener("beforeunload", (event) => {
    if (leaving) return;
    if (store.teachers.some((t) => store.isDirty(t.id))) {
      event.preventDefault();
      event.returnValue = "";
    }
  });

  // Ready: swap the loading placeholders for the form.
  document.getElementById("evalLoading").hidden = true;
  app.hidden = false;
  form.load();
}

document.addEventListener("DOMContentLoaded", init);
