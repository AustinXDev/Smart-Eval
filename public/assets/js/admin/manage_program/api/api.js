import { post, get } from "../../../services/http.js";

const BASE = "/Smart-Eval/app/Controllers/programs";
const BASE_API = "/Smart-Eval/api/program/";

function toJson(res) {
  return res.json();
}

export async function getPrograms() {
  try {
    return await get(`program/get_program.php`);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function addProgram(formData) {
  try {
    return await post("program/create.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function editProgram(formData) {
  try {
    return await post("program/update.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteProgram(programId) {
  try {
    return await post("program/delete.php", { program_id: programId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
