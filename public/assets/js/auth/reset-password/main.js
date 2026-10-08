import { notify } from "../../common/notify.js";
import { initPassword } from "./password.js";
import { validateInputs } from "./validator.js";
import { resetPassword } from "./api.js";

// Check if have token
const token = new URLSearchParams(window.location.search).get("token");

const redirect = () => {
  window.location.href = `${window.BASE_URL}login`;
};

if (!token) {
  StatusModal.show(
    "Reset Token Not Found",
    "No reset token found. Please request a new password reset link.",
    "error",
    {
      button: false,
    },
  );

  setTimeout(redirect, 3000);
}

const form = document.getElementById("forgot-password-form");
const resetBTN = document.getElementById("reset-btn");

document.addEventListener("DOMContentLoaded", () => {
  initPassword();

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const newPassword = document
      .getElementById("inputNewPassword")
      .value.trim();
    const confirmPassword = document
      .getElementById("inputConfirmPassword")
      .value.trim();

    const validated = validateInputs({
      newPassword,
      confirmPassword,
    });

    if (!validated) {
      notify("error", validated.message);
      return;
    }

    const data = {
      token: token,
      password: newPassword,
    };

    resetBTN.disabled = true;
    resetBTN.textContent = "Resetting password...";

    try {
      const result = await resetPassword(data);

      if (result.status === "error") {
        notify("error", result.message);
        return;
      }

      StatusModal.show(
        "Reset Password Successful",
        result.message,
        result.status,
      );

      setTimeout(() => {
        window.location.href = `${window.BASE_URL}login`;
      }, 3000);
    } catch (error) {
      console.error("Error:", error);
      notify(
        "error",
        "An error occured while resetting the password. Please try again later.",
      );
    } finally {
      resetBTN.disabled = false;
      resetBTN.textContent = "Reset Password";
    }
  });
});
