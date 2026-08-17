import { notify } from "../../../common/notify.js";
import { validateInputs } from "./validator.js";
import { sendReset } from "./api.js";

const resetBTN = document.getElementById("reset-button");
const form = document.getElementById("forgot-password-form");

form.addEventListener("submit", async (e) => {
  e.preventDefault();

  const adminUsername = document.getElementById("inputAdmin").value.trim();

  const validated = validateInputs({
    adminUsername,
  });

  if (!validated.valid) {
    notify("error", validated.message);
    return;
  }

  const data = {
    admin_username: validated.username,
  };

  resetBTN.disabled = true;
  resetBTN.textContent = "Sending Reset Link...";

  try {
    const result = await sendReset(data);

    if (result.status === "error") {
      StatusModal.show("Sending Reset Error", result.message, "error");

      return;
    }

    StatusModal.show(
      "Password Reset Request Received",
      result.message,
      "success",
    );
    form.reset();
  } catch (error) {
    console.error("Password reset error:", error);

    notify(
      "error",
      "An error occurred while sending the reset link. Please try again later.",
    );
  } finally {
    resetBTN.disabled = false;
    resetBTN.textContent = "Reset Password";
  }
});
