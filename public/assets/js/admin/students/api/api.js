import { post, get } from "../../../services/http.js";

function toJson(res) {
  return res.json();
}

export async function getStudent(studentId) {
  try {
    return await get(
      `student/get_student.php?student_id=${encodeURIComponent(studentId)}`,
    );
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function getStudentsByDepartment(department) {
  return get(
    `student/get_all_students.php?department=${encodeURIComponent(department)}`,
  );
}

export async function addStudent(formData) {
  try {
    return await post("student/create.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function reactivateStudent(studentId) {
  try {
    return await post("student/reactivate.php", {
      student_id: studentId,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function editStudent(formData) {
  try {
    return await post(`student/update.php`, formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteStudent(studentId, force = false) {
  try {
    return await post(`student/delete.php`, { student_id: studentId, force });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function resetStudentPassword(studentId) {
  try {
    return await post(`student/reset.php`, { student_id: studentId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function uploadCsv(formData) {
  try {
    return await post("student/csv.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
