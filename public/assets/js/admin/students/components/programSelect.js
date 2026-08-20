import { fetchAllPrograms } from "../../shared/program_api.js";

export async function populateProgramSelect(department) {
  const select = document.getElementById("programSelect");
  if (!select) return;

  select.innerHTML = '<option value="" disabled>Select Program</option>';

  const programs = await fetchAllPrograms(department);
  programs.forEach((program) => {
    const option = document.createElement("option");
    option.value = program.program_id;
    option.textContent = program.program_name;
    select.appendChild(option);
  });
}

export async function populateEditProgramSelect(student) {
  const select = document.getElementById("editProgramSelect");
  if (!select) return;

  select.innerHTML = '<option value="" disabled>Select Program</option>';

  const programs = await fetchAllPrograms(student.department);
  programs.forEach((program) => {
    const option = document.createElement("option");
    option.value = program.program_id;
    option.textContent = program.program_name;
    if (program.program_id == student.program_id) option.selected = true;
    select.appendChild(option);
  });
}
