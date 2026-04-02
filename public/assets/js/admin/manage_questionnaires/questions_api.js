
//fetch all sets from backend
export async function fetchAllSets(){

  const response = await fetch('/Smart-Eval/app/handlers/questionnaires/get_sets.php');

  const data = await response.json();
  return data;
}