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
    const passwordValue = password.value.trim();

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

    try {
      const result = await login({
        student_id: validation.studentId,
        password: validation.password,
      });

      /*
       * Password is correct.
       * 2FA verification is required.
       */
      if (result.status === "2fa_required") {
        signinBTN.value = "Sign In";
        signinBTN.disabled = false;

        let verifyingLoginCode = false;
        let loginResult = null;

        StatusModal.verify(
          "Verify it's you",
          "Enter the 6-digit code we sent to your registered email.",
          {
            onSubmit: async (code) => {
              if (verifyingLoginCode) {
                return false;
              }

              verifyingLoginCode = true;

              try {
                const verifyResult = await verifyLoginCode({
                  code,
                });

                if (verifyResult.status !== "success") {
                  notify("error", verifyResult.message);

                  return false;
                }

                /*
                 * Save the result returned by
                 * completeLogin()
                 */
                loginResult = verifyResult;

                return true;
              } catch (error) {
                notify("error", error.message);

                return false;
              } finally {
                verifyingLoginCode = false;
              }
            },

            onResend: async () => {
              try {
                const result = await resendLoginCode();

                console.log("RESEND RESULT:", result);

                if (result.status !== "success") {
                  notify("error", result.message);
                  return false;
                }

                notify(
                  "success",
                  "A new verification code has been sent to your email.",
                );

                return true;
              } catch (error) {
                notify("error", error.message);

                return false;
              }
            },

            onSuccess: () => {
              StatusModal.show(
                "Login Successful",
                "You're logged in and your teacher evaluation is on record.",
                "success",
              );

              signinBTN.disabled = true;
              signinBTN.value = "Redirecting...";

              setTimeout(() => {
                if (!loginResult?.redirect) {
                  console.error("Missing redirect:", loginResult);

                  notify(
                    "error",
                    "Login succeeded, but no redirect was provided.",
                  );

                  signinBTN.disabled = false;
                  signinBTN.value = "Sign In";

                  return;
                }

                window.location.href = `${window.BASE_URL}${loginResult.redirect}`;
              }, 3000);

              form.reset();
            },
          },
        );

        /*
         *
         * Stop execution here.
         */
        return;
      }

      /*
       * Unexpected response
       */
      if (result.status !== "success") {
        notify("error", result.message);

        return;
      }
    } catch (error) {
      notify("error", error.message);
    } finally {
      /*
       * Don't reset the button while 2FA is being handled.
       */
      if (!document.querySelector(".your-otp-modal-selector")) {
        signinBTN.disabled = false;
        signinBTN.value = "Sign In";
      }
    }
  });
});
