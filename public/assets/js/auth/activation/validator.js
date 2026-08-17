import { getPasswordScore } from "./password.js";

export function validateActivation({
  password,
  confirmPassword,
  acceptedTerms,
}) {
  if (!password || !confirmPassword) {
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

  if (password !== confirmPassword) {
    return {
      valid: false,
      message: "Password does not match.",
    };
  }

  const eula = document.getElementById("eula");

  if (!acceptedTerms) {
    return {
      valid: false,
      message: "You must agree to the terms and conditions.",
    };
  }

  return {
    valid: true,
  };
}
