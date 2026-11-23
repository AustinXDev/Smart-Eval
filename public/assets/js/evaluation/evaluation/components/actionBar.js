/**
 * Fixed bottom bar: status line, one dot per item, Previous, and Next /
 * Review & Submit All. Next stays aria-disabled (but focusable, so it can
 * explain itself) until the current teacher is complete.
 */
export function createActionBar(store, { onDot }) {
  const status = document.getElementById("barStatus");
  const barDot = document.getElementById("barDot");
  const dots = document.getElementById("barDots");
  const prev = document.getElementById("prevBtn");
  const next = document.getElementById("nextBtn");
  const nextText = document.getElementById("nextText");
  const nextIcon = document.getElementById("nextIcon");
  const spinner = document.getElementById("nextSpinner");

  let wasReady = false;
  let busy = false;

  dots.addEventListener("click", (event) => {
    const dot = event.target.closest("[data-dot]");
    if (dot) onDot(Number(dot.dataset.dot));
  });

  /** What Next should do right now: { mode, enabled, label }. */
  function nextState() {
    const t = store.teachers[store.current];
    const p = store.progress(t.id);
    const last = store.current === store.teachers.length - 1;

    if (!p.complete) {
      return { mode: "incomplete", enabled: false, label: `${p.left} item${p.left === 1 ? "" : "s"} left` };
    }
    if (!last) return { mode: "next", enabled: true, label: "Submit & Next Teacher" };

    const pending = store.teachers.findIndex((x) => !store.progress(x.id).complete);
    if (pending !== -1) {
      return { mode: "pending", enabled: false, label: `Finish Teacher ${pending + 1} first`, pending };
    }
    return { mode: "review", enabled: true, label: "Review & Submit All" };
  }

  function update() {
    const t = store.teachers[store.current];
    const p = store.progress(t.id);
    const total = store.teachers.length;

    status.textContent = `${p.done} of ${p.total} completed · Teacher ${store.current + 1} of ${total}`;
    barDot.className = `h-2 w-2 shrink-0 rounded-full ${p.complete ? "bg-emerald-500" : "bg-amber-500"}`;

    // One dot per item; the comment is the wider pill at the end.
    dots.innerHTML = Array.from({ length: p.total }, (_, i) => {
      const isComment = i === p.total - 1;
      const done = isComment ? p.commentOk : !!store.getAnswer(t.id, store.questions[i].id);
      const name = isComment ? "Comment" : `Statement ${i + 1}`;
      return `<li><button type="button" data-dot="${i}" aria-label="${name}, ${done ? "answered" : "not answered"}"
        class="flex h-6 ${isComment ? "w-8" : "w-4"} items-center justify-center rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4F46E5]">
        <span class="block h-2 ${isComment ? "w-6" : "w-2"} rounded-full ${done ? "bg-[#4F46E5]" : "bg-[#E2E8F0]"}"></span>
      </button></li>`;
    }).join("");

    prev.disabled = busy || store.current === 0;

    const state = nextState();
    next.setAttribute("aria-disabled", String(!state.enabled || busy));
    if (!busy) nextText.textContent = state.label;
    // Use the hidden attribute: Font Awesome's display rule overrides Tailwind's "hidden" class.
    nextIcon.className = `fa-solid ${state.mode === "review" ? "fa-clipboard-check" : "fa-arrow-right"} text-xs`;
    nextIcon.hidden = busy || !state.enabled;

    if (state.enabled && !wasReady) {
      next.classList.remove("is-ready-pulse");
      void next.offsetWidth;
      next.classList.add("is-ready-pulse");
    }
    wasReady = state.enabled;
    return state;
  }

  function setBusy(value, label = "Saving…") {
    busy = value;
    spinner.classList.toggle("hidden", !value);
    if (value) nextText.textContent = label;
    update();
  }

  return { update, nextState, setBusy, get busy() { return busy; } };
}
