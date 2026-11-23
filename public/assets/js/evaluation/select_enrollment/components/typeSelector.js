/**
 * Enrollment type radio group (role="radiogroup" / role="radio" cards).
 *
 * Keyboard: Tab focuses the group, Arrow keys move and select, Enter/Space
 * select, and on desktop "1" / "2" select Regular / Irregular.
 */

export const TYPES = {
  regular: { value: "Regular", label: "Regular Student" },
  irregular: { value: "Irregular", label: "Irregular Student" },
};

export function createTypeSelector(group, { onChange }) {
  const cards = [...group.querySelectorAll('[role="radio"]')];
  const isDisabled = (card) => card.getAttribute("aria-disabled") === "true";
  const enabledCards = () => cards.filter((card) => !isDisabled(card));

  let selected = null; // "regular" | "irregular" | null
  let locked = false;

  function render() {
    cards.forEach((card) => {
      const checked = card.dataset.type === selected;
      card.setAttribute("aria-checked", String(checked));
    });

    // Roving tabindex: the checked card (or the first enabled one) is the tab stop.
    const tabStop =
      cards.find((card) => card.dataset.type === selected) ?? enabledCards()[0];
    cards.forEach((card) => {
      card.tabIndex = card === tabStop ? 0 : -1;
    });
  }

  function select(type, { focus = false } = {}) {
    if (locked) return;

    const card = cards.find((c) => c.dataset.type === type);
    if (!card || isDisabled(card)) return;

    selected = type;
    render();
    if (focus) card.focus();
    onChange(selected);
  }

  function clear() {
    if (locked) return;
    selected = null;
    render();
    onChange(null);
  }

  cards.forEach((card) => {
    card.addEventListener("click", () => select(card.dataset.type));

    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        select(card.dataset.type);
        return;
      }

      const step = { ArrowDown: 1, ArrowRight: 1, ArrowUp: -1, ArrowLeft: -1 }[event.key];
      if (!step) return;

      event.preventDefault();
      const list = enabledCards();
      const index = list.indexOf(card);
      const next = list[(index + step + list.length) % list.length];
      if (next) select(next.dataset.type, { focus: true });
    });
  });

  render();

  return {
    select,
    clear,
    get selected() {
      return selected;
    },
    setLocked(value) {
      locked = value;
      group.setAttribute("aria-disabled", String(value));
    },
  };
}
