import { getPasswordScore } from "./password.js";

export function validateInputs({ newPassword, confirmPassword }) {
  if (!newPassword || !confirmPassword) {
    return {
      valid: false,
      message: "Password and confirmation password are required.",
    };
  }

  if (getPasswordScore() < 4) {
    return {
      valid: false,
      message:
        "Password must be min 8 chars, include uppercase, lowercase, number & symbol.",
    };
  }

  if (newPassword !== confirmPassword) {
    return {
      valid: false,
      message: "Password does not match.",
    };
  }

  return {
    valid: true,
  };
}
