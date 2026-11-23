/**
 * Teacher roster: role="checkbox" cards rendered by the page.
 *
 * Keeps the selection (in the order it was made), filters cards by the
 * search text, and numbers the first nine visible cards for the 1–9 shortcuts.
 */

export function createRoster(root, { onChange }) {
  const cards = [...root.querySelectorAll('[role="checkbox"]')];
  const byId = new Map(cards.map((card) => [card.dataset.id, card]));
  const selected = new Set(); // insertion order = selection order

  let visible = cards;

  function info(card) {
    return {
      id: card.dataset.id,
      name: card.dataset.name,
      employee: card.dataset.employee,
      initials: card.dataset.initials,
      photo: card.dataset.photo,
      badge: card.dataset.badge,
    };
  }

  function render(card) {
    card.setAttribute("aria-checked", String(selected.has(card.dataset.id)));
  }

  function emit() {
    onChange([...selected].map((id) => info(byId.get(id))));
  }

  function setSelected(id, value) {
    const card = byId.get(String(id));
    if (!card) return;
    if (value) selected.add(card.dataset.id);
    else selected.delete(card.dataset.id);
    render(card);
  }

  function toggle(id) {
    setSelected(id, !selected.has(String(id)));
    emit();
  }

  function numberVisible() {
    cards.forEach((card) => {
      const label = card.querySelector("[data-shortcut]");
      if (label) label.textContent = "";
    });
    visible.slice(0, 9).forEach((card, index) => {
      const label = card.querySelector("[data-shortcut]");
      if (label) label.textContent = `[${index + 1}]`;
    });
  }

  /** Shows cards whose name / employee ID / program text contains the query. */
  function filter(query) {
    const q = query.trim().toLowerCase();
    visible = cards.filter((card) => !q || card.dataset.search.includes(q));
    cards.forEach((card) => {
      card.hidden = !visible.includes(card);
    });
    numberVisible();
    return { shown: visible.length, total: cards.length };
  }

  /** All visible cards selected? (used for the Select all / Deselect all label) */
  function allVisibleSelected() {
    return visible.length > 0 && visible.every((card) => selected.has(card.dataset.id));
  }

  function setVisible(value) {
    visible.forEach((card) => setSelected(card.dataset.id, value));
    emit();
  }

  function clear() {
    selected.clear();
    cards.forEach(render);
    emit();
  }

  cards.forEach((card) => {
    card.addEventListener("click", () => toggle(card.dataset.id));
    card.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        toggle(card.dataset.id);
      }
    });
  });

  numberVisible();

  return {
    filter,
    toggle,
    clear,
    setVisible,
    allVisibleSelected,
    /** The n-th visible card (0-based), for the number shortcuts. */
    visibleAt: (index) => visible[index] ?? null,
    get selectedIds() {
      return [...selected];
    },
    get selected() {
      return [...selected].map((id) => info(byId.get(id)));
    },
  };
}
