import { post } from "../../../services/http.js";

export async function sendReset(data) {
  try {
    return await post(`${window.BASE_URL}api/admin/sendReset.php`, data);
  } catch (error) {
    console.error("Reset request API error:", error);

    return {
      status: "error",
      message: error.message || "Unable to send reset request.",
    };
  }
}
