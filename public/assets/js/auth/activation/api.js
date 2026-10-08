import { post } from "../../services/http.js";

export async function activateAccount(token, password) {
  try {
    return await post(`student/auth/activate.php`, {
      token,
      password,
    });
  } catch (error) {
    console.error("Activation API Error:", error);

    return {
      status: "error",
      message:
        "An error occurred while activating your account. Please try again later.",
    };
  }
}

export async function verifyRegistrationCode(data) {
  console.log("VERIFY REQUEST:", data);

  return await post(`student/auth/registration_verify.php`, data);
}
