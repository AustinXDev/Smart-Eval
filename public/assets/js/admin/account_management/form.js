import { openModal, closeModal } from "../../modal/modal.js";
import { setLoading } from "../shared/Loader.js";
import { createAccount, updateAccount, isPinRequired } from "./api.js";

// StatusModal is loaded globally by assets/js/common/modal.js.

/* ---------- Field errors ---------- */

export function clearErrors(form) {
  form.querySelectorAll("[data-error-for]").forEach((el) => {
    el.textContent = "";
    el.classList.add("hidden");
  });
  form.querySelectorAll('[aria-invalid="true"]').forEach((el) => el.removeAttribute("aria-invalid"));
}

/** Shows the server's { field: message } errors under each field; focuses the first. */
function showErrors(form, errors) {
  let first = null;
  Object.entries(errors).forEach(([field, message]) => {
    const slot = form.querySelector(`[data-error-for="${field}"]`);
    if (slot) {
      slot.textContent = message;
      slot.classList.remove("hidden");
    }
    const input = form.querySelector(`[name="${field}"]`);
    if (input) {
      input.setAttribute("aria-invalid", "true");
      first ??= input;
    }
  });
  first?.focus();
}

/** Quick checks before asking the server (the server checks everything again). */
function checkLocally(form, isCreate) {
  const v = (name) => (form.elements[name]?.value ?? "").trim();
  const errors = {};

  if (v("full_name").length < 2) errors.full_name = "Enter a full name.";
  if (!/^[A-Za-z0-9._-]{4,50}$/.test(v("username"))) errors.username = "Use 4–50 letters, numbers, dots, dashes or underscores.";
  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v("email"))) errors.email = "Enter a valid email address.";
  if (!v("role")) errors.role = "Choose a role.";

  if (isCreate) {
    const password = form.elements.password.value;
    if (password.length < 8 || password.length > 72 || !/[A-Za-z]/.test(password) || !/\d/.test(password)) {
      errors.password = "Use 8–72 characters with at least one letter and one number.";
    } else if (password !== form.elements.confirm_password.value) {
      errors.confirm_password = "The passwords do not match.";
    }
  }
  return errors;
}

function payloadOf(form) {
  const data = Object.fromEntries(new FormData(form).entries());
  // A disabled field (your own role) is left out of FormData; send its value anyway.
  if (form.elements.role?.disabled) data.role = form.elements.role.value;
  return data;
}

async function save(form, request, { modalId, onSaved, successTitle }) {
  setLoading("Saving…");
  try {
    const result = await request(payloadOf(form));
    closeModal(modalId);
    clearErrors(form);
    onSaved();

    const data = result.data ?? {};
    if (data.email_sent === false) {
      // StatusModal has only success/error styles; use error so this isn't missed.
      StatusModal.show(
        "Email not sent",
        `${data.full_name}'s account was created, but the email with the sign-in details could not be sent. Share the username and temporary password with them another way.`,
        "error",
      );
    } else {
      StatusModal.show(successTitle, result.message || "Saved.", "success");
    }
  } catch (error) {
    if (isPinRequired(error)) {
      window.location.reload();
      return;
    }
    if (error.status === 422 && error.data?.errors) {
      showErrors(form, error.data.errors);
    } else {
      StatusModal.show("Not saved", error.message || "Something went wrong. Please try again.", "error");
    }
  } finally {
    setLoading("", false);
  }
}

/* ---------- Add ---------- */

export function initAddForm({ onSaved }) {
  const form = document.getElementById("addAccountForm");
  if (!form) return;

  form.addEventListener("input", (event) => {
    const name = event.target.name;
    form.querySelector(`[data-error-for="${name}"]`)?.classList.add("hidden");
    event.target.removeAttribute("aria-invalid");
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    clearErrors(form);

    const errors = checkLocally(form, true);
    if (Object.keys(errors).length) return showErrors(form, errors);

    StatusModal.confirm(
      "Create Account",
      `Create an account for ${form.elements.full_name.value.trim()} and email the sign-in details to ${form.elements.email.value.trim()}?`,
      () => save(form, createAccount, { modalId: "addAccountModal", onSaved, successTitle: "Account created" }),
    );
  });
}

export function openAddForm() {
  const form = document.getElementById("addAccountForm");
  form.reset();
  clearErrors(form);
  openModal("addAccountModal");
  form.elements.full_name.focus();
}

/* ---------- Edit ---------- */

export function initEditForm({ onSaved }) {
  const form = document.getElementById("editAccountForm");
  if (!form) return;

  form.addEventListener("input", (event) => {
    const name = event.target.name;
    form.querySelector(`[data-error-for="${name}"]`)?.classList.add("hidden");
    event.target.removeAttribute("aria-invalid");
  });

  form.addEventListener("submit", (event) => {
    event.preventDefault();
    clearErrors(form);

    const errors = checkLocally(form, false);
    if (Object.keys(errors).length) return showErrors(form, errors);

    const deactivating = form.dataset.status === "active" && form.elements.status.value === "inactive";
    const name = form.elements.full_name.value.trim();

    StatusModal.confirm(
      deactivating ? "Deactivate Account" : "Save Changes",
      deactivating
        ? `${name} will be signed out and won't be able to sign in until the account is activated again. Continue?`
        : `Save the changes to ${name}'s account?`,
      () => save(form, updateAccount, { modalId: "editAccountModal", onSaved, successTitle: "Account updated" }),
    );
  });
}

/** Fills the edit form with an account from the list. */
export function openEditForm(account) {
  const form = document.getElementById("editAccountForm");
  form.reset();
  clearErrors(form);

  form.elements.admin_id.value = account.admin_id;
  form.elements.full_name.value = account.full_name;
  form.elements.username.value = account.username;
  form.elements.email.value = account.email;
  form.elements.role.value = account.role;
  form.querySelectorAll('input[name="status"]').forEach((radio) => {
    radio.checked = radio.value === account.status;
    // Your own account: role and status are locked (the server enforces this too).
    radio.disabled = account.is_self && radio.value !== account.status;
  });
  form.elements.role.disabled = account.is_self;
  form.dataset.status = account.status;
  document.getElementById("editSelfNote").classList.toggle("hidden", !account.is_self);

  openModal("editAccountModal");
  form.elements.full_name.focus();
}

/* ---------- Show / hide password ---------- */

export function initPasswordToggles() {
  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-toggle-password]");
    if (!button) return;
    const input = document.getElementById(button.dataset.togglePassword);
    const show = input.type === "password";
    input.type = show ? "text" : "password";
    button.setAttribute("aria-pressed", String(show));
    button.setAttribute("aria-label", show ? "Hide password" : "Show password");
    button.querySelector("i").className = `fa-solid ${show ? "fa-eye-slash" : "fa-eye"} text-xs`;
  });
}
