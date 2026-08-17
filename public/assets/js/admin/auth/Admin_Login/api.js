import { post } from "../../../services/http.js";

export async function login(data) {
  try {
    return post(`${window.BASE_URL}api/admin/login.php`, data);
  } catch (error) {
    console.error("Admin Login API:", error);

    return {
      status: "error",
      message: "An error occurred while logging in. Please try again later.",
    };
  }
}

export async function verifyLoginCode(code) {
  try {
    return post(`${window.BASE_URL}api/admin/verify.php`, code);
  } catch (error) {
    console.error("Admin Verify OTP API", error);

    return {
      status: "error",
      message: error.message,
    };
  }
}
