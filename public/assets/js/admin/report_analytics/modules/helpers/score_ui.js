const MEAN_SCORE_STATES = [
  {
    min: 4.21,
    max: 5.0,
    bg: "#ECFDF5",
    text: "#065F46",
    label: "#34D399",
    sublabel: "#6EE7B7",
    dark: { bg: "rgba(16, 185, 129, 0.12)", text: "#6EE7B7" },
  },
  {
    min: 3.41,
    max: 4.2,
    bg: "#EDFAF1",
    text: "#1A7F3C",
    label: "#4ADE80",
    sublabel: "#86EFAC",
    dark: { bg: "rgba(34, 197, 94, 0.12)", text: "#86EFAC" },
  },
  {
    min: 2.61,
    max: 3.4,
    bg: "#FFFBEB",
    text: "#B45309",
    label: "#F59E0B",
    sublabel: "#FCD34D",
    dark: { bg: "rgba(245, 158, 11, 0.12)", text: "#FCD34D" },
  },
  {
    min: 1.81,
    max: 2.6,
    bg: "#FEF3C7",
    text: "#92400E",
    label: "#D97706",
    sublabel: "#FCD34D",
    dark: { bg: "rgba(217, 119, 6, 0.14)", text: "#FBBF24" },
  },
  {
    min: 1.0,
    max: 1.8,
    bg: "#FEF2F2",
    text: "#991B1B",
    label: "#F87171",
    sublabel: "#FCA5A5",
    dark: { bg: "rgba(239, 68, 68, 0.12)", text: "#FCA5A5" },
  },
];

const isDarkTheme = () => document.documentElement.getAttribute("data-theme") === "dark";

// Last mean-score card rendered, so it can be re-coloured when the theme changes.
let lastMeanScore = null;

window.addEventListener("se:themechange", () => {
  if (lastMeanScore) meanScoreUi(...lastMeanScore);
});

export function growthRateUI(growthRate, id) {
  const growthEl = document.getElementById(id);
  if (!growthEl) return;

  const rate = Number(growthRate);
  const prefix = rate > 0 ? "+" : "";
  growthEl.textContent = `${prefix}${rate}%`;

  if (rate > 0) {
    growthEl.className =
      "text-green-600 mt-2 text-2xl font-bold tracking-tight";
  } else if (rate < 0) {
    growthEl.className = "text-red-600 mt-2 text-2xl font-bold tracking-tight";
  } else {
    growthEl.className = "text-gray-500 mt-2 text-2xl tracking-tight";
    growthEl.textContent = "0%";
  }
}

export function meanScoreUi(mean, adjectiveRating, parentEl, childEl) {
  if (!parentEl || !childEl) {
    console.warn("Mean UI elements missing");
    return;
  }

  lastMeanScore = [mean, adjectiveRating, parentEl, childEl];
  childEl.textContent = Number(mean).toFixed(2);

  const base =
    MEAN_SCORE_STATES.find((s) => mean >= s.min && mean <= s.max) ??
    MEAN_SCORE_STATES[0];

  // Dark mode: tinted card with light text instead of a pastel card.
  // "important" so these beat dark.css's neutral fallback for this card.
  const dark = isDarkTheme();
  const state = dark ? { ...base, ...base.dark } : base;
  const priority = dark ? "important" : "";
  const paint = (el, prop, value) => el && el.style.setProperty(prop, value, priority);

  paint(parentEl, "background", state.bg);
  paint(childEl, "color", state.text);

  const titleEl = parentEl.querySelector(".mean-title");
  const sublabelEl = parentEl.querySelector(".mean-sublabel");
  const adjectiveRatingEl = parentEl.querySelector(".adjectiveRating");

  if (adjectiveRatingEl) adjectiveRatingEl.textContent = adjectiveRating;
  paint(adjectiveRatingEl, "color", state.text);
  paint(titleEl, "color", state.label);
  paint(sublabelEl, "color", state.sublabel);
}
