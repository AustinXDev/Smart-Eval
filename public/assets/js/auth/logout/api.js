import { post } from "../../services/http.js";

export async function logout() {
  return await post(`${window.BASE_URL}api/logout.php`, {});
}
