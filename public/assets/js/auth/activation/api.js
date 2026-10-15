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

export async function verifyRegistrationCode(data) {
  console.log("VERIFY REQUEST:", data);

  return await post(`student/auth/registration_verify.php`, data);
}
