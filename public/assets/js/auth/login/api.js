import { post } from "../../services/http.js";

export async function login(credentials) {
  try {
    return post(`${window.BASE_URL}api/login.php`, credentials);
  } catch (error) {
    console.error("Login API Error:", error);

    return {
      status: "error",
      message: "An error occurred while logging in. Please try again later.",
    };
  }
}

export async function verifyLoginCode(data) {
  try {
    return await post(`${window.BASE_URL}api/two_factor_verify.php`, data);
  } catch (error) {
    console.error("Verify API Error:", error);

    return {
      status: "error",
      message: error?.message || "Something went wrong. Please try again.",
    };
  }
}

export async function resendLoginCode() {
  try {
    return await post(`${window.BASE_URL}api/two_factor_resend.php`);
  } catch (error) {
    console.error("Resend API Error", error);

    return {
      status: "error",
      message: error?.message || "Something went wrong. Please try again.",
    };
  }
}
