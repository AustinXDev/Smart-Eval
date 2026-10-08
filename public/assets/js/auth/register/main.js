import { notify } from "../../common/notify.js";
import { playAnimation } from "../../common/animation.js";
import { inputValidation } from "./validator.js";
import { register } from "./api.js";

/*   animate navigation controls   */
document.getElementById("login-link").addEventListener("click", (e) => {
  e.preventDefault();

  playAnimation("register-wrapper");

  setTimeout(() => {
    window.location.href = "./login";
  }, 1200);
});

document.addEventListener("DOMContentLoaded", () => {
  const studentID = document.getElementById("inputStudentID");
  const email = document.getElementById("inputEmail");
  const form = document.getElementById("signUpForm");
  const registerBtn = document.getElementById("signup-btn");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const StudentIDValue = studentID.value;
    const emailValue = email.value;

    const validated = inputValidation(StudentIDValue, emailValue);

    if (!validated) {
      notify("error", validated.message);
      return;
    }

    const credentials = {
      studentId: StudentIDValue,
      studentEmail: emailValue,
    };

    registerBtn.disabled = true;
    registerBtn.value = "Signing Up...";

    try {
      const result = await register(credentials);

      if (result.status !== "success") {
        notify("error", result.message);
        return;
      }

      StatusModal.show("Registration Complete", result.message, result.status);
      form.reset();
    } catch (error) {
      console.error("Error:", error);
      notify(
        "error",
        "An error occurred while processing your request. Please try again later.",
      );
    } finally {
      registerBtn.disabled = false;
      registerBtn.value = "Sign Up";
    }
  });
});
