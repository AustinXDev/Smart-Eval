const MEAN_SCORE_STATES = [
  {
    min: 4.21,
    max: 5.0,
    bg: "#ECFDF5",
    text: "#065F46",
    label: "#34D399",
    sublabel: "#6EE7B7",
  },
  {
    min: 3.41,
    max: 4.2,
    bg: "#EDFAF1",
    text: "#1A7F3C",
    label: "#4ADE80",
    sublabel: "#86EFAC",
  },
  {
    min: 2.61,
    max: 3.4,
    bg: "#FFFBEB",
    text: "#B45309",
    label: "#F59E0B",
    sublabel: "#FCD34D",
  },
  {
    min: 1.81,
    max: 2.6,
    bg: "#FEF3C7",
    text: "#92400E",
    label: "#D97706",
    sublabel: "#FCD34D",
  },
  {
    min: 1.0,
    max: 1.8,
    bg: "#FEF2F2",
    text: "#991B1B",
    label: "#F87171",
    sublabel: "#FCA5A5",
  },
];

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

  childEl.textContent = Number(mean).toFixed(2);

  const state =
    MEAN_SCORE_STATES.find((s) => mean >= s.min && mean <= s.max) ??
    MEAN_SCORE_STATES[0];

  parentEl.style.background = state.bg;
  childEl.style.color = state.text;

  const titleEl = parentEl.querySelector(".mean-title");
  const sublabelEl = parentEl.querySelector(".mean-sublabel");
  const adjectiveRatingEl = parentEl.querySelector(".adjectiveRating");

  if (adjectiveRatingEl) {
    adjectiveRatingEl.style.color = state.text;
    adjectiveRatingEl.textContent = adjectiveRating;
  }
  if (titleEl) titleEl.style.color = state.label;
  if (sublabelEl) sublabelEl.style.color = state.sublabel;
}
