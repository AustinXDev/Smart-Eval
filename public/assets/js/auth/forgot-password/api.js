import { post } from "../../services/http.js";

export async function sendReset(studentEmail) {
  try {
    return await post(`${window.BASE_URL}api/send_reset.php`, { studentEmail });
  } catch (error) {
    console.error("Activation API Error:", error);

    return {
      status: "error",
      message:
        "An error occurred while sending reset link. Please try again later.",
    };
  }
}
