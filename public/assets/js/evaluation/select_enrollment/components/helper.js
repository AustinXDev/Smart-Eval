/**
 * "Not sure? Answer one question" card. Each answer selects the matching
 * enrollment type and explains the result in plain language.
 */

const MESSAGES = {
  regular: {
    tone: "border-indigo-200 bg-indigo-50 text-indigo-900",
    text: (page) =>
      `Then you're a Regular Student. We selected it for you, and your ${page.teacherCount} ${page.blockLabel} instructors will load automatically.`,
  },
  regularUnavailable: {
    tone: "border-amber-200 bg-amber-50 text-amber-900",
    text: (page) =>
      `You'd normally be a Regular Student, but no instructors are assigned to ${page.blockLabel} yet. Choose Irregular to pick your instructors, or contact your program office.`,
  },
  irregular: {
    tone: "border-amber-200 bg-amber-50 text-amber-900",
    text: () =>
      "Then you're an Irregular Student. We selected it for you. On the next screen you'll pick each instructor from your COR.",
  },
};

export function initHelper(root, { page, selector }) {
  const buttons = [...root.querySelectorAll("[data-helper-answer]")];
  const message = root.querySelector("#helperMessage");

  buttons.forEach((button) => {
    button.addEventListener("click", () => {
      const answer = button.dataset.helperAnswer;

      buttons.forEach((b) => b.setAttribute("aria-pressed", String(b === button)));

      const key = answer === "regular" && !page.hasRoster ? "regularUnavailable" : answer;
      const { tone, text } = MESSAGES[key];

      message.className = `mt-3 rounded-lg border px-3 py-2.5 text-xs leading-5 ${tone}`;
      message.textContent = text(page);

      if (key !== "regularUnavailable") selector.select(answer);
    });
  });

  return {
    /** Forget the answer (used when the selection is cleared or changed by hand). */
    reset(selectedType) {
      const pressed = buttons.find((b) => b.getAttribute("aria-pressed") === "true");
      if (pressed && pressed.dataset.helperAnswer === selectedType) return;

      buttons.forEach((b) => b.setAttribute("aria-pressed", "false"));
      message.classList.add("hidden");
      message.textContent = "";
    },
  };
}
