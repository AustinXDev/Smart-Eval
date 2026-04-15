//fetch all sets from backend
export async function fetchAllSets() {
  const response = await fetch(
    "/Smart-Eval/app/Controllers/questionnaires/get_sets.php",
  );

  const data = await response.json();
  return data;
}

export async function fetchAllQuestions(id) {
  try {
    const response = await fetch(
      `/Smart-Eval/app/Controllers/questionnaires/get_questions.php?id=${id}`,
    );

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    console.log("Fetched questions:", data);

    return data;
  } catch (error) {
    console.error("Fetch error:", error);
  }
}
