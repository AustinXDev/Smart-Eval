import { fetchAllPrograms } from "../../shared/program_api.js";

export async function populateProgramSelect(
  department,
  selectedProgramId = null,
) {
  const select = document.getElementById("programSelect");
  if (!select) return;

  select.innerHTML =
    '<option value="" disabled selected hidden>Select Program</option>';

  const programs = await fetchAllPrograms(department);

  programs
    .filter((program) => Number(program.is_active) === 1)
    .forEach((program) => {
      const option = document.createElement("option");

      option.value = program.program_id;
      option.textContent = program.program_name;

      select.appendChild(option);
    });
}
