import { post, get } from "../../../services/http.js";

export async function getTeachersByDepartment(department, params = {}) {
  // Never send a request the API will reject; name the caller's problem instead.
  if (!department) {
    throw new Error(
      "Missing department: open this page from the sidebar (College or SHS).",
    );
  }
  const query = new URLSearchParams({ department, ...params });
  return get(`teacher/get_teachers.php?${query}`);
}

export async function getTeacherHandles(teacherId) {
  const teacher = await getTeacher(teacherId);
  return teacher?.data?.handles ?? teacher?.handles ?? [];
}

export async function getTeacher(teacherId) {
  try {
    const id = Number(teacherId);
    if (!Number.isInteger(id) || id <= 0) {
      throw new Error(`Missing teacher ID (received "${teacherId}").`);
    }

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

/** Import teachers from a CSV file. Throws (error.message) when the whole file is rejected. */
export function importTeachersCsv(formData) {
  return post("teacher/csv.php", formData);
}
