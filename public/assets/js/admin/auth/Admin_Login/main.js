import { notify } from "../../../common/notify.js";
import { initPassword } from "./password.js";
import { validateAdminInputs } from "./validator.js";
import { login, verifyLoginCode } from "./api.js";

document.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const adminUserName = document.getElementById("inputAdminUserName");
  const adminPassword = document.getElementById("inputAdminPassword");
  const form = document.getElementById("login-form");
  const signinBTN = document.getElementById("singin-button");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const adminUsernameValue = adminUserName.value;
    const adminPasswordValue = adminPassword.value;

    console.log("Before validation:", {
      adminUsernameValue,
      adminPasswordValue,
    });

    const validation = validateAdminInputs({
      usernameInput: adminUsernameValue,
      passwordInput: adminPasswordValue,
    });

    if (!validation.valid) {
      notify("error", validate.message);
      return;
    }

    const data = {
      admin_username: adminUsernameValue,
      admin_password: adminPasswordValue,
    };

    signinBTN.disabled = true;
    signinBTN.value = "Sign in...";

    try {
      const result = await login(data);

      if (result.status === "2fa_required") {
        signinBTN.value = "Sign in";
        signinBTN.disabled = false;

        let verifyingLoginCode = false;
        let loginResult = null;

        StatusModal.verify(
          "Verify It's you",
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

                /**
                 * save result
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

            onSuccess: () => {
              StatusModal.show("Welcome Back", loginResult.message, "success");

              signinBTN.disabled = true;
              signinBTN.value = "Redirecting...";

              setTimeout(() => {
                if (!loginResult?.redirect) {
                  console.error("Missing redirect:", loginResult);

                  notify(
                    "error",
                    "Login succeeded, but no redirect was provided",
                  );

                  signinBTN.disabled = false;
                  signinBTN.value = "Sign In";
                }

                window.location.href = `${window.BASE_URL}${loginResult.redirect}?dept=college`;
              }, 3000);

              form.reset();
            },
          },
        );

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
