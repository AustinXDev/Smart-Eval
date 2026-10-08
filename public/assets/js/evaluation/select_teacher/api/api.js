import { get, post } from "../../../services/http.js";

export async function fetchAvailableTeachers(department) {
  try {
    return await get(`teacher/get_teachers.php?department=${department}`);
  } catch (error) {
    return {
      status: "error",
      message: "Failed to fetch teachers.",
      data: null,
    };
  }
}

export async function submitTeacherSelection(teacherIds) {
  try {
    return await post("student/select_teacher.php", { teachers: teacherIds });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
