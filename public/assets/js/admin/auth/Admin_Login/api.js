import { post } from "../../../services/http.js";

export async function login(data) {
  try {
    return post(`admin/auth/login.php`, data);
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
    return post(`admin/auth/verify.php`, code);
  } catch (error) {
    console.error("Admin Verify OTP API", error);

    return {
      status: "error",
      message: error.message,
    };
  }
}
