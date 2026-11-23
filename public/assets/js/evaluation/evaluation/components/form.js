/**
 * The statement cards and the comment box. The markup is rendered once by
 * the page; switching teacher only re-applies that teacher's answers.
 */
export function createForm(store, { onChange }) {
  const cards = [...document.querySelectorAll("#questionList .q-card")];
  const commentCard = document.getElementById("commentCard");
  const textarea = document.getElementById("commentInput");
  const counter = document.getElementById("commentCount");
  const help = document.getElementById("commentHelp");
  const commentStatus = document.getElementById("commentStatus");
  const starters = [...document.querySelectorAll("[data-starter]")];
  const itemsDone = document.getElementById("itemsDone");
  const jumpButton = document.getElementById("jumpBtn");
  const { min, max } = { min: 10, max: Number(textarea.maxLength) || 500 };

  const teacherId = () => store.teachers[store.current].id;
  const reduceMotion = () =>
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  function refresh() {
    const id = teacherId();
    cards.forEach((card) => {
      const qid = card.dataset.questionId;
      card.dataset.answered = qid
        ? String(!!store.getAnswer(id, qid))
        : String(store.commentValid(store.getComment(id)));
    });

    const text = store.getComment(id);
    const length = text.trim().length;
    counter.textContent = text.length;
    const ok = store.commentValid(text);
    help.textContent =
      length === 0
        ? `At least ${min} characters.`
        : length < min
          ? `${min - length} more character${min - length === 1 ? "" : "s"} needed.`
          : length > max
            ? `Please shorten your comment to ${max} characters.`
            : "Feedback meets length requirement";
    help.className = ok
      ? "font-medium text-emerald-700"
      : length > 0
        ? "text-amber-700"
        : "text-[#64748B]";

    commentStatus.className = `inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3 py-1 text-xs font-semibold ${
      ok
        ? "border-emerald-200 bg-emerald-50 text-emerald-700"
        : "border-[#E2E8F0] bg-[#F8FAFC] text-[#64748B]"
    }`;
    commentStatus.innerHTML = ok
      ? '<i class="fa-solid fa-check text-[10px]" aria-hidden="true"></i>Completed'
      : "Pending";

    // Only the starter whose text is currently in the box shows as selected.
    starters.forEach((chip) => {
      chip.setAttribute(
        "aria-pressed",
        String(text.trim() === chip.dataset.starter),
      );
    });

    const p = store.progress(id);
    itemsDone.textContent = `${p.done} / ${p.total} Completed`;
    jumpButton.hidden = p.complete;
    onChange();
  }

  /** Puts the current teacher's saved/draft answers into the form. */
  function load() {
    const id = teacherId();
    cards.forEach((card) => {
      card.classList.remove("is-flagged");
      const qid = card.dataset.questionId;
      if (!qid) return;
      const score = store.getAnswer(id, qid);
      card.querySelectorAll('input[type="radio"]').forEach((input) => {
        input.checked = Number(input.value) === Number(score);
      });
    });
    textarea.value = store.getComment(id);
    refresh();
  }

  /** Scrolls to an item (0-based) and focuses its first control. */
  function goToItem(index, { flag = false } = {}) {
    const card = cards[index];
    if (!card) return;
    card.scrollIntoView({
      behavior: reduceMotion() ? "auto" : "smooth",
      block: "center",
    });
    if (flag) {
      card.classList.add("is-flagged");
      setTimeout(() => card.classList.remove("is-flagged"), 1600);
    }
    const target =
      card === commentCard
        ? textarea
        : (card.querySelector("input:checked") ?? card.querySelector("input"));
    target?.focus({ preventScroll: true });
  }

  function jumpToOpen(options) {
    const index = store.firstOpenItem(teacherId());
    if (index !== -1) goToItem(index, options);
  }

  document
    .getElementById("questionList")
    .addEventListener("change", (event) => {
      const input = event.target;
      if (input.type !== "radio") return;
      const card = input.closest(".q-card");
      store.setAnswer(teacherId(), card.dataset.questionId, input.value);
      card.classList.remove("is-flagged");
      refresh();
    });

  textarea.addEventListener("input", () => {
    store.setComment(teacherId(), textarea.value);
    refresh();
  });

  starters.forEach((chip) => {
    // Picking a starter replaces whatever is in the box with that starter's text.
    chip.addEventListener("click", () => {
      const value = chip.dataset.starter.slice(0, max);
      textarea.value = value;
      store.setComment(teacherId(), value);
      refresh();
      textarea.focus();
      textarea.setSelectionRange(value.length, value.length);
    });
  });

  jumpButton.addEventListener("click", () => jumpToOpen());

  return { load, goToItem, jumpToOpen, refresh };
}
