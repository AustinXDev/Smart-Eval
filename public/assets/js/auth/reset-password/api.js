import { post } from "../../services/http.js";

export async function resetPassword(data) {
  try {
    return await post(`${window.BASE_URL}/api/password_reset.php`, data);
  } catch (error) {
    console.error("Reset Password API Error: ", error);

    return {
      status: "error",
      message:
        "An error occurred while updating your password. Please try again later.",
    };
  }
}
