const BASE_URL = "/Smart-Eval/app/Controllers/students/";

//get all programs
export async function fetchAllPrograms(department) {
  try {
    const res = await fetch(
      `${BASE_URL}get_program.php${department ? `?department=${department}` : ""}`,
    );
    const data = await res.json();
    //console.log(data.programs);
    return data.programs || [];
  } catch {
    console.error("Error fetching all programs", err);
    return [];
  }
}
