export function playAnimation(wrapperId) {
  const wrapper = document.getElementById(wrapperId);
  const leftPanel = document.getElementById("left-panel");
  const rightPanel = document.getElementById("right-panel");

  if (window.innerWidth >= 768) {
    // Desktop animation
    wrapper?.classList.add("fade-slide-out");
    leftPanel?.classList.add("slide-left-out");
    rightPanel?.classList.add("slide-right-out");
  } else {
    // Mobile animation
    wrapper?.classList.add("fade-slide-out");
    rightPanel?.classList.add("fade-slide-out");
  }

  console.log("animation play");
}
