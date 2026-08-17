import { post } from "../../services/http.js";

export async function logout() {
  return await post(`student/auth/logout.php`, {});
}
