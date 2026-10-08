import { post } from "../../../services/http.js";

export async function selectEnrollmentType(enrollmentType) {
  try {
    return await post("student/set_enrollment.php", {
      enrollmentType: enrollmentType,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
