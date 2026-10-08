import { getProgram } from "../api/evaluationApi.js";

export async function loadProgram(programElement, nameElement) {
  const programId = programElement?.dataset.programId;
  if (!programId || !nameElement) return;

  try {
    const data = await getProgram(programId);

    if (data.status === "success") {
      nameElement.textContent = data.data.program_name;
      return;
    }

    console.error("Program error:", data.message);
  } catch (error) {
    console.error("Program fetch error:", error);
  }
}
