export async function fetchPrograms() {
  try {
    const res = await fetch(
      "/Smart-Eval/app/Controllers/programs/get_programs.php",
    );
    const data = await res.json();

    if (data.status !== "success") {
      console.error("PHP error:", data.message);
      return [];
    }

    return data.data;
  } catch {
    console.error("Error fetching all programs", err);
    return [];
  }
}
