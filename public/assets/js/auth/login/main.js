import { notify } from "../../common/notify.js";
import { playAnimation } from "../../common/animation.js";
import { initPassword } from "./password.js";
import { login, verifyLoginCode, resendLoginCode } from "./api.js";
import { validateLoginInputs } from "./validator.js";

//animate navigation controls
document.getElementById("signup-link").addEventListener("click", (e) => {
  e.preventDefault();

  playAnimation("login-wrapper");

  setTimeout(() => {
    window.location.href = "./register";
  }, 1200);
});

//login validation
document.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const studentID = document.getElementById("inputStudentID");
  const password = document.getElementById("inputPassword");
  const form = document.getElementById("login-form");
  const signinBTN = document.getElementById("signin-btn");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const StudentIDValue = studentID.value.trim();
    const passwordValue = password.value;

    const validation = validateLoginInputs({
      studentId: StudentIDValue,
      password: passwordValue,
    });

    if (!validation.valid) {
      notify("error", validation.message);
      return;
    }

    signinBTN.disabled = true;
    signinBTN.value = "Signing In...";

    let otpRequired = false;
    let redirecting = false;

    const handleLoginSuccess = (result) => {
      if (!result?.redirect) {
        notify("error", "Login succeeded, but no redirect was provided.");
        return false;
      }

      redirecting = true;

      signinBTN.disabled = true;
      signinBTN.value = "Redirecting...";

      StatusModal.show(
        "Login Successful",
        "You have successfully signed in to SMART-EVAL.",
        "success",
      );

      setTimeout(() => {
        window.location.href = `${window.BASE_URL}${result.redirect}`;
      }, 1500);

      return true;
    };

    try {
      const result = await login({
        student_id: validation.studentId,
        password: validation.password,
      });

      // Browser is already trusted.
      if (result.status === "success") {
        handleLoginSuccess(result);
        return;
      }

      // Browser requires OTP verification.
      if (result.status === "2fa_required") {
        otpRequired = true;

        signinBTN.disabled = false;
        signinBTN.value = "Sign In";

        let verifyingLoginCode = false;
        let loginResult = null;

        StatusModal.verify(
          "Verify it's you",
          "Enter the 6-digit code we sent to your registered email.",
          {
            length: 6,
            allowTrustDevice: true,
            trustDays: 365,

            onSubmit: async (code, trustDevice) => {
              if (verifyingLoginCode) {
                return false;
              }

              verifyingLoginCode = true;

              try {
                const verifyResult = await verifyLoginCode({
                  code,
                  trust_device: trustDevice === true,
                });

                if (verifyResult.status !== "success") {
                  notify(
                    "error",
                    verifyResult.message || "Unable to verify the code.",
                  );
                  return false;
                }

                loginResult = verifyResult;

                return true;
              } catch (error) {
                notify("error", error.message || "Verification failed.");
                return false;
              } finally {
                verifyingLoginCode = false;
              }
            },

            onResend: async () => {
              try {
                const resendResult = await resendLoginCode();

                if (resendResult.status !== "success") {
                  notify("error", resendResult.message);
                  return false;
                }

                notify("success", "A new verification code has been sent.");

                return true;
              } catch (error) {
                notify("error", error.message);
                return false;
              }
            },

            onSuccess: () => {
              handleLoginSuccess(loginResult);
              form.reset();
            },
          },
        );

        return;
      }

      notify("error", result.message || "Unable to sign in.");
    } catch (error) {
      notify("error", error.message || "An unexpected error occurred.");
    } finally {
      if (!otpRequired && !redirecting) {
        signinBTN.disabled = false;
        signinBTN.value = "Sign In";
      }
    }
  });
});
