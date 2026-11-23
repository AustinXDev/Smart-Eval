/** Fixed bottom bar: status dot + count, Clear all, and the Continue button. */
export function createActionBar({ dot, ping, label, clearButton, continueButton, continueText }) {
  let lastCount = 0;

  return function update(count) {
    const has = count > 0;

    label.textContent = `${count} ${count === 1 ? "teacher" : "teachers"} selected`;
    dot.className = `relative inline-flex h-2.5 w-2.5 rounded-full ${has ? "bg-[#4F46E5]" : "bg-[#CBD5E1]"}`;
    ping.className = `absolute inline-flex h-full w-full rounded-full opacity-60 ${has ? "dot-ping bg-[#818CF8]" : ""}`;
    clearButton.classList.toggle("hidden", !has);

    continueButton.disabled = !has;
    continueText.textContent = has
      ? `Continue with ${count} ${count === 1 ? "Teacher" : "Teachers"}`
      : "Select teachers to continue";

    // Pulse once when the button first becomes available.
    if (has && lastCount === 0) {
      continueButton.classList.remove("is-ready-pulse");
      void continueButton.offsetWidth;
      continueButton.classList.add("is-ready-pulse");
    }
    lastCount = count;
  };
}
