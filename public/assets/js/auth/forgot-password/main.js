import { notify } from "../../common/notify.js";
import { validateInput } from "./validator.js";
import { sendReset } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
  const resetBTN = document.getElementById("reset-button");
  const form = document.getElementById("forgot-password-form");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const studentEmail = document.getElementById("inputEmail").value.trim();

    const validate = validateInput(studentEmail);

    if (!validate.valid) {
      notify("error", validate.message);
      return;
    }

    resetBTN.disabled = true;
    resetBTN.textContent = "Sending reset link...";

    try {
      const result = await sendReset(studentEmail);

      if (result.status !== "success") {
        notify(result.status, result.message);
        return;
      }

      notify(result.status, result.message);
      form.reset();
    } catch (error) {
      console.error("Error:", error);

      notify(
        "error",
        "An error occured while sending the reset link. Please try again later.",
      );
    } finally {
      resetBTN.disabled = false;
      resetBTN.textContent = "Reset Password";
    }
  });
});
