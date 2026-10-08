import { fetchAllSets, fetchAllQuestions } from "../api/api.js";
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
      console.error("Failed to load question sets");
      return;
    }

    const sets = res.data ?? [];

    setAllQuestions(sets);

    renderSetList(sets);
  } catch (err) {
    console.error("Failed to load question sets", err);

    container.innerHTML = `
      <div class="
        col-span-full
        flex flex-col
        items-center
        justify-center
        rounded-2xl
        border border-red-100
        bg-white
        px-6 py-14
        text-center
      ">

        <div class="
          mb-4
          flex h-12 w-12
          items-center justify-center
          rounded-xl
          bg-red-50
          text-red-500
        ">
          <i class="fa-solid fa-triangle-exclamation"></i>
        </div>

        <p class="
          text-sm
          font-semibold
          text-slate-700
        ">
          Unable to load questionnaire sets
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
            inline-flex
            items-center
            gap-2
            rounded-lg
            bg-violet-600
            px-4 py-2
            text-xs
            font-semibold
            text-white
            transition
            hover:bg-violet-700
          "
          onclick="location.reload()"
        >
          <i class="fa-solid fa-plus"></i>
          Add Question Set
        </button>

      </div>
    `;
  }
}

export function renderSetList(sets) {
  const container = document.querySelector(".cards-container");
  if (!container) return;

  if (!sets || sets.length === 0) {
    container.innerHTML = renderEmptyState();
    return;
  }

  container.innerHTML = sets.map(renderSetCard).join("");
}

function renderEmptyState() {
  return `
    <div class="
      col-span-full
      flex flex-col
      items-center
      justify-center
      rounded-2xl
      border border-dashed
      border-slate-200
      bg-white
      px-6 py-16
      text-center
    ">

      <div class="
        flex h-14 w-14
        items-center justify-center
        rounded-2xl
        bg-violet-50
        text-violet-500
      ">
        <i class="fa-solid fa-clipboard-question text-lg"></i>
      </div>

      <h3 class="
        mt-4
        text-sm
        font-semibold
        text-slate-700
      ">
        No questionnaire sets yet
      </h3>

      <p class="
        mt-1
        max-w-sm
        text-xs
        leading-5
        text-slate-400
      ">
        Create your first questionnaire set to start adding
        evaluation questions.
      </p>

      <button
        type="button"
        class="
          addSet
          mt-5
          inline-flex
          items-center
          gap-2
          rounded-xl
          bg-violet-600
          px-4 py-2.5
          text-xs
          font-semibold
          text-white
          shadow-sm
          transition
          hover:bg-violet-700
          hover:shadow-md
        "
      >
        <i class="fa-solid fa-plus"></i>
        Add New Set
      </button>

    </div>
  `;
}

export function initQuestionSetSearch(inputId) {
  const input = document.getElementById(inputId);
  if (!input) return;

  let debounceTimer;

  input.addEventListener("input", () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      const query = input.value.trim().toLowerCase();
      const allSets = getAllQuestions();

      const filtered = query
        ? allSets.filter((s) => s.set_name.toLowerCase().includes(query))
        : allSets;

      renderSetList(filtered);
    }, 200);
  });
}

function renderSetCard(s) {
  const badge = s.active_evaluation_using_set
    ? "In Used"
    : `Used ${s.total_periods_using_set} time${s.total_periods_using_set !== 1 ? "s" : ""}`;

  const createdDate = new Date(s.created_at).toLocaleDateString("en-US", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });

  return `
    <div class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">
      <div class="absolute left-0 top-0 h-1 w-full bg-amber-400"></div>

      <div class="flex items-start justify-between gap-4">
        <div class="flex min-w-0 items-center gap-4">
          <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M13 3v5h5" />
            </svg>
          </div>

          <div class="min-w-0">
            <h2 class="truncate text-base font-bold text-slate-800">${s.set_name}</h2>
            <p class="mt-1 text-sm text-slate-500">
              <span class="font-medium text-slate-700">${s.total_questions}</span>
              Total Questions
            </p>
          </div>
        </div>

        <span class="shrink-0 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600">
          ${badge}
        </span>
      </div>

      <div class="my-5 h-px bg-slate-100"></div>

      <div class="flex items-center justify-between gap-4">
        <div>
          <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Date Created</p>
          <p class="mt-1 text-sm font-medium text-slate-700">${createdDate}</p>
        </div>

        <div class="text-right">
          <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Questions</p>
          <p class="mt-1 text-sm font-bold text-slate-800">${s.total_questions}</p>
        </div>
      </div>

      <div class="mt-5 flex items-center gap-2 border-t border-slate-100 pt-4">
        <button
          data-set-id="${s.set_id}"
          class="manageQuestion inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
          Manage
        </button>

        <button
          data-set-id="${s.set_id}"
          data-set-name="${s.set_name}"
          class="editSet inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5h2M5 19l1.5-6L16 4.5a2.121 2.121 0 013 3L9.5 17.5 5 19z" />
          </svg>
          Edit
        </button>

        <button
          data-set-id="${s.set_id}"
          data-set-name="${s.set_name}"
          class="deleteSet inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-red-500 transition hover:border-red-200 hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
          title="Delete">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M10 11v6M14 11v6M9 7V5h6v2m-8 0l1 14h6l1-14" />
          </svg>
        </button>
      </div>
    </div>
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
              ${q.question_text}
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

          ${q.category}
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
            data-question-text="${q.question_text}"
            data-category="${q.category}"
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
