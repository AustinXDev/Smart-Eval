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
      <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition p-4 space-y-3">
        
        <div class="flex items-center gap-3">
          <div class="bg-yellow-100 p-2 rounded text-lg">📋</div>
          <div>
            <h2 class="font-semibold text-gray-800">${s.set_name}</h2>
            <p class="text-sm text-gray-500">
              Total Questions: <span class="font-medium">${s.total_questions}</span>
            </p>
          </div>
        </div>

        <hr>

        <div class="flex justify-between items-center text-sm">
          <p class="text-gray-500">Date Created: ${new Date(s.created_at).toLocaleDateString("en-US", { year: "numeric", month: "long", day: "numeric" })}</p>
          <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs whitespace-nowrap">
            ${badge}
          </span>
        </div>

        <div class="flex gap-2 pt-2">
          <button data-set-id="${s.set_id}" class="manageQuestion bg-purple-700 text-white px-3 py-1 rounded text-sm hover:bg-purple-800">
            Manage
          </button>
          <button data-set-id="${s.set_id}" data-set-name="${s.set_name}" class="editSet border px-3 py-1 rounded text-sm hover:bg-gray-100">
            Edit
          </button>
          <button data-set-id="${s.set_id}" data-set-name="${s.set_name}" class="deleteSet border text-red-600 px-3 py-1 rounded text-sm hover:bg-red-50">
            Delete
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
