import { post } from "../../../services/http.js";

export async function resetPassword(data) {
  try {
    return await post(`${window.BASE_URL}api/admin/password_reset.php`, data);
  } catch (error) {
    console.log("Reset Password API Error: ", error);

    return {
      status: "error",
      message: error.message,
    };
  }
}
