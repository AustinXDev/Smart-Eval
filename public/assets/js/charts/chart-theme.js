/**
 * Dark mode for Chart.js charts.
 *
 * Call enableChartTheme() once on a page (after Chart.js has loaded, before
 * charts are created). Every chart on the page then:
 *   - gets dark axis, grid, legend and tooltip colours when <html data-theme="dark">,
 *   - gets its original colours back in light mode,
 *   - is re-coloured when the theme button is used ("se:themechange").
 *
 * Only "chrome" colours change (text, grid, tooltips, white separators).
 * Data colours (lines, bars, slices) stay as each chart defines them.
 *
 * Do not use on the dashboard: dashboard.js themes its own chart.
 */

const DARK = {
  tick: "#a3a3a3",
  grid: "#262626",
  axisTitle: "#a3a3a3",
  pointLabel: "#d4d4d4",
  angleLine: "#2e2e2e",
  legend: "#d4d4d4",
  tooltipBg: "#1c1c1c",
  tooltipTitle: "#fafafa",
  tooltipBody: "#d4d4d4",
  tooltipBorder: "#333333",
  surface: "#141414", // replaces white point fills / slice borders
};

const WHITE = /^(#fff|#ffffff|white|rgba?\(\s*255\s*,\s*255\s*,\s*255\s*(,\s*1(\.0+)?\s*)?\))$/i;

const isDark = () => document.documentElement.getAttribute("data-theme") === "dark";

/**
 * Set obj[key] for dark mode, remembering the original value so light mode can
 * restore it. `store` is per chart; `path` makes each entry unique.
 */
function themed(obj, key, darkValue, store, path, dark) {
  if (!obj) return;

  if (!store.has(path)) {
    store.set(path, { had: Object.prototype.hasOwnProperty.call(obj, key), value: obj[key] });
  }

  if (dark) {
    obj[key] = darkValue;
    return;
  }

  const original = store.get(path);
  if (original.had) obj[key] = original.value;
  else delete obj[key];
}

function applyChartTheme(chart, dark = isDark()) {
  const store = (chart.$seTheme ??= new Map());
  const options = chart.config.options ?? (chart.config.options = {});

  // Axes: tick labels, grid lines, axis titles, radar labels.
  for (const [id, scale] of Object.entries(options.scales ?? {})) {
    if (!scale) continue;
    scale.ticks ??= {};
    themed(scale.ticks, "color", DARK.tick, store, `s.${id}.ticks`, dark);
    // Radar/polar charts draw a white box behind tick labels by default.
    themed(scale.ticks, "backdropColor", "transparent", store, `s.${id}.ticks.bg`, dark);

    if (scale.grid?.display !== false) {
      scale.grid ??= {};
      themed(scale.grid, "color", DARK.grid, store, `s.${id}.grid`, dark);
    }

    if (scale.title) themed(scale.title, "color", DARK.axisTitle, store, `s.${id}.title`, dark);

    if (chart.config.type === "radar" || scale.type === "radialLinear") {
      scale.pointLabels ??= {};
      scale.angleLines ??= {};
      themed(scale.pointLabels, "color", DARK.pointLabel, store, `s.${id}.pl`, dark);
      themed(scale.angleLines, "color", DARK.angleLine, store, `s.${id}.al`, dark);
    }
  }

  // Legend and tooltip.
  options.plugins ??= {};
  options.plugins.legend ??= {};
  options.plugins.legend.labels ??= {};
  themed(options.plugins.legend.labels, "color", DARK.legend, store, "legend", dark);

  options.plugins.tooltip ??= {};
  const tip = options.plugins.tooltip;
  themed(tip, "backgroundColor", DARK.tooltipBg, store, "tip.bg", dark);
  themed(tip, "titleColor", DARK.tooltipTitle, store, "tip.title", dark);
  themed(tip, "bodyColor", DARK.tooltipBody, store, "tip.body", dark);
  themed(tip, "borderColor", DARK.tooltipBorder, store, "tip.border", dark);
  themed(tip, "borderWidth", 1, store, "tip.borderW", dark);

  // White point fills and slice/bar borders would glow on a dark card.
  (chart.config.data?.datasets ?? []).forEach((ds, i) => {
    for (const key of ["pointBackgroundColor", "pointBorderColor", "pointHoverBorderColor", "borderColor"]) {
      const original = store.get(`d.${i}.${key}`)?.value ?? ds[key];
      if (typeof original === "string" && WHITE.test(original.trim())) {
        themed(ds, key, DARK.surface, store, `d.${i}.${key}`, dark);
      }
    }
  });
}

let enabled = false;

export function enableChartTheme() {
  if (enabled || typeof Chart === "undefined") return;
  enabled = true;

  // Theme each chart as it is created (before its first draw).
  Chart.register({
    id: "seTheme",
    beforeInit(chart) {
      applyChartTheme(chart);
    },
  });

  // Re-colour existing charts when the theme button is used.
  window.addEventListener("se:themechange", () => {
    Object.values(Chart.instances).forEach((chart) => {
      applyChartTheme(chart);
      chart.update("none");
    });
  });
}
