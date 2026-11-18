import { el } from "./elements.js";

// Page state shared by the table, tabs and filters.
export const state = {
  activeTab: "admin",
};

/** Current tab + filter values, sent with every table request. */
export function currentFilters() {
  return {
    actor_type: state.activeTab,
    search: el.search().value.trim(),
    module: el.module().value,
    department: el.department().value,
    from: el.from().value,
    to: el.to().value,
  };
}
