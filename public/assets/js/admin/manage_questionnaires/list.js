import { fetchAllSets, fetchAllQuestions } from "./questions_api.js";

document.addEventListener("DOMContentLoaded", () => {
  loadQuestionSetList();
});

//load all question set
export async function loadQuestionSetList() {
  const res = await fetchAllSets();

  if (res.status !== "success") {
    console.error("Failed to load question sets");
    return;
  }

  const sets = res.data;
  const questionsContainer = document.querySelector(".cards-container");

  questionsContainer.innerHTML = "";

  sets.forEach((s) => {
    let badge = ``;

    if (s.active_evaluation_using_set) {
      badge = `In Used`;
    } else {
      badge = `Used ${s.total_periods_using_set} time${s.total_periods_using_set !== 1 ? "s" : ""}`;
    }

    questionsContainer.insertAdjacentHTML(
      "beforeend",
      `
      <div
        class="group relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-lg">

        <!-- Top Accent -->
        <div class="absolute left-0 top-0 h-1 w-full bg-amber-400"></div>

        <!-- HEADER -->
        <div class="flex items-start justify-between gap-4">

          <div class="flex min-w-0 items-center gap-4">

            <!-- Icon -->
            <div
              class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-600">

              <svg xmlns="http://www.w3.org/2000/svg"
                class="h-5 w-5"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
                stroke-width="1.8">

                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />

                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 3v5h5" />

              </svg>

            </div>

            <!-- TITLE -->
            <div class="min-w-0">

              <h2 class="truncate text-base font-bold text-slate-800">
                ${s.set_name}
              </h2>

              <p class="mt-1 text-sm text-slate-500">
                <span class="font-medium text-slate-700">
                  ${s.total_questions}
                </span>
                Total Questions
              </p>

            </div>

          </div>

          <!-- STATUS -->
          <span
            class="shrink-0 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600">
            ${badge}
          </span>

        </div>


        <!-- DIVIDER -->
        <div class="my-5 h-px bg-slate-100"></div>


        <!-- DETAILS -->
        <div class="flex items-center justify-between gap-4">

          <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
              Date Created
            </p>

            <p class="mt-1 text-sm font-medium text-slate-700">
              ${new Date(s.created_at).toLocaleDateString("en-US", {
                year: "numeric",
                month: "long",
                day: "numeric",
              })}
            </p>
          </div>

          <!-- Question Count -->
          <div class="text-right">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">
              Questions
            </p>

            <p class="mt-1 text-sm font-bold text-slate-800">
              ${s.total_questions}
            </p>
          </div>

        </div>


        <!-- ACTIONS -->
        <div class="mt-5 flex items-center gap-2 border-t border-slate-100 pt-4">

          <!-- Manage -->
          <button
            data-set-id="${s.set_id}"
            class="manageQuestion inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">

            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2">

              <path stroke-linecap="round" stroke-linejoin="round"
                d="M4 6h16M4 12h16M4 18h16" />

            </svg>

            Manage
          </button>


          <!-- Edit -->
          <button
            data-set-id="${s.set_id}"
            data-set-name="${s.set_name}"
            class="editSet inline-flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-300">

            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2">

              <path stroke-linecap="round" stroke-linejoin="round"
                d="M11 5h2M5 19l1.5-6L16 4.5a2.121 2.121 0 013 3L9.5 17.5 5 19z" />

            </svg>

            Edit
          </button>


          <!-- Delete -->
          <button
            data-set-id="${s.set_id}"
            data-set-name="${s.set_name}"
            class="deleteSet inline-flex items-center justify-center rounded-xl border border-red-100 bg-red-50 px-3 py-2.5 text-red-500 transition hover:border-red-200 hover:bg-red-100 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
            title="Delete">

            <svg xmlns="http://www.w3.org/2000/svg"
              class="h-4 w-4"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
              stroke-width="2">

              <path stroke-linecap="round" stroke-linejoin="round"
                d="M6 7h12M10 11v6M14 11v6M9 7V5h6v2m-8 0l1 14h6l1-14" />

            </svg>

          </button>

        </div>

      </div>

    `,
    );
  });
}

//load question list per set
export async function loadQuestions(id) {
  const res = await fetchAllQuestions(id);

  if (res.status !== "success") {
    console.error("Failed to load question sets", res);
    return;
  }

  const questions = res.data;

  const listBody = document.getElementById("questionList");

  listBody.innerHTML = "";

  questions.forEach((q) => {
    listBody.insertAdjacentHTML(
      "beforeend",
      `<tr class="hover:bg-gray-50 transition whitespace-nowrap">
          <td class="px-4 py-3 font-medium text-gray-700">
            ${q.question_text}
          </td>
          <td class="px-4 py-3 text-gray-500">${q.category}</td>
          <td class="px-4 py-3 text-center flex justify-center gap-2">

            <button 
            data-question-id="${q.question_id}" 
            data-set-id="${q.set_id}" 
            data-question-text="${q.question_text}" 
            data-category="${q.category}"
              class="editQuestion bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-md text-xs 
              transition flex items-center gap-1">
              <i class="fa-solid fa-pen"></i> Edit
            </button>

            <button data-question-id=${q.question_id}
              class="deleteQuestion bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-xs 
              transition flex items-center gap-1">
              <i class="fa-solid fa-trash"></i> Delete
            </button>

          </td>
        </tr>`,
    );
  });
}
