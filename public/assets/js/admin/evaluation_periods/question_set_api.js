const BASE_URL = "/Smart-Eval/app/Controllers/periods/get_question_set.php";

//get all question sets
export async function fetchAllQuestionSets() {
  try {
    const res = await fetch(BASE_URL);
    const data = await res.json();

    //console.log(data);
    return data.data || [];
  } catch (err) {
    console.error("Error fetching all question sets", err);
    return [];
  }
}

//get created period
export async function fetchCreatedPeriod(periodId) {
  try {
    const res = await fetch(
      `/Smart-Eval/app/Controllers/periods/get_specific_period.php?period_id=${periodId}`,
    );

    const data = await res.json();

    if (data.status !== "success") {
      throw new Error(data.message || "Failed to fetch period");
    }

    return data;
  } catch (err) {
    console.error("Error fetching all question sets", err);
    return null;
  }
}
