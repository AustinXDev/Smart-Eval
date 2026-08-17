import { post } from "../../services/http.js";

export async function register(credentials) {
  try {
    return post(`student/auth/register.php`, credentials);
  } catch (error) {
    console.error("Register API Error: ", error);

    return {
      status: "error",
      message:
        "An error occured while sending registration. Please try again later.",
    };
  }
}
