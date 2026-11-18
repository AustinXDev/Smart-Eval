<?php
/**
 * Dark mode for a page. Include inside <head>, after custom.css:
 *
 *   <?php require __DIR__ . '/../partials/theme_head.php'; ?>
 *
 * It sets <html data-theme> before the page paints (so there is no white flash),
 * loads the dark palette, and turns on the theme button in the header.
 * Pages that do not include this file always stay light.
 */
$themeEnabled = true;
?>
<link rel="stylesheet" href="<?= BASE_URL ?>assets/css/dark.css">
<script>
  (function () {
    var theme = "light";
    try {
      theme = localStorage.getItem("se-theme") === "dark" ? "dark" : "light";
    } catch (e) {}
    document.documentElement.setAttribute("data-theme", theme);
    document.documentElement.style.colorScheme = theme;
  })();
</script>
<script src="<?= BASE_URL ?>assets/js/common/theme.js" defer></script>
