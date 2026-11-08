// Render the open menu under body so table overflow cannot clip it.
export function initArchiveActionMenu() {
  let active = null;
  const owners = new WeakMap();

  function close(restoreFocus = false) {
    if (!active) return;
    const { details, trigger, panel } = active;
    active = null;
    details.open = false;
    trigger.setAttribute("aria-expanded", "false");
    panel.removeAttribute("style");
    details.append(panel);
    if (restoreFocus && trigger.isConnected) trigger.focus();
  }

  function open(details) {
    close();
    const trigger = details.querySelector("summary");
    const panel = details.querySelector("summary + div");
    if (!panel) return;

    owners.set(panel, details);
    active = { details, trigger, panel };
    // Move before opening: the table never contains an expanded panel.
    document.body.append(panel);
    details.open = true;
    trigger.setAttribute("aria-expanded", "true");
    Object.assign(panel.style, {
      position: "fixed",
      margin: "0",
      right: "auto",
      top: "0",
      left: "0",
      zIndex: "9999",
      maxWidth: "calc(100vw - 16px)",
      maxHeight: "calc(100vh - 16px)",
      overflowY: "auto",
    });

    const anchor = trigger.getBoundingClientRect();
    const bounds = panel.getBoundingClientRect();
    const left = Math.max(
      8,
      Math.min(
        anchor.right - bounds.width,
        window.innerWidth - bounds.width - 8,
      ),
    );
    const below = anchor.bottom + 8;
    const top =
      below + bounds.height <= window.innerHeight - 8
        ? below
        : Math.max(8, anchor.top - bounds.height - 8);
    panel.style.left = left + "px";
    panel.style.top = top + "px";
    panel.querySelector("button:not(:disabled)")?.focus();
  }

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("#archive-table details > summary");
    if (trigger) {
      event.preventDefault();
      const details = trigger.parentElement;
      if (active?.details === details) close(true);
      else open(details);
    } else if (active && !active.panel.contains(event.target)) {
      close();
    }
  });

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && active) {
      event.preventDefault();
      close(true);
    }
  });

  document.addEventListener("focusin", (event) => {
    if (
      active &&
      !active.panel.contains(event.target) &&
      !active.details.contains(event.target)
    )
      close();
  });

  document.addEventListener(
    "scroll",
    (event) => {
      if (active && !active.panel.contains(event.target)) close();
    },
    true,
  );
  window.addEventListener("resize", () => close());

  const table = document.getElementById("archive-table");
  if (table) {
    new MutationObserver(() => {
      if (active && !table.contains(active.details)) close();
    }).observe(table, { childList: true, subtree: true });
  }

  return {
    dismissFor(button) {
      const panel = button.parentElement;
      const details = owners.get(panel);
      if (active?.panel === panel) close(true);
      return details;
    },
  };
}
