/** "Preview of Step 2": shows the panel that matches the selected type. */
export function updatePreview(root, type) {
  const active = type ?? "none";
  root.querySelectorAll("[data-preview]").forEach((panel) => {
    panel.hidden = panel.dataset.preview !== active;
  });
}
