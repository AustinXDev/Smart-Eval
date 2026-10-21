import { post } from "../../services/http.js";

/**
 * Activate student account.
 */
export async function activateAccount(token, password) {
  return post("student/auth/activate.php", {
    token,
    password,
  });
}
