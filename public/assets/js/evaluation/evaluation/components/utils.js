export function escapeHTML(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

export function getInitials(name = "") {
  return name
    .trim()
    .split(/\s+/)
    .slice(0, 2)
    .map((part) => part.charAt(0).toUpperCase())
    .join("");
}

export function getTeacherImage(teacher) {
  return teacher?.profile_image || teacher?.image_path || teacher?.photo || "";
}

export function debounce(callback, delay = 300) {
  let timeout;

  return (...args) => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
      callback(...args);
    }, delay);
  };
}

export function renderRatingStars(rating) {
  const score = Math.max(0, Math.min(5, Number(rating) || 0));

  return Array.from({ length: 5 }, (_, index) => {
    const starNumber = index + 1;

    let iconClass = "fa-regular fa-star";
    let colorClass = "text-slate-200";

    if (score >= starNumber) {
      iconClass = "fa-solid fa-star";
      colorClass = "text-amber-400";
    } else if (score >= starNumber - 0.5) {
      iconClass = "fa-solid fa-star-half-stroke";
      colorClass = "text-amber-400";
    }

    return `
      <i class="${iconClass} ${colorClass} text-lg"></i>
    `;
  }).join("");
}

export function countText(inputId, counterId) {
  if ((!inputId, counterId)) return;

  const field = document.getElementById(inputId);
  const counter = document.getElementById(counterId);

  field.addEventListener("input", (e) => {
    const text = e.target.value;

    counter.textContent = text.length;
  });
}
