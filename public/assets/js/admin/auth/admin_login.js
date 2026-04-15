import { notify } from "../../../../../resources/views/components/notify.js";

document.addEventListener("DOMContentLoaded", () => {
  const showToggle = document.getElementById("show");
  const hiddenToggle = document.getElementById("hidden");
  const inputPassword = document.getElementById("inputAdminPassword");

  // main password
  if (showToggle && hiddenToggle && inputPassword) {
    showToggle.addEventListener("click", () => {
      inputPassword.type = "password";
      showToggle.classList.add("hidden");
      hiddenToggle.classList.remove("hidden");
      console.log("click");
    });

    hiddenToggle.addEventListener("click", () => {
      inputPassword.type = "text";
      hiddenToggle.classList.add("hidden");
      showToggle.classList.remove("hidden");
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const adminUserName = document.getElementById("inputAdminUserName");
  const adminPassword = document.getElementById("inputAdminPassword");
  const form = document.getElementById("login-form");
  const signinBTN = document.getElementById("singin-button");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const adminUsernameValue = adminUserName.value;
    const adminPasswordValue = adminPassword.value;

    const data = {
      admin_username: adminUsernameValue,
      admin_password: adminPasswordValue,
    };

    signinBTN.disabled = true;
    signinBTN.value = "Sign in...";

    let isSuccess = false;

    fetch("/Smart-Eval/app/admin/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.status === "success") {
          isSuccess = true;

          notify(result.status, result.message);

          signinBTN.value = "Redirecting...";

          setTimeout(() => {
            signinBTN.value = "Sign in to Admin Dashboard";
            window.location.replace("/Smart-Eval/views/admin/verify.view.php");
          }, 1500);

          form.reset();
        } else {
          notify(result.status, result.message);
        }
      })
      .catch((error) => {
        console.log(error);
        notify("error", "Something went wrong!");
      })
      .finally(() => {
        if (!isSuccess) {
          signinBTN.disabled = false;
          signinBTN.value = "Sign in to Admin Dashboard";
        }
      });
  });
});
