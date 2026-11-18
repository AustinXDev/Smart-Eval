/**
 * Light / dark theme switch.
 *
 * views/partials/theme_head.php sets <html data-theme> before the page paints
 * (no white flash) and loads this file. This file wires every
 * [data-theme-toggle] button, saves the choice, and announces changes:
 *
 *   window.addEventListener("se:themechange", (e) => e.detail.theme);  // "light" | "dark"
 *   window.SmartEvalTheme.get();                                        // current theme
 */
(function () {
  "use strict";

  var STORAGE_KEY = "se-theme";
  var root = document.documentElement;

  function get() {
    return root.getAttribute("data-theme") === "dark" ? "dark" : "light";
  }

  function save(theme) {
    try {
      localStorage.setItem(STORAGE_KEY, theme);
    } catch (e) {
      /* private mode / storage blocked: the theme just won't be remembered */
    }
  }

  function syncButtons(theme) {
    var buttons = document.querySelectorAll("[data-theme-toggle]");
    for (var i = 0; i < buttons.length; i++) {
      var dark = theme === "dark";
      buttons[i].setAttribute("aria-pressed", dark ? "true" : "false");
      buttons[i].setAttribute("aria-label", dark ? "Switch to light mode" : "Switch to dark mode");
      buttons[i].setAttribute("title", dark ? "Light mode" : "Dark mode");
    }
  }

  function set(theme) {
    theme = theme === "dark" ? "dark" : "light";
    root.setAttribute("data-theme", theme);
    root.style.colorScheme = theme;
    save(theme);
    syncButtons(theme);
    window.dispatchEvent(new CustomEvent("se:themechange", { detail: { theme: theme } }));
  }

  window.SmartEvalTheme = { get: get, set: set };

  function init() {
    syncButtons(get());

    document.addEventListener("click", function (event) {
      var button = event.target.closest ? event.target.closest("[data-theme-toggle]") : null;
      if (button) set(get() === "dark" ? "light" : "dark");
    });

    // Enable colour transitions only after the first paint.
    requestAnimationFrame(function () {
      root.classList.add("theme-ready");
    });
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
