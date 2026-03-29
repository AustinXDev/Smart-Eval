const BASE_URL = '/Smart-Eval/app/handlers/periods/get_question_set.php';

//get all question sets
export async function fetchAllQuestionSets(){
  try{
    const res = await fetch(BASE_URL);
    const data = await res.json();

    //console.log(data);
    return data.data || [];
  } catch (err) {
    console.error('Error fetching all question sets', err);
    return[];
  }
}
