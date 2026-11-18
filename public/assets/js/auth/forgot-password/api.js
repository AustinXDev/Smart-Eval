import { post } from "../../services/http.js";

const FALLBACK_MESSAGE =
  "An error occurred while sending reset link. Please try again later.";

export async function sendReset(studentEmail) {
  try {
    return await post(`student/auth/send_reset.php`, { studentEmail });
  } catch (error) {
    console.error("Reset link API error:", error);

    // post() throws an Error carrying the server's message for 4xx/5xx
    // responses (e.g. "This account is not activated yet…", "Too many
    // requests…"): show that message. Network failures (TypeError) and
    // non-JSON responses (SyntaxError) have no message meant for users,
    // so they keep the generic text.
    const isConnectionProblem =
      error instanceof TypeError || error instanceof SyntaxError;

    return {
      status: "error",
      message:
        !isConnectionProblem && error?.message
          ? error.message
          : FALLBACK_MESSAGE,
    };
  }
}
