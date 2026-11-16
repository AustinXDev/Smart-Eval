import { escapeHTML } from "../../../services/escape.js";
import { fetchAllSets, fetchAllQuestions } from "../api/api.js";
import { countUp } from "../../shared/count_up.js";
import { setAllQuestions, getAllQuestions } from "./questionState.js";
import {
  showTableLoader,
  hideTableLoader,
  showSetCardLoader,
  hideSetCardLoader,
} from "../../shared/Loader.js";

// ── Question set cards ───────────────────────────────
export async function loadQuestionSetList() {
  const container = document.querySelector(".cards-container");
  if (!container) return;

  showSetCardLoader(".cards-container");

  try {
    const res = await fetchAllSets();

    if (res.status !== "success") {
      throw new Error(res.message || "Failed to load question sets");
    }

    const sets = res.data ?? [];

    setAllQuestions(sets);
    renderSetKpis(res.stats, sets);

    // Keep any search the user typed while the list was loading.
    const query =
      document.getElementById("questionnaireSearch")?.value.trim().toLowerCase() ?? "";
    renderSetList(filterSets(sets, query), { filtered: query !== "" });
  } catch (err) {
    console.error("Failed to load question sets", err);

    ["kpi-sets", "kpi-in-use", "kpi-questions"].forEach((id) => setText(id, "—"));
    setText("table-count", "0");

    container.innerHTML = stateBlock({
      icon: "fa-circle-exclamation",
      tone: "error",
      title: "Questionnaire sets could not be loaded",
      text: "Check your connection and try again.",
      button:
        '<button type="button" class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]" onclick="location.reload()"><i class="fas fa-rotate-right text-[11px]" aria-hidden="true"></i>Try again</button>',
    });
  }
}

function setText(id, value) {
  const node = document.getElementById(id);
  if (node) node.textContent = value;
}

// KPI cards. Figures come from QuestionRepositories::getSetStats() (the
// "stats" key of get_sets.php); a server without it falls back to counting
// the sets list it returned.
function renderSetKpis(stats, sets = []) {
  const fromServer = stats && typeof stats === "object";
  const total = fromServer ? Number(stats.total_sets) || 0 : sets.length;
  const inUse = fromServer
    ? Number(stats.sets_in_use) || 0
    : sets.filter((s) => s.active_evaluation_using_set).length;
  const questions = fromServer
    ? Number(stats.total_questions) || 0
    : sets.reduce((sum, s) => sum + (Number(s.total_questions) || 0), 0);

  countUp("kpi-sets", total);
  countUp("kpi-in-use", inUse);
  setText("kpi-in-use-share", total > 0 ? `${((inUse / total) * 100).toFixed(1)}%` : "");
  countUp("kpi-questions", questions);
  setText("kpi-questions-share", total > 0 ? `${(questions / total).toFixed(1)} per set` : "");
}

function filterSets(sets, query) {
  return query
    ? sets.filter((s) => String(s.set_name ?? "").toLowerCase().includes(query))
    : sets;
}

export function renderSetList(sets, { filtered = false } = {}) {
  const container = document.querySelector(".cards-container");
  if (!container) return;

  setText("table-count", String((sets ?? []).length));

  if (!sets || sets.length === 0) {
    container.innerHTML = filtered ? renderNoMatchState() : renderEmptyState();
    return;
  }

  container.innerHTML = sets.map(renderSetCard).join("");
}

function stateBlock({ icon, tone = "neutral", title, text, button = "" }) {
  const iconTone =
    tone === "error" ? "bg-rose-50 text-rose-500" : "bg-slate-50 text-slate-400";
  return `
    <div class="col-span-full flex flex-col items-center justify-center py-12 text-center">
      <div class="flex h-12 w-12 items-center justify-center rounded-xl ${iconTone}">
        <i class="fa-solid ${icon} text-lg" aria-hidden="true"></i>
      </div>
      <p class="mt-3 text-sm font-semibold text-slate-700">${title}</p>
      <p class="mt-1 max-w-sm text-xs text-slate-500">${text}</p>
      ${button}
    </div>`;
}

function renderEmptyState() {
  return stateBlock({
    icon: "fa-clipboard-question",
    title: "No questionnaire sets yet",
    text: "Create your first questionnaire set to start adding evaluation questions.",
    button:
      '<button type="button" class="addSet mt-4 inline-flex h-9 items-center gap-2 rounded-xl bg-[#6010FF] px-4 text-xs font-semibold text-white shadow-sm transition hover:brightness-110"><i class="fas fa-plus text-[11px]" aria-hidden="true"></i>Add New Set</button>',
  });
}

function renderNoMatchState() {
  return stateBlock({
    icon: "fa-magnifying-glass",
    title: "No sets match your search",
    text: "Try a different set name.",
    button:
      '<button type="button" data-clear-set-search class="mt-4 inline-flex h-9 items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 text-xs font-semibold text-slate-600 shadow-sm transition hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF]"><i class="fas fa-filter-circle-xmark text-[11px]" aria-hidden="true"></i>Clear search</button>',
  });
}

