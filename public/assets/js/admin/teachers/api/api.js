import { post, get } from "../../../services/http.js";

const BASE = "/Smart-Eval/app/Controllers/teachers";

function toJson(res) {
  return res.json();
}

export async function getTeacher(teacherId) {
  try {
    const response = await get(
      `teacher/get_teachers.php?id=${encodeURIComponent(teacherId)}`,
    );

    if (response.status !== "success") {
      throw new Error(response.message);
    }

    return response.data;
  } catch (error) {
    console.error("Failed to get teacher:", error);

    throw error;
  }
}

export async function addTeacher(formData) {
  try {
    return await post("teacher/create.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function editTeacher(formData) {
  try {
    return await post("teacher/update.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteTeacher(teacherId) {
  console.log(teacherId);
  try {
    return await post("teacher/delete.php", { teacher_id: teacherId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function addHandle(formData) {
  console.log(formData);
  try {
    return await post("teacher/add_handle.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteHandle({ teacherId, loadId }) {
  try {
    return await post("teacher/delete_handle.php", {
      teacher_id: teacherId,
      load_id: loadId,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
