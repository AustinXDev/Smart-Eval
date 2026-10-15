import { initPassword } from "./password.js";
import { initTermsModal } from "./eula_modal.js";
import { validateActivation } from "./validator.js";
import { activateAccount } from "./api.js";
import { notify } from "../../common/notify.js";

// Get activation token from URL
const token = new URLSearchParams(window.location.search).get("token");

const redirect = () => {
  window.location.href = `${window.BASE_URL}/login`;
};

document.addEventListener("DOMContentLoaded", () => {
  initPassword();

  const modal = initTermsModal();

  document.getElementById("openTermsBtn")?.addEventListener("click", () => {
    modal.open("terms");
  });

  document.getElementById("openPrivacyBtn")?.addEventListener("click", () => {
    modal.open("privacy");
  });

  const form = document.getElementById("activation-form");
  const activateBTN = document.getElementById("activate-btn");
  const passwordInput = document.getElementById("inputNewPassword");
  const confirmInput = document.getElementById("inputConfirmPassword");
  const eula = document.getElementById("eula");

  if (!form || !activateBTN || !passwordInput || !confirmInput || !eula) {
    console.error("Activation form elements are missing.");
    return;
  }

  // Validate activation token
  if (!token) {
    activateBTN.disabled = true;

    StatusModal.show(
      "Missing Activation Token",
      "Activation token is missing. Redirecting to login...",
      "error",
      { button: false },
    );

    setTimeout(redirect, 3000);
    return;
  }

  let isSubmitting = false;
  let activationSuccessful = false;

  const setLoading = (loading) => {
    activateBTN.disabled = loading || activationSuccessful;

    activateBTN.textContent = activationSuccessful
      ? "Redirecting..."
      : loading
        ? "Activating account..."
        : "Activate";
  };

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    if (isSubmitting || activationSuccessful) return;

    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;

    // Validate activation form
    const validation = validateActivation({
      password,
      confirmPassword,
      acceptedTerms: eula.checked,
    });

    if (!validation.valid) {
      notify("error", validation.message);
      return;
    }

    isSubmitting = true;
    setLoading(true);

    try {
      // Activate account immediately after password creation
      const result = await activateAccount(token, password);

      console.log("Activation response:", result);
      console.log("Status:", result?.status);
      console.log("Message:", result?.message);

      if (result?.status === "error") {
        StatusModal.show(
          "Activation Failed",
          result.message || "Unable to activate your account.",
          "error",
        );
        return;
      }

      if (result.status === "error") {
        throw new Error(result.message || "Request failed.");
      }

      // Account successfully activated
      activationSuccessful = true;

      StatusModal.show(
        "Account Activated",
        "Your account has been successfully activated. Redirecting to the login page...",
        "success",
        { button: false },
      );

      setLoading(false);

      setTimeout(redirect, 2500);
    } catch (error) {
      console.error("Activation error:", error);

      StatusModal.show(
        "Activation Failed",
        error.message || "Something went wrong. Please try again.",
        "error",
      );
    } finally {
      isSubmitting = false;

      if (!activationSuccessful) {
        setLoading(false);
      }
    }
  });
});
