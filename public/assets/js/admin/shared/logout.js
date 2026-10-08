import { post } from "../../services/http.js";

export function logout() {
  document.addEventListener("click", async (e) => {
    const logoutBtn = e.target.closest("#logoutBtn");

    if (!logoutBtn) return;

    e.preventDefault();

    try {
      const response = await post("/logout.php", {});

      if (response.status === "success") {
        StatusModal.show("Logged Out", response.message, "success", {
          button: false,
        });

        setTimeout(() => {
          window.location.href = "admin-login";
        }, 1500);
      } else {
        StatusModal.show("Failed", response.message, "error");
      }
    } catch (error) {
      console.error("Logout request failed:", error);
    }
  });
}
