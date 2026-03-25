function openModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.remove("hidden");
}

function closeModal(modalId) {
  const modal = document.getElementById(modalId);
  if (modal) modal.classList.add("hidden");

  const form = modal.querySelector("form");
  if (form) form.reset();
}

function showConfirmation({ title, message, onConfirm }) {
  const modal = document.getElementById("confirmationModal");
  const confirmBtn = document.getElementById("confirmBtn");
  const cancelBtn = document.getElementById("cancelBtn");

  document.getElementById("modalTitle").textContent = title;
  document.getElementById("modalMessage").textContent = message;

  modal.classList.remove("hidden");

  const newConfirmBtn = confirmBtn.cloneNode(true);
  confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);

  newConfirmBtn.addEventListener("click", () => {
    modal.classList.add("hidden");
    onConfirm();
  });

  cancelBtn.onclick = () => {
    modal.classList.add("hidden");
  };
}
