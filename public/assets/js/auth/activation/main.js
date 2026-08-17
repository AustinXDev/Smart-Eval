import { initPassword } from "./password.js";
import { initTermsModal } from "./eula_modal.js";
import { validateActivation } from "./validator.js";
import { activateAccount, verifyRegistrationCode } from "./api.js";
import { notify } from "../../common/notify.js";

// Get activation token from URL
const token = new URLSearchParams(window.location.search).get("token");

const redirect = () => {
  window.location.href = `${window.BASE_URL}/login`;
};

if (!token) {
  StatusModal.show(
    "Missing Activation Token",
    "Activation token is missing.",
    "error",
    {
      button: false,
    },
  );

  setTimeout(redirect, 3000);

  throw new Error("Activation token is missing.");
}

document.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const modal = initTermsModal();

  document.getElementById("openTermsBtn").addEventListener("click", () => {
    modal.open("terms");
  });

  document.getElementById("openPrivacyBtn").addEventListener("click", () => {
    modal.open("privacy");
  });

  const form = document.getElementById("activation-form");
  const activateBTN = document.getElementById("activate-btn");

  const passwordInput = document.getElementById("inputNewPassword");

  const confirmInput = document.getElementById("inputConfirmPassword");

  const eula = document.getElementById("eula");

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const password = passwordInput.value.trim();

    const confirmPassword = confirmInput.value.trim();

    /*
     * Validate activation form
     */
    const validation = validateActivation({
      password,
      confirmPassword,
      acceptedTerms: eula.checked,
    });

    if (!validation.valid) {
      notify("error", validation.message);
      return;
    }

    activateBTN.disabled = true;
    activateBTN.textContent = "Activating account...";

    try {
      /*
       *
       * Send activation token + password
       *
       * Backend should:
       * - validate token
       * - validate password
       * - hash password
       * - store password temporarily
       * - send registration OTP
       * - return 2fa_required
       */
      const result = await activateAccount(token, password);

      console.log("ACTIVATION RESULT:", result);

      /*
       * OTP is required.
       */
      if (result.status === "2fa_required") {
        activateBTN.disabled = false;
        activateBTN.textContent = "Activate";

        let verifyingCode = false;

        StatusModal.verify(
          "Verify your account",
          "Enter the 6-digit verification code we sent to your registered email.",
          {
            /*
             *
             * User submits OTP
             */
            onSubmit: async (code) => {
              if (verifyingCode) {
                return false;
              }

              verifyingCode = true;

              try {
                const verifyResult = await verifyRegistrationCode({
                  student_id: result.student_id,
                  code: code,
                });

                console.log("REGISTRATION VERIFY RESULT:", verifyResult);

                if (verifyResult.status !== "success") {
                  notify("error", verifyResult.message);

                  return false;
                }

                /*
                 * OTP is correct.
                 *
                 * - verified OTP
                 * - stored/confirmed password
                 * - activated account
                 */
                return true;
              } catch (error) {
                console.error("Registration verification error:", error);

                notify("error", error.message);

                return false;
              } finally {
                verifyingCode = false;
              }
            },

            /*
             *
             * OTP verification succeeded
             */
            onSuccess: () => {
              StatusModal.show(
                "Account Activated",
                "Your account has been successfully activated. You can now sign in.",
                "success",
              );

              activateBTN.disabled = true;
              activateBTN.textContent = "Redirecting...";

              setTimeout(() => {
                window.location.href = `${window.BASE_URL}/login`;
              }, 2500);
            },
          },
        );

        /*
         *
         * Stop here.
         * Do not continue to the normal activation flow.
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
      console.error("Activation error:", error);

      notify("error", error.message);
    } finally {
      if (!document.querySelector(".your-otp-modal-selector")) {
        activateBTN.disabled = false;
        activateBTN.textContent = "Activate";
      }
    }
  });
});
