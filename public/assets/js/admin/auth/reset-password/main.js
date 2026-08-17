import { notify } from "../../../common/notify.js";
import { initPassword } from "./password.js";
import { validateInputs } from "./validator.js";
import { resetPassword } from "./api.js";

// Check if have token
const token = new URLSearchParams(window.location.search).get("token");

if (!token) {
  alert("invalid or missing token. Please request a new password reset link.");
  window.location.href = `${window.BASE_URL}admin-forgot-password`;
}

window.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const form = document.getElementById("forgot-password-form");
  const resetBTN = document.getElementById("reset-btn");

  document.addEventListener("DOMContentLoaded", () => {
    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      const newPassword = document
        .getElementById("inputNewPassword")
        .value.trim();
      const confirmPassword = document
        .getElementById("inputConfirmPassword")
        .value.trim();

      const validate = validateInputs({ newPassword, confirmPassword });

      if (!validate.valid) {
        notify("error", validate.message);
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
          "success",
          {
            button: false,
          },
        );

        setTimeout(() => {
          window.location.href = `${window.BASE_URL}admin-login`;
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
});
