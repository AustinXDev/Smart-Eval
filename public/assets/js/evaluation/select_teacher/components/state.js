export function createSelectionState() {
  let selected = [];

  return {
    getAll: () => [...selected],
    isSelected: (id) => selected.includes(id),
    toggle(id) {
      selected = selected.includes(id)
        ? selected.filter((t) => t !== id)
        : [...selected, id];
      return selected.includes(id);
    },
    count: () => selected.length,
  };
}
