import { post } from "../../../services/http.js";

export async function resetPassword(data) {
  try {
    return await post(`admin/auth/password_reset.php`, data);
  } catch (error) {
    console.log("Reset Password API Error: ", error);

    return {
      status: "error",
      message: error.message,
    };
  }
}
