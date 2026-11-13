// ================================================================
// COUNT-UP NUMBER ANIMATION
// Animates an element's number from the value it currently shows to
// a new value (ease-out, ~700ms). Used by Evaluation Monitoring and
// Reports & Analytics.
//
//   countUp("totalStudents", 141);
//   countUp("completionRate", 38.5, { decimals: 1, suffix: "%" });
// ================================================================

const reduceMotion =
  typeof window !== "undefined" &&
  window.matchMedia?.("(prefers-reduced-motion: reduce)").matches;

/**
 * @param {string|HTMLElement} target element or element id
 * @param {number} value          new value
 * @param {object} [options]
 * @param {number} [options.decimals=0]   digits after the decimal point
 * @param {string} [options.suffix=""]    text after the number, e.g. "%"
 * @param {string} [options.prefix=""]    text before the number
 * @param {number} [options.duration=700] milliseconds
 */

export function countUp(target, value, options = {}) {
  const el =
    typeof target === "string" ? document.getElementById(target) : target;
  if (!el) return;

  const { decimals = 0, suffix = "", prefix = "", duration = 700 } = options;
  const to = Number(value) || 0;
  const from = Number(el.dataset.countValue) || 0;
  const format = new Intl.NumberFormat(undefined, {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  });
  const render = (n) => {
    el.textContent = `${prefix}${format.format(n)}${suffix}`;
  };

  // Cancel an animation still running on this element.
  if (el._countUpFrame) cancelAnimationFrame(el._countUpFrame);
  el.dataset.countValue = String(to);

  if (reduceMotion || from === to || duration <= 0) {
    render(to);
    return;
  }

  const start = performance.now();
  const step = (now) => {
    const t = Math.min(1, (now - start) / duration);
    const eased = 1 - Math.pow(1 - t, 3); // ease-out cubic
    render(from + (to - from) * eased);
    el._countUpFrame = t < 1 ? requestAnimationFrame(step) : null;
  };
  el._countUpFrame = requestAnimationFrame(step);
}