export function initQuestionSetSearch(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;

  const clearBtn = document.getElementById("clear-search");
  let debounceTimer;

  const apply = () => {
    const query = input.value.trim().toLowerCase();
    renderSetList(filterSets(getAllQuestions() ?? [], query), { filtered: query !== "" });
  };

  const syncClearButton = () => {
    const hasValue = input.value.length > 0;
    clearBtn?.classList.toggle("hidden", !hasValue);
    clearBtn?.classList.toggle("inline-flex", hasValue);
  };

  const clear = () => {
    input.value = "";
    syncClearButton();
    apply();
  };

  input.addEventListener("input", () => {
    syncClearButton();
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(apply, 200);
  });

  input.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && input.value) clear();
  });

  clearBtn?.addEventListener("click", () => {
    clear();
    input.focus();
  });

  document.addEventListener("click", (event) => {
    if (event.target.closest("[data-clear-set-search]")) clear();
  });
}

function renderSetCard(s) {
  const inUse = Boolean(s.active_evaluation_using_set);
  const timesUsed = Number(s.total_periods_using_set) || 0;
  const questions = Number(s.total_questions) || 0;
  const id = escapeHTML(String(s.set_id));
  const name = escapeHTML(s.set_name);

  const created = new Date(String(s.created_at ?? "").replace(" ", "T"));
  const createdDate = Number.isNaN(created.getTime())
    ? "—"
    : created.toLocaleDateString(undefined, { year: "numeric", month: "short", day: "numeric" });

  const badge = inUse
    ? '<span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/15" title="An active evaluation period uses this set"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>In use</span>'
    : `<span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-slate-50 px-2.5 py-1 text-[11px] font-semibold text-slate-600 ring-1 ring-inset ring-slate-500/10"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>Used ${timesUsed} time${timesUsed === 1 ? "" : "s"}</span>`;

  return `
    <article class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-[#6010FF]/[0.04] via-white to-white p-4 shadow-sm transition duration-200 hover:border-[#6010FF]/30 hover:shadow-md sm:p-5">
      <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-[#6010FF] to-[#6010FF]/10"></span>

      <div class="flex items-start justify-between gap-3">
        <div class="flex min-w-0 items-start gap-3">
          <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#6010FF]/10 text-[#6010FF] ring-1 ring-[#6010FF]/15">
            <i class="fas fa-clipboard-list text-sm" aria-hidden="true"></i>
          </span>
          <div class="min-w-0">
            <h4 class="truncate text-sm font-semibold text-slate-900" title="${name}">${name}</h4>
            <p class="mt-0.5 text-xs text-slate-500">Question set</p>
          </div>
        </div>
        ${badge}
      </div>

      <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4">
        <div>
          <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Questions</dt>
          <dd class="mt-1 text-xl font-bold tabular-nums tracking-tight text-slate-900">${questions}</dd>
        </div>
        <div>
          <dt class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Created</dt>
          <dd class="mt-1.5 text-sm font-medium tabular-nums text-slate-700">${createdDate}</dd>
        </div>
      </dl>

      <div class="mt-4 flex items-center gap-2 border-t border-slate-100 pt-4">
        <button type="button" data-set-id="${id}"
          class="manageQuestion inline-flex h-9 flex-1 items-center justify-center gap-2 rounded-xl bg-[#6010FF] px-4 text-xs font-semibold text-white shadow-sm transition hover:brightness-110 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF] focus-visible:ring-offset-2">
          <i class="fas fa-list-check text-[11px]" aria-hidden="true"></i>
          Manage questions
        </button>
        <button type="button" data-set-id="${id}" data-set-name="${name}" title="Edit set" aria-label="Edit set: ${name}"
          class="editSet flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 hover:border-[#6010FF]/30 hover:bg-[#6010FF]/5 hover:text-[#6010FF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]">
          <i class="fas fa-pen text-xs" aria-hidden="true"></i>
        </button>
        <button type="button" data-set-id="${id}" data-set-name="${name}" title="Delete set" aria-label="Delete set: ${name}"
          class="deleteSet flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-500 transition-colors duration-150 hover:border-rose-200 hover:bg-rose-50 hover:text-rose-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]">
          <i class="fas fa-trash-alt text-xs" aria-hidden="true"></i>
        </button>
      </div>
    </article>
  `;
}

