import { el } from "./elements.js";
import { state } from "./state.js";

const ACTIVE = ["border-[#6010FF]", "text-[#6010FF]"];
const INACTIVE = ["border-transparent", "text-slate-500"];

function selectTab(key, onChange, focus = false) {
  state.activeTab = key;

  el.tabs().forEach((tab) => {
    const selected = tab.dataset.logTab === key;
    tab.setAttribute("aria-selected", String(selected));
    tab.tabIndex = selected ? 0 : -1;
    ACTIVE.forEach((c) => tab.classList.toggle(c, selected));
    INACTIVE.forEach((c) => tab.classList.toggle(c, !selected));
    if (selected && focus) tab.focus();
  });

  el.panel().setAttribute("aria-labelledby", `tab-${key}`);
  onChange();
}

/**
 * Admin / Student / System tabs with arrow-key navigation (WAI-ARIA tabs).
 * @param {() => void} onChange called after the active tab changes.
 */
export function initTabs(onChange) {
  const tabs = Array.from(el.tabs());

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => selectTab(tab.dataset.logTab, onChange));

    tab.addEventListener("keydown", (event) => {
      const next = {
        ArrowRight: (index + 1) % tabs.length,
        ArrowLeft: (index + tabs.length - 1) % tabs.length,
        Home: 0,
        End: tabs.length - 1,
      }[event.key];

      if (next !== undefined) {
        event.preventDefault();
        selectTab(tabs[next].dataset.logTab, onChange, true);
      }
    });
  });
}
