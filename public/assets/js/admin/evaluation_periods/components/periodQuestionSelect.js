import { fetchAllQuestionSets } from "../api/api.js";

export async function populateQuestionsSet(selectId = "questionSetSelect") {
  const select = document.getElementById(selectId);
  if (!select) return;

  select.innerHTML =
    "<option disabled selected hidden>Select Question Bank</option>";

  const response = await fetchAllQuestionSets();

  const questionSets = response.data;

  questionSets.forEach((qs) => {
    const option = document.createElement("option");
    option.value = String(qs.set_id);
    option.textContent = qs.set_name;
    select.appendChild(option);
  });
}
