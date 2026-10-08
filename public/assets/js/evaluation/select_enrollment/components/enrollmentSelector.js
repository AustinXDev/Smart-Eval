const TYPES = {
  regular: "Regular",
  irregular: "Irregular",
};

const SELECTED_CARD_CLASSES = [
  "border-violet-500",
  "bg-violet-50/50",
  "shadow-lg",
  "shadow-violet-500/10",
];
const SELECTED_INDICATOR_CLASSES = [
  "border-violet-500",
  "bg-violet-600",
  "text-white",
  "scale-110",
];
const DISABLED_CARD_CLASSES = [
  "pointer-events-none",
  "opacity-50",
  "cursor-not-allowed",
];

export function createEnrollmentSelector({
  cards,
  selectedTypeInput,
  proceedButton,
  buttonText,
  buttonArrow,
  buttonLoader,
}) {
  let selectedType = selectedTypeInput.value || "";

  cards.forEach((card) => {
    card.addEventListener("click", () => selectCard(card));
    card.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" && event.key !== " ") return;

      event.preventDefault();
      selectCard(card);
    });
  });

  function selectCard(card) {
    selectedType = TYPES[card.dataset.enrollmentType] || TYPES.irregular;
    resetCards();

    card.classList.add(...SELECTED_CARD_CLASSES);
    setAccent(card, true);
    setIndicator(card, true);
    setIcon(card, true);

    selectedTypeInput.value = selectedType;
    setReadyState();
  }

  function resetCards() {
    cards.forEach((card) => {
      card.classList.remove(...SELECTED_CARD_CLASSES);
      setAccent(card, false);
      setIndicator(card, false);
      setIcon(card, false);
    });
  }

  function setAccent(card, selected) {
    card
      .querySelector(".selection-accent")
      ?.classList.toggle("opacity-100", selected);
    card
      .querySelector(".selection-accent")
      ?.classList.toggle("opacity-0", !selected);
  }

  function setIndicator(card, selected) {
    const indicator = card.querySelector(".selection-indicator");
    if (!indicator) return;

    indicator.classList.toggle("border-slate-200", !selected);
    indicator.classList.toggle("text-transparent", !selected);
    indicator.classList.toggle("border-violet-500", selected);
    indicator.classList.toggle("bg-violet-600", selected);
    indicator.classList.toggle("text-white", selected);
    indicator.classList.toggle("scale-110", selected);
  }

  function setIcon(card, selected) {
    const icon = card.querySelector(".option-icon");
    if (!icon) return;

    icon.classList.toggle("bg-violet-100", selected);
    icon.classList.toggle(
      "bg-violet-50",
      !selected && card.dataset.enrollmentType === "regular",
    );
    icon.classList.toggle(
      "bg-amber-50",
      !selected && card.dataset.enrollmentType !== "regular",
    );
  }

  function setReadyState() {
    proceedButton.disabled = false;
    proceedButton.classList.remove(
      "bg-slate-200",
      "text-slate-400",
      "shadow-none",
      "cursor-not-allowed",
    );
    proceedButton.classList.add(
      "bg-violet-600",
      "text-white",
      "shadow-md",
      "shadow-violet-600/15",
    );
    buttonText.textContent =
      selectedType === TYPES.regular
        ? "Proceed with Assigned Teachers"
        : "Proceed to Select Teachers";
    buttonArrow?.classList.remove("hidden");
  }

  function setLoading(isLoading) {
    proceedButton.disabled = isLoading;
    buttonText.textContent = isLoading
      ? "Processing..."
      : buttonText.textContent;
    buttonLoader.classList.toggle("hidden", !isLoading);
    buttonArrow?.classList.toggle("hidden", isLoading);
    cards.forEach((card) =>
      card.classList.toggle("pointer-events-none", isLoading),
    );
    cards.forEach((card) => card.classList.toggle("opacity-50", isLoading));
    cards.forEach((card) =>
      card.classList.toggle("cursor-not-allowed", isLoading),
    );
  }

  function resetLoading() {
    setLoading(false);
    setReadyState();
  }

  return {
    getSelectedType: () => selectedType,
    setLoading,
    resetLoading,
  };
}
