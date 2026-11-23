/**
 * Fixed bottom action bar: status dot + label, Clear link, Continue button.
 */

const STATES = {
  null: {
    label: "No option selected",
    dot: "bg-[#CBD5E1]",
    button: "Select an option to continue",
  },
  regular: {
    label: "Regular Student selected",
    dot: "bg-indigo-600",
    button: "Continue with Regular Setup",
  },
  irregular: {
    label: "Irregular Student selected",
    dot: "bg-[#D97706]",
    button: "Continue to Pick Teachers",
  },
};

const DOT_CLASSES = ["bg-[#CBD5E1]", "bg-indigo-600", "bg-[#D97706]"];

export function createActionBar({ dot, ping, label, clearButton, continueButton, continueText }) {
  function update(type) {
    const state = STATES[type ?? "null"];
    const wasDisabled = continueButton.disabled;

    label.textContent = state.label;
    continueText.textContent = state.button;
    clearButton.classList.toggle("hidden", !type);

    dot.classList.remove(...DOT_CLASSES);
    dot.classList.add(state.dot);
    ping.classList.remove(...DOT_CLASSES, "dot-ping");
    if (type) ping.classList.add(state.dot, "dot-ping");

    continueButton.disabled = !type;

    // Subtle pulse the moment the button becomes available.
    if (type && wasDisabled) {
      continueButton.classList.remove("is-ready-pulse");
      void continueButton.offsetWidth; // restart the animation
      continueButton.classList.add("is-ready-pulse");
    }
  }

  return { update };
}
