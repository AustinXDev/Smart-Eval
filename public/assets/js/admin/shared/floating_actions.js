// ================================================================
// FLOATING ACTION BUTTONS (Management Pages)
//
// Displays icon-only floating action buttons when the page header
// scrolls out of view. Hides them when the header becomes visible.
//
// Buttons forward clicks to their original header buttons, preserving
// all existing event handlers and functionality.
//
// Example:
// initFloatingActions([
//   { target: ".add-btn", label: "Add Student", icon: "fa-plus" },
//   { target: ".csv-btn", label: "Import CSV", icon: "fa-file-csv" },
// ]);
//
// First action: Primary violet button (bottom).
// Remaining actions: Secondary buttons (stacked above).
// ================================================================

const HEADER_BAR_HEIGHT = 64;

export function initFloatingActions(
  actions = [],
  { watch = "main header" } = {},
) {
  const watched = document.querySelector(watch);

  const resolved = actions
    .map((action) => ({
      ...action,
      source: document.querySelector(`${watch} ${action.target}`),
    }))
    .filter((action) => action.source);

  if (!watched || resolved.length === 0) return;

  // Floating action container
  const container = document.createElement("div");

  container.className =
    "fab-group pointer-events-none fixed bottom-5 right-4 z-30 flex flex-col items-center gap-3 sm:bottom-6 sm:right-6";

  container.setAttribute("role", "group");
  container.setAttribute("aria-label", "Quick actions");

  // Secondary actions above primary action
  const [primary, ...secondary] = resolved;

  [...secondary.reverse(), primary].forEach((action) => {
    const isPrimary = action === primary;

    const button = document.createElement("button");

    button.type = "button";
    button.title = action.label;
    button.setAttribute("aria-label", action.label);

    button.className = isPrimary
      ? [
          "fab-button",
          "inline-flex h-14 w-14 items-center justify-center",
          "rounded-2xl",
          "bg-[#6010FF] text-white",
          "shadow-lg shadow-violet-500/30",
          "ring-1 ring-white/20",
          "transition-all duration-200 ease-out",
          "hover:-translate-y-1 hover:bg-violet-700 hover:shadow-xl hover:shadow-violet-500/30",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-4",
          "active:scale-95",
        ].join(" ")
      : [
          "fab-button",
          "inline-flex h-12 w-12 items-center justify-center",
          "rounded-xl",
          "border border-slate-200/80",
          "bg-white text-slate-600",
          "shadow-md shadow-slate-900/5",
          "transition-all duration-200 ease-out",
          "hover:-translate-y-0.5 hover:border-violet-200 hover:bg-violet-50 hover:text-violet-600 hover:shadow-lg",
          "focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-violet-500 focus-visible:ring-offset-4",
          "active:scale-95",
        ].join(" ");

    // Icon only — no visible labels
    const icon = document.createElement("i");

    icon.className = `fas ${action.icon ?? "fa-plus"} ${
      isPrimary ? "text-lg" : "text-base"
    }`;

    icon.setAttribute("aria-hidden", "true");

    button.appendChild(icon);

    // Forward click to original header action
    button.addEventListener("click", () => {
      if (!action.source.disabled) action.source.click();
    });

    // Mirror the header button's state: disabled flag and its current label
    // (e.g. Notify All -> "Queuing…", "Notify again in 30s", "Notify All (12)").
    const syncFromSource = () => {
      const disabled = Boolean(action.source.disabled);
      button.disabled = disabled;
      button.classList.toggle("opacity-50", disabled);
      button.classList.toggle("cursor-not-allowed", disabled);

      const liveLabel = action.source
        .querySelector("[data-label]")
        ?.textContent.trim();
      const label = liveLabel || action.label;
      button.title = label;
      button.setAttribute("aria-label", label);
    };

    new MutationObserver(syncFromSource).observe(action.source, {
      attributes: true,
      attributeFilter: ["disabled"],
      childList: true,
      characterData: true,
      subtree: true,
    });

    syncFromSource();

    container.appendChild(button);
  });

  document.body.appendChild(container);

  // Reserve space so content is not hidden behind floating actions
  const main = document.querySelector("main");
  main?.classList.add("pb-28");

  // Visibility control
  const setVisible = (visible) => {
    container.classList.toggle("fab-visible", visible);

    container.querySelectorAll("button").forEach((button) => {
      button.tabIndex = visible ? 0 : -1;
    });

    container.setAttribute("aria-hidden", String(!visible));
  };

  // Show only when the header has scrolled past the fixed top bar
  let visible = null;

  const update = () => {
    const next = watched.getBoundingClientRect().bottom <= HEADER_BAR_HEIGHT;

    if (next !== visible) {
      visible = next;
      setVisible(next);
    }
  };

  window.addEventListener("scroll", update, { passive: true });
  window.addEventListener("resize", update, { passive: true });

  update();
}
