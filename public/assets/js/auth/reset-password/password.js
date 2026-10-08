let score = 0;

const rules = {
  length: (pw) => pw.length >= 8,
  uppercase: (pw) => /[A-Z]/.test(pw),
  number: (pw) => /[0-9]/.test(pw),
  symbol: (pw) => /[^A-Za-z0-9]/.test(pw),
};

function calculatePasswordScore(password) {
  let score = 0;

  Object.values(rules).forEach((test) => {
    if (test(password)) {
      score++;
    }
  });

  return score;
}

export function getPasswordScore() {
  return score;
}

export function initPassword() {
  const showConfirmToggle = document.getElementById("showConfirm");
  const hideConfirmToggle = document.getElementById("hideConfirm");
  const showToggle = document.getElementById("show");
  const hiddenToggle = document.getElementById("hidden");
  const inputPassword = document.getElementById("inputNewPassword");
  const inputConfirmPassword = document.getElementById("inputConfirmPassword");

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

  // confirm password
  if (showConfirmToggle && hideConfirmToggle && inputConfirmPassword) {
    showConfirmToggle.addEventListener("click", () => {
      inputConfirmPassword.type = "password";
      showConfirmToggle.classList.add("hidden");
      hideConfirmToggle.classList.remove("hidden");
    });

    hideConfirmToggle.addEventListener("click", () => {
      inputConfirmPassword.type = "text";
      hideConfirmToggle.classList.add("hidden");
      showConfirmToggle.classList.remove("hidden");
    });
  }

  if (inputPassword) {
    inputPassword.addEventListener("input", () => {
      const value = inputPassword.value;

      score = calculatePasswordScore(value);

      Object.entries(rules).forEach(([rule, test]) => {
        const li = document.querySelector(`[data-rule="${rule}"]`);

        if (test(value)) {
          li.classList.remove("text-gray-400");
          li.classList.add("text-green-600");
        } else {
          li.classList.remove("text-green-600");
          li.classList.add("text-gray-400");
        }
      });
    });
  }
}
