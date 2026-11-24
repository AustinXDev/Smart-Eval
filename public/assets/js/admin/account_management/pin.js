import { pinAction } from "./api.js";
import { logout } from "../shared/logout.js";

/**
 * PIN gate for Account Management: six one-digit boxes per PIN (typing moves
 * to the next box, Backspace goes back, pasting "123456" fills all six).
 * On success the page reloads and the server renders the unlocked page.
 */

window.logout = logout;

const gate = document.getElementById("pinGate");
const verifyForm = document.getElementById("pinVerifyForm");
const setForm = document.getElementById("pinSetForm");

function boxesOf(group) {
  return [...group.querySelectorAll(".pin-box")];
}

function valueOf(form, name) {
  const group = form.querySelector(`[data-pin="${name}"]`);
  return group ? boxesOf(group).map((box) => box.value).join("") : "";
}

function clearPin(form, name) {
  const group = form.querySelector(`[data-pin="${name}"]`);
  if (group) boxesOf(group).forEach((box) => (box.value = ""));
}

function initPinGroup(group) {
  const boxes = boxesOf(group);

  boxes.forEach((box, index) => {
    box.addEventListener("input", () => {
      box.value = box.value.replace(/\D/g, "").slice(-1);
      if (box.value && boxes[index + 1]) boxes[index + 1].focus();
    });

    box.addEventListener("keydown", (event) => {
      if (event.key === "Backspace" && !box.value && boxes[index - 1]) {
        boxes[index - 1].focus();
        boxes[index - 1].value = "";
        event.preventDefault();
      } else if (event.key === "ArrowLeft" && boxes[index - 1]) {
        boxes[index - 1].focus();
      } else if (event.key === "ArrowRight" && boxes[index + 1]) {
        boxes[index + 1].focus();
      }
    });

    box.addEventListener("paste", (event) => {
      const digits = (event.clipboardData?.getData("text") ?? "").replace(/\D/g, "").slice(0, 6);
      if (!digits) return;
      event.preventDefault();
      boxes.forEach((b, i) => (b.value = digits[i] ?? ""));
      boxes[Math.min(digits.length, 5)].focus();
    });

    box.addEventListener("focus", () => box.select());
  });
}

function showError(form, message) {
  const el = form.querySelector(".pin-error");
  el.textContent = message;
  el.classList.toggle("hidden", !message);
}

function setBusy(form, busy, label) {
  const button = form.querySelector('button[type="submit"]');
  button.disabled = busy;
  const text = button.querySelector("span");
  if (busy) {
    button.dataset.label = text.textContent;
    text.textContent = label;
  } else if (button.dataset.label) {
    text.textContent = button.dataset.label;
  }
}

async function submit(form, payload, pinNames) {
  showError(form, "");
  setBusy(form, true, "Checking…");
  try {
    await pinAction(payload);
    setBusy(form, true, "Opening…");
    window.location.reload();
  } catch (error) {
    setBusy(form, false);
    showError(form, error.message || "Something went wrong. Please try again.");
    pinNames.forEach((name) => clearPin(form, name));
    form.querySelector(".pin-box")?.focus();
    // Locked after too many tries: reload to show the locked screen.
    if (/locked/i.test(error.message || "")) setTimeout(() => window.location.reload(), 1800);
  }
}

if (gate) {
  gate.querySelectorAll(".pin-group").forEach(initPinGroup);

  verifyForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    const pin = valueOf(verifyForm, "pin");
    if (!/^\d{6}$/.test(pin)) return showError(verifyForm, "Enter all 6 digits.");
    submit(verifyForm, { action: "verify", pin }, ["pin"]);
  });

  setForm?.addEventListener("submit", (event) => {
    event.preventDefault();
    const password = document.getElementById("pinAccountPassword").value;
    const pin = valueOf(setForm, "pin");
    const confirm = valueOf(setForm, "pin_confirm");
    if (!password) return showError(setForm, "Enter your account password.");
    if (!/^\d{6}$/.test(pin)) return showError(setForm, "Enter all 6 digits of the new PIN.");
    if (pin !== confirm) return showError(setForm, "The PINs do not match.");
    submit(setForm, { action: "set", account_password: password, pin, pin_confirm: confirm }, ["pin", "pin_confirm"]);
  });

  // "Forgot PIN?" / "Back to PIN entry"
  gate.addEventListener("click", (event) => {
    const button = event.target.closest("[data-pin-switch]");
    if (!button) return;
    const toSet = button.dataset.pinSwitch === "set";
    verifyForm?.classList.toggle("hidden", toSet);
    setForm?.classList.toggle("hidden", !toSet);
    (toSet ? document.getElementById("pinAccountPassword") : verifyForm?.querySelector(".pin-box"))?.focus();
  });

  (verifyForm && !verifyForm.classList.contains("hidden")
    ? verifyForm.querySelector(".pin-box")
    : document.getElementById("pinAccountPassword"))?.focus();
}
