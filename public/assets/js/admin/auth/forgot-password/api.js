import { post } from "../../../services/http.js";

export async function sendReset(data) {
  try {
    return await post(`admin/auth/sendReset.php`, data);
  } catch (error) {
    console.error("Reset request API error:", error);

    return {
      status: "error",
      message: error.message || "Unable to send reset request.",
    };
  }
}
