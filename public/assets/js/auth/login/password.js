export function initPassword() {
  const showToggle = document.getElementById("show");
  const hiddenToggle = document.getElementById("hidden");
  const inputPassword = document.getElementById("inputPassword");

  // main password
  if (showToggle && hiddenToggle && inputPassword) {
    showToggle.addEventListener("click", () => {
      inputPassword.type = "password";
      showToggle.classList.add("hidden");
      hiddenToggle.classList.remove("hidden");
    });

    hiddenToggle.addEventListener("click", () => {
      inputPassword.type = "text";
      hiddenToggle.classList.add("hidden");
      showToggle.classList.remove("hidden");
    });
  }
}