// ── Question rows for the manage-questions modal ─────
export async function loadQuestions(setId) {
  const listBody = document.getElementById("questionList");
  if (!listBody) return;

  showTableLoader("#questionTable", "Loading question records...");

  try {
    const res = await fetchAllQuestions(setId);

    if (res.status !== "success") {
      console.error("Failed to load questions", res);
      return;
    }

    const questions = res.data ?? [];

    if (questions.length === 0) {
      listBody.innerHTML = `
        <tr>
          <td colspan="3" class="px-5 py-12">
            <div class="
              flex flex-col
              items-center
              justify-center
              text-center
            ">

              <div class="
                mb-3
                flex h-11 w-11
                items-center justify-center
                rounded-xl
                bg-slate-50
                text-slate-400
              ">
                <i class="
                  fa-solid
                  fa-circle-question
                  text-sm
                "></i>
              </div>

              <p class="
                text-sm
                font-semibold
                text-slate-700
              ">
                No questions found
              </p>

              <p class="
                mt-1
                text-xs
                text-slate-400
              ">
                Add a question to this questionnaire set.
              </p>

            </div>
          </td>
        </tr>
      `;

      return;
    }

    listBody.innerHTML = questions.map(renderQuestionRow).join("");
  } catch (err) {
    console.error("Failed to load questions", err);

    listBody.innerHTML = `
      <tr>
        <td colspan="3" class="px-5 py-12">

          <div class="
            flex flex-col
            items-center
            justify-center
            text-center
          ">

            <div class="
              mb-3
              flex h-11 w-11
              items-center justify-center
              rounded-xl
              bg-red-50
              text-red-500
            ">
              <i class="
                fa-solid
                fa-triangle-exclamation
                text-sm
              "></i>
            </div>

            <p class="
              text-sm
              font-semibold
              text-slate-700
            ">
              Unable to load questions
            </p>

            <p class="
              mt-1
              text-xs
              text-slate-400
            ">
              Please try again.
            </p>

            <button
              type="button"
              class="
                mt-4
                rounded-lg
                bg-violet-600
                px-4 py-2
                text-xs
                font-semibold
                text-white
                transition
                hover:bg-violet-700
              "
              onclick="loadQuestions('${setId}')"
            >
              Try Again
            </button>

          </div>

        </td>
      </tr>
    `;
  } finally {
    hideTableLoader("#questionTable");
  }
}

function renderQuestionRow(q) {
  return `
    <tr
      class="
        group
        border-b border-slate-100
        transition-colors duration-150
        hover:bg-slate-50/70
      "
    >
      <!-- Question -->
      <td class="px-5 py-3.5 align-middle">
        <div class="flex items-start gap-3">

          <!-- Question indicator -->
          <div
            class="
              flex h-8 w-8 shrink-0
              items-center justify-center
              rounded-lg
              bg-violet-50
              text-violet-600
            "
          >
            <i class="fa-solid fa-circle-question text-xs"></i>
          </div>

          <!-- Question text -->
          <div class="min-w-0 max-w-[520px]">
            <p
              class="
                font-medium
                leading-5
                text-slate-700
                whitespace-normal
              "
            >
              ${escapeHTML(q.question_text)}
            </p>

            <p class="mt-0.5 text-[11px] text-slate-400">
              Question #${q.question_id}
            </p>
          </div>

        </div>
      </td>


      <!-- Category -->
      <td class="px-5 py-3.5 align-middle flex">

        <span
          class="
            inline-flex items-center gap-1.5
            rounded-lg
            bg-slate-50
            px-2.5 py-1
            text-xs
            font-medium
            text-slate-600
            border border-slate-100
          "
        >
          <span
            class="h-1.5 w-1.5 rounded-full bg-violet-500"
          ></span>

          ${escapeHTML(q.category)}
        </span>

      </td>


      <!-- Actions -->
      <td class="px-5 py-3.5 align-middle text-right">

        <div class="flex items-center justify-end gap-1.5">

          <!-- Edit -->
          <button
            type="button"
            data-question-id="${q.question_id}"
            data-set-id="${q.set_id}"
            data-question-text="${escapeHTML(q.question_text)}"
            data-category="${escapeHTML(q.category)}"
            class="
              editQuestion
              flex h-8 w-8
              items-center justify-center
              rounded-lg
              border border-slate-200
              bg-white
              text-slate-500
              shadow-sm
              transition-all duration-200

              hover:-translate-y-0.5
              hover:border-violet-200
              hover:bg-violet-50
              hover:text-violet-600
              hover:shadow-sm

              focus:outline-none
              focus:ring-2
              focus:ring-violet-500/20

              cursor-pointer
            "
            title="Edit Question"
            aria-label="Edit Question"
          >
            <i class="fa-solid fa-pen text-[11px]"></i>
          </button>


          <!-- Delete -->
          <button
            type="button"
            data-question-id="${q.question_id}"
            class="
              deleteQuestion
              flex h-8 w-8
              items-center justify-center
              rounded-lg
              border border-slate-200
              bg-white
              text-slate-500
              shadow-sm
              transition-all duration-200

              hover:-translate-y-0.5
              hover:border-red-200
              hover:bg-red-50
              hover:text-red-600
              hover:shadow-sm

              focus:outline-none
              focus:ring-2
              focus:ring-red-500/20

              cursor-pointer
            "
            title="Delete Question"
            aria-label="Delete Question"
          >
            <i class="fa-solid fa-trash-can text-[11px]"></i>
          </button>

        </div>

      </td>

    </tr>
  `;
}
